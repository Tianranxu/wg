<?php
/*************************************************
 * 文件名：WXRepairController.class.php
 * 功能：     维修员控制器
 * 日期：     2015.9.27
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
use Think\Controller;
use Org\Util\RabbitMQ;
use Predis\Client;
class WXRepairController extends AuthorizeController{

    private $_restore_order = 'restore_order_queue';
    private $_shift_order = 'shift_order_queue';
    protected $_catch_order='catch_order_queue';
    protected $compAuth = 'true';
    protected $templateMsgQueue = 'template_msg_queue';
    /*protected $r_openid='oXLJ7wcGRGiowKHl0CavoHkH0C7Q';
    protected $srid=1;*/
    public function _initialize(){
        parent::_initialize();
        $this->faultDataController = A('Faultdata');
        //不管点击哪 个菜单都把授权走完
        $repairMod = D('Wxrepair');
        //读取维修员信息
        $repair = $repairMod->repairer($this->r_openid);
        //判断维修员有没有注册 
        if($repair['status']== C('REPAIR_STATUS.NOT_REGI')){
            $access_token = $repair['access_token'];
            //当前时间
            $currDate = time();       
            //到期时间
            $expires  = $currDate - $repair['expires'];
            //离过期10分就刷新access_token
            if($expires>C('EXPIRES_IN')){
                $result = $repairMod->refreshToken($repair['refresh_token']);
                $access_token = $result->access_token;
            }
            //调接获取用户信息
            $userInfo = $repairMod->WXrepairInfo($access_token,$this->r_openid);
            $time = isset($result->refresh_token)?$currDate:$repair['expires'];
            $refresh_token = isset($result->refresh_token)?$result->refresh_token:$repair['refresh_token'];
            $data = array(
                'id' => $repair['id'],
                'name' => $userInfo->nickname,
                'head' => $userInfo->headimgurl,
                'status' => C('REPAIR_STATUS.NOT_REGI'),
                'expires' => $time,
                'access_token' => $access_token,
                'refresh_token' => $refresh_token
            );
            $repairMod->saveRepairman($data);
        }
        
    }
    public function index(){
    }
    //维修员注册页面
    public function register(){
        $url = $_SERVER['REQUEST_URI'];
        $this->authorize($url);
        $repairMod = D('Wxrepair');
        //读取维修员信息       
        $repair = $repairMod->repairer($this->r_openid);
        if($repair['status'] != C('REPAIR_STATUS.NOT_REGI')){
            //已注册跳转到个人中心
            $this->redirect('WXRepair/centrality', '', 0, '已注册!跳转到个人中心');
        }else{
            //查询关联的维修公司
            $compMod = D('company');
            $repairCompany = $compMod->selectTypeComp('',2);
            //读取所有维修设备
            $deviceMod = D('Device');
            $device = $deviceMod->getDeviceList(1);
            
            $this->assign('device',$device);
            $this->assign('repair',$repair);
            $this->assign('company',$repairCompany);
            $this->display();
        }
    }

    //提示注册成功页面
    public function prompt(){
        $this->display();
    }
    //个人中心
    public function centrality(){
        $repairMod = D('Wxrepair');
        //个人信息
        $repairMod = D('Wxrepair');
        $info = $repairMod->repairer($this->r_openid);
        //个人维修项目
        $itemMod = D('Repairdev');
        $item = $itemMod->repairDevi($info['id']);
        //个人维修范围
        $cityMod = D('Repaircity');
        $areas = $cityMod->city($info['id'],$info['cm_id']);
        foreach($areas as $ar){
            $ci = $cityMod->selectRegion($ar['pid']);
            $pr = $cityMod->selectRegion($ci['pid']);
            $region[$ar['pid']]['city'] = $ci['name'];
            $region[$ar['pid']]['prov'] = $pr['name'];
            $region[$ar['pid']]['areas'][] = $ar['name'];
        }
        //所属维修公司
        $compMod = D('company');
        $info['cm_name'] = $compMod->selectCompanyDetail($info['cm_id'])['name'];

        $this->assign('info',$info);
        $this->assign('region',$region);
        $this->assign('item',$item);
        $this->display();
    }
    
    //编辑个人信息
    public function personal(){
        //个人信息
        $repairMod = D('Wxrepair');
        $info = $repairMod->repairer($this->r_openid);
        
        $this->assign('info',$info);
        $this->display();
    }

 
//修复完成后的故障回单
    public function receipt(){
        $orderID = I('get.id');
        $faultMod = D('Fault');
        $repairMod = D('Wxrepair');
        //查询维修员是否有属于的维修公司
        $rm_id = $repairMod->repairer($this->r_openid)['cm_id'];
        $ord_details = $faultMod->getFaultById($orderID);
        //判断维修员权限
        if(empty($rm_id)|| $rm_id!=$ord_details['rc_id']){
            $this->compAuth = 'false';
        }
        //查询故障原因
        $reasonMod = D('reason');
        $cause = $reasonMod->getReasonList($ord_details['did']);
        //如果是已修复订单点进来就查询已选择的故障原因
        $ord_status = $ord_details['status'];
        $repaired = C('FAULT_STATUS.REPAIRED');
        $evaluated = C('FAULT_STATUS.EVALUATED');
        $finish = C('FAULT_STATUS.FINISH');
        if($ord_status == $repaired || $ord_status == $evaluated || $ord_status == $finish){
            $ord_details['r_name'] = $reasonMod->causeById($ord_details['fr_id'])['name'];
            foreach($cause as &$ca){
                if($ca['id'] == $ord_details['fr_id']){
                    $ca['sele'] = 'selected';
                }
            }
        }
        //订单状态
        $statusName = '';
        switch($ord_status){
            case $repaired :
                $statusName = '已修复';
                break;
            case $evaluated :
                $statusName = '已评价';
                break;
            case $finish :
                $statusName = '已结单';
                break;
            case C('FAULT_STATUS.SHIFTED'):
                $statusName = '已转单';
                break;
        }
        $this->assign('order',$ord_details);
        $this->assign('statusName',$statusName);
        $this->assign('cause',$cause);
        $this->assign('compAuth',$this->compAuth);
        $this->display();
    }
    //提交回单
    public function do_receipt(){
        $id = I('post.id');
        $cu_id = I('post.cu_id');
        $details = I('post.details');
        //查询维修员ID
        $WXrepairMod = D('Wxrepair');
        $rid = $WXrepairMod->repairer($this->r_openid)['id'];
        $data = array(
            'id' => $id,
            'sr_id' => $rid,//维修人员id
            'fr_id' => $cu_id,//fr_id 故障原因id
            'status' => C('FAULT_STATUS.REPAIRED'),//status 状态(未修复或未接单是-1；正在修复或已接单是1；已修复是2；已评价是3；已转单是4；超时接单是-2；超时2次转后台是-3)
            'restore_time' => date('Y-m-d H:i:s',time()),//修复时间
            'repairman_openid' => $this->r_openid,
            'description' => $details //description 修复后维修员填写的修复描述description
        );
        $faultMod = D('Fault');
        $result = $faultMod->save($data);
//消息队列
        //查询已修订单详情
        $message = $faultMod->getFaultInfo($id);
        if($result){
            //推送消息队列
            RabbitMQ::publish($this->_restore_order, json_encode($message));
        }
        $result = $result?'success':'fail';
        exit($result);
    }

//故障转单
    public function transfe(){
        $orderID = I('get.id');
        $faultMod = D('Fault');
        $repairMod = D('Wxrepair');
        //查询维修员是否有属于的维修公司
        $rm_id = $repairMod->repairer($this->r_openid)['cm_id'];
        //订单详情
        $ord_details = $faultMod->getFaultById($orderID);
        //判断维修员权限
        if(empty($rm_id)|| $rm_id!=$ord_details['rc_id']){
            $this->compAuth = 'false';
        }
        //订单状态
        $statusName = '';
        switch($ord_details['status']){
            case C('FAULT_STATUS.REPAIRED'):
                $statusName = '已修复';
                break;
            case C('FAULT_STATUS.EVALUATED'):
                $statusName = '已评价';
                break;
            case C('FAULT_STATUS.FINISH'):
                $statusName = '已结单';
                break;
            case C('FAULT_STATUS.SHIFTED'):
                $statusName = '已转单';
                break;
        }

        $this->assign('order',$ord_details);
        $this->assign('statusName',$statusName);
        $this->assign('compAuth',$this->compAuth);
        $this->display();
    }
    //提交转单
    public function do_transfe(){
        //要转的订单ID
        $id = I('post.id');
        $faultMod = D('Fault');
        //转单原因 shift_reson
        $details = I('post.details');
        //更改订单为已转单
        $faultMod = D('Fault');
        $result = $faultMod->where('id=%d',$id)->setField(array(
            'status' => C('FAULT_STATUS.SHIFTED'),
            'shift_time' => date('Y-m-d H:i:s',time()),
            'shift_reson' => $details
        ));
        //消息队列
        if($result){
            //订单详情
            $ord_details = $faultMod->getFaultById($id);
            //推送消息队列
            RabbitMQ::publish($this->_shift_order, json_encode($ord_details));
            //发送消息通知客服
            $users = D('WXuser')->getUserByType($ord_details['cm_id'], C('WXUSER_TYPE.REPAIR_MG'));
            if ($users) {
                $long_id = D('Weixin')->getTemplateId($ord_details['cm_id'], C('TEMPLATE_MSG.FAULT'), C('PUBLICNO_TYPE.PROPERTY'));
                foreach ($users as $key => $user) {
                    $temp_data['content'][$key] = [
                        'touser' => $user['openid'],
                        'template_id' => $long_id,
                        'data' => [
                            'first' => ['value' => '您的公司有1个故障转单需要处理', 'color' => '#173177'],
                            'keyword1' => ['value' => $ord_details['contacts'], 'color' => '#173177'],
                            'keyword2' => ['value' => $ord_details['ct_mobile'], 'color' => '#173177'],
                            'keyword4' => ['value' => $ord_details['location'], 'color' => '#173177'],
                            'keyword5' => ['value' => $ord_details['d_name'] . '  '. $ord_details['p_name']],
                            'remark' => ['value' => '请您尽快安排处理此故障单，谢谢！', 'color' => '#173177'],
                        ],
                    ];
                }
                $temp_data['compid'] = $ord_details['cm_id'];
                $temp_data['msgtype'] = 'property';
                RabbitMQ::publish($this->templateMsgQueue, json_encode($temp_data));
            }
            $result = $result?'success':'fail';
            exit($result);
        }
    }
    //查找 维修公司
    public function repCompay(){
        $number = I('post.key');
        $compMod = D('company');
        $result = $compMod->selectTypeComp($number,2);
        exit(json_encode ($result));
    }
    //提交注册处理
    public function repsubmit(){
        $repairMod = D('Wxrepair');
        //计算维修员编号
        $count = $repairMod->select_repair_count();
        $NO = str_pad($count,5,"0",STR_PAD_LEFT);
        $NO = substr((string)$NO,0,5);
        $number = 'WXY'.$NO;
        //维修员注册信息
        $date = date('Y-m-d H:i:s',time());
        $repairer = array(
            'number' => $number,
            'name' => I('post.name'),
            'status' => C('REPAIR_STATUS.PENDING'),
            'phone' => I('post.phone'),
            'cm_id' => I('post.cid'),
            'create_time' => $date,
            'request' => $date
        );
        $this->compid = I('post.cid');
        $device = explode(',',I('post.device'));
        //保存数据到维修人员表
        $register = $repairMod->keep_repairer($repairer,$this->r_openid);
        //保存数据到设备中间表
        if($register && $register!='update'){
            foreach($device as $dev){
                $devTemp[] = array(
                    'cid' => I('post.cid'),
                    'rid' => $register,
                    'dev_id' => $dev
                );
            }
            $repTodevMod = D('Repairdev');
            $temp = $repTodevMod->addAll($devTemp);
            $result = $temp?'success':'device';
            exit($result);
        }
        $result = $register?'success':'fail';
        exit($result);
    }
    //处理编辑个人信息
    public function doedit(){
        //维修员注册信息
    
        $info = array(
            'id' => I('post.id'),
            'name' => I('post.name'),
            'phone' => I('post.phone')
        );
        $repairMod = D('Wxrepair');
        $result = $repairMod->save($info);
        if($result){
            exit('success');
        }else{
            exit('fail');
        }
    }


    //退出维修公司
    public function quit(){
        $rid = I('post.rid');
        $repairMod = D('Wxrepair');
        $devTemp = D('Repairdev');
        $cityTemp = D('Repaircity');
        $field = array(
            'cm_id' => null,
            'status' => C('REPAIR_STATUS.NOT_REGI'),
            'gid' => null,
            'exam_id' => null,
            'request' => null,
            'number' => null
        );
        $result = $repairMod->where('openid="%s"',$this->r_openid)
                            ->setField($field);
        //删除dev和city中间表
        $devTemp->where('rid=%d',$rid)->delete();
        $cityTemp->where('rid=%d',$rid)->delete();
        
        if($result){
            exit('success');
        }else{
            exit('fail');
        }
    }

    public function faultList(){
        $WXFaultController = A('WXFault');
        $WXFaultController->faultList(2,$this->r_openid,$this->compid);
    }

    //未接单
    public function uncatched(){
        $title = '未接工单';
        //链接redis
        vendor('Redis.autoload');
        $redis = new Client(array(
            'host' => C('REDIS_HOST'),
            'port' => C('REDIS_PORT'),
            'database' => C('REDIS_DB')
        ));
        //TODO 从redis中用维修员r_openid读取fd_id并组成数组$ids 
        $uncatchedFaultKeys=$redis->keys("unaccept_order:{$this->r_openid}:{$this->compid}:{$this->srid}:*");
        $redis->quit();
        foreach ($uncatchedFaultKeys as $key){
            $pos = strrpos($key, ':');
            $ids[] = substr($key, $pos+1);
        }
        $faultModel = D('fault');
        $where = $this->faultDataController->getWhere();
        $where['ids'] = $ids;
        $list = $faultModel->getFaultList($where);
        
        $this->assign('title',$title);
        $this->assign('type',2);
        $this->faultDataController->setAndSendData($list, 'WXFault/mobile_list','','');
    }

    //接单
    public function catchfault()
    {
        $id = I('get.id', '');
        $flag = I('get.flag', '');
        $this->faultModel = D('Fault');
        $msg = '';
        //检查订单状态
        $faultInfo = $this->faultModel->getFaultById($id);
        if ($faultInfo['status'] != C('FAULT_STATUS')['NOT_YET']) $msg = '接单失败！<br/>该单已经被接单或者维修员修复中。';
        //检查维修员状态
        $repairerModel = D('wxrepair');
        $repairerInfo = $repairerModel->checkRepairer($this->srid, $faultInfo['rc_id'], 2);
        if (!$repairerInfo) $msg = '您无权限操作';
        if ($msg) {
            $this->assign('msg', $msg);
            if ($flag) retMessage(false, null, "单已被接", "", 4001);
        } else {
            //更改订单状态
            $result = $this->faultModel->catchFault($id, $this->srid, $this->r_openid);
            if (!$result) {
                $this->assign('msg', $msg);
                if ($flag) retMessage(false, null, "更改状态错误", "", 4002);
            } else {
                $faultDatas = $this->handleUnCatchFault($faultInfo);
                if($faultInfo['timeout_status']==C('FAULT_OVERTIME.OVERTIME_TWICE')){
                    $updatePropertyNoticesResult=A('pushmsg')->readNotices($faultInfo['cm_id'],C('NOTICE_TYPE')[0]['type'],$faultInfo['id']);
                    $updateRepairNoticesResult=A('pushmsg')->readNotices($faultInfo['rc_id'],C('NOTICE_TYPE')[0]['type'],$faultInfo['id']);
                }
                $this->assign('msg', $faultDatas[0]);
                //TODO 发送消息给报障人和维修员
                RabbitMQ::publish($this->_catch_order, json_encode($faultDatas[1]));
                if ($flag) retMessage(true, null);
            }
        }
        $this->display('WXFault/catchfault');
    }

    /**
     * 进行接单
     * @param int $id 工单ID
     * @return string
     */
    public function handleUnCatchFault($faultInfo)
    {
        //清除redis中派单的数据
        $redisModel = D('base');
        $redis = $redisModel->connectRedis();
        $keys = $redis->keys("unaccept_order:*:*:{$faultInfo['id']}");
        $unCatchOpenids = [];
        foreach ($keys as $k => $key) {
            $tempKey = explode(':', $key);
            $unCatchOpenids[$k] = $tempKey[1];
            $redis->del($key);
        }
        unset($unCatchOpenids[array_search($this->r_openid, $unCatchOpenids)]);
        $redisModel->disConnectRedis();
        $faultInfo['status'] = C('FAULT_STATUS')['CATCHED'];
        $faultInfo['sr_id'] = $this->srid;
        //查询维修员ID
        $this->wxrepairModel = D('wxrepair');
        $srInfo = $this->wxrepairModel->repairer($this->r_openid);
        $faultInfo['sr_name'] = $srInfo['name'];
        $faultInfo['phone'] = $srInfo['phone'];
        $faultInfo['repairman_openid'] = $this->r_openid;
        $faultInfo['catch_time'] = date('Y-m-d H:i:s');
        $faultInfo['uncatch_openid'] = $unCatchOpenids;
        $msg = '接单成功！<br/>请及时修复相关故障';
        return [$msg,$faultInfo];
    }

    //继续修障
    public function repair(){
        //TODO 发送消息给维修员，若成功发出，retMessage(true,1);
        $id = I('post.id');
        $faultModel = D('Fault');
        $data = $faultModel->getFaultById($id);
        if ($data['sr_id']) {
            $repairmanModel = D('Wxrepair');
            $repairman = $repairmanModel->getRepairman($data['sr_id']);
            $data['sr_name'] = $repairman['name'];
            $data['phone'] = $repairman['phone'];
        }
        $data['flag'] = 1;
        if ($data['fault_number']) {
            RabbitMQ::publish($this->_catch_order, json_encode($data));
            retMessage(true,null);
            exit();
        }
        retMessage(false,null);
    }

}
