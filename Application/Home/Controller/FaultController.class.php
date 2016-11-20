<?php
/*
* 文件名：FaultController.class.php
* 功能：故障控制器
* 日期：2015-11-11
* 作者：XU
* 版权：Copyright @ 2015 风馨科技 All Rights Reserved
*/
namespace Home\Controller;
use Think\Controller;
use Org\Util\RabbitMQ;
use Predis\Client;
class FaultController extends AccessController{
    protected $compid;
    protected $_userMod;
    protected $distributeOrderQueue = 'distribute_order_queue';
    protected $evaluateQueue = 'evaluate_order_queue';
    
    public function _initialize(){
        header("Content-Type:text/html;charset=utf-8");
        parent::_initialize();
        $this->faultModel = D('Fault');
        $this->faultDataController = A('Faultdata');
        $this->compid = I('get.compid');
        $this->_userMod = D('user');
    }
    //故障首页
    public function index(){
        // 接收企业ID
        $compId = I('get.compid', '');
        // 判断是否有企业ID
        if (! $compId) {
            $this->error('对不起，你无此权限进入！！', U('User/login'));
            exit();
        }
        
        // 判断是否有用户ID
        if ($this->userID) {
            $userModel = D('user');
            // TODO 查询用户信息，以及头像url
            $userInfo = $userModel->find_user_info($this->userID);
            $userPhotoUrl = $userModel->find_user_photo($userInfo['photo'])['url_address'];
            // TODO 查询该企业的名称
            $companyModel = D('company');
            $companyInfo = $companyModel->selectCompanyDetail($compId);
        
            $this->assign('compid', $compId);
            $this->assign('userInfo', $userInfo);
            $this->assign('photo', $userPhotoUrl);
            $this->assign('companyInfo', $companyInfo);
        } else {
            $this->error('对不起，你无此权限进入！！', U('User/login'));
            exit();
        }
        $this->display();
    }
    //PC开始报修
    public function repair(){
        //获取用户信息
        $userInfo = $this->_userMod->find_user_info($this->userID);
        //获取所有楼盘
        $propMod = D('Property');
        $prop = $propMod->get_property_list($this->compid, 0, '', '', 1);
        $proLlist = json_decode($prop)[1]->list;

        $this->assign('lists',$proLlist);
        $this->assign('info',$userInfo);
        $this->assign('compid',$this->compid);
        $this->assign('user',$this->userID);
        $this->display();
    }
    //报修具体情况
    public function specific(){
        $cc_id = I('post.cc_id');
      //所有正常的楼栋
        $buildMod = D('building');
        $builds = $buildMod->selectAllBuild($cc_id, 0, 0, 1);
      //所有正常的故障设备
        $compDeviMod = D('Compdevice');
        //查询楼盘绑定维修公司列表
        $CompBindRepairs = $compDeviMod->getBindList($cc_id, 1);
      //组成数组
        $concrete = array(
            'build' => $builds,
            'devi' => $CompBindRepairs
        );
        exit(json_encode($concrete));      
    }
    //故障现象
    public function phenomenon(){
        $dev_id = I('post.dev_id');
        $phenomMod = D('phenomenon');
        $phenoms = $phenomMod->getPhenomenonList($dev_id);
        exit(json_encode($phenoms)); 
    }

    //故障列表
    public function faultList() {
        $where = $this->faultDataController->getWhere();
        $compid = $where['cm_id'] ? $where['cm_id'] : $where['rc_id'] ;
        $c_type = $where['cm_id'] ? 1 : 2 ;
        $this->assign('c_type',$c_type);   //公司类型
        $flag = $where['flag'] ? $where['flag'] : '' ;
        $isShift = I('get.isShift',1);
        $this->assign('isShift',$isShift);
        $list = $this->faultModel->getFaultList($where);
        $this->faultDataController->setAndSendData($list, 'fault_list',$compid,$flag);
    }

    //故障详情
    public function detail(){
        $c_type = I('get.c_type','');
        $compid = I('get.compid','');
        $this->assign('c_type',$c_type);
        $this->assign('compid',$compid);
        $this->faultDataController->showDetail('detail');
    }

    //评价
    public function evaluate(){
        $eva_data['work_evaluation'] = I('post.work');
        $eva_data['service_evaluation'] = I('post.service');
        $eva_data['eva_content'] = I('post.content','');
        $eva_data['fd_id'] = I('post.id');
        $result = $this->faultModel->addEvaluation($eva_data);
        $data = $this->faultModel->getFaultDetails($eva_data['fd_id']);
        if($result){
            RabbitMQ::publish($this->evaluateQueue, json_encode($data));
            retMessage(true,$result);
        }else{
            retMessage(false,$result,"添加失败","添加失败",4001);
        }
    }

    //结单
    public function finish(){
        $id = I('post.id');
        $result = $this->faultModel->changeStatus($id,C('FAULT_STATUS')['FINISH']);
        $this->clearRedis($id);
        if(!$result) retMessage(false,null,"添加失败","添加失败",4001);
        $noticeResult=A('pushmsg')->readNotices($this->companyID,C('NOTICE_TYPE')[5]['type'],$id);
        retMessage(true,$result);
    }

    //清除redis中派单的数据
    public function clearRedis($id){
        $redisModel=D('base');
        $redis=$redisModel->connectRedis();
        $keys=$redis->keys("unaccept_order:*:*:*:{$id}");
        foreach ($keys as $key){
            $redis->del($key);
        }
        $redisModel->disConnectRedis();
    }
                

    //重新派单(物业公司)
    public function reassign(){
        $id = I('post.id');
        $where['id'] = $id;
        $temp = $this->faultModel->getFaultList($where);
        $picture = $this->faultModel->getAttachment($id);
        $data = $temp['result'][0];
        $ori_number = $data['fault_number'];
        unset($data['id'],$data['sr_id'],$data['repairman_openid'],$data['catch_time'],$data['shift_reason'],$data['shift_time']);
        $data['origin_fd_id'] = $id;
        $data['status'] = C('FAULT_STATUS.NOT_YET');
        $data['create_time'] = date('Y-m-d H:i:s');
        $count = $this->faultModel->getFaultCount();
        $count = str_pad($count,7,'0',STR_PAD_LEFT);
        $count = substr(strval($count),-7);
        $data['fault_number'] = 'GZ'.$count;
        $picture = $picture[0]['picurl'] ? $picture : '' ;
        $data_result = $this->faultDataController->publish($data,$picture,$ori_number);
        if (!$data_result) retMessage(false,null,"故障添加失败","",4001);
        $fin_result = $this->faultModel->changeStatus($id,C('FAULT_STATUS')['FINISH']);
        if(!$fin_result) retMessage(false,null,"更改状态失败","",4002);
        $noticeResult=A('pushmsg')->readNotices($data['cm_id'],C('NOTICE_TYPE')[5]['type'],$id);
        retMessage(true,null);
    }

    //再派单(维修公司)
    public function republish(){
        $id = I('post.id');
        $result = $this->faultModel->changeStatus($id,C('FAULT_STATUS')['HANGED']);
        $data = $this->faultModel->getFaultById($id);
        $data['flag'] = 1;
        RabbitMQ::publish($this->distributeOrderQueue, json_encode($data));
        if ($result) {
            retMessage(true,1);
        }else{
            retMessage(false,1);
        }
    }
} 