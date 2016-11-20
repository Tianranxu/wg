<?php
/*
* 文件名：FaultdataController.class.php
* 功能：故障数据控制器
* 日期：2015-11-11
* 作者：XU
* 版权：Copyright @ 2015 风馨科技 All Rights Reserved
*/
namespace Home\Controller;
use Think\Controller;
use Org\Util\RabbitMQ;
class FaultdataController extends Controller{
    //报障的消息队列
    private $_The_repair_query = 'distribute_order_queue';
    protected $QUERY_MAPPER = array(
        'st' => 'status',
        'lt' => 'limit',
        'fd_id' => 'fault_number',
        'ad' => 'location',
        'did' => 'did', //设备id
        'c_time' => 'create_time',
        's_time' => 'start_time',
        'e_time' => 'end_time',
        'sr_id' => 'sr_id',  //维修员id
        'openid' => 'submitter', //报修人，微信端为openid，pc端为userID
        'tp' => 'type', //报障类型
        'flag' => 'flag',
        'ot' => 'timeout_status',       //超时状态 
    );

    public function _initialize(){
        header("Content-Type:text/html;charset=utf-8");
        $this->faultModel = D('Fault');
    }

    /*
    * 获取查询条件
    */
    public function getWhere(){
        $compid = I('get.compid');
        $page = I('get.page',1);
        $compModel = D('company');
        $where['page'] = $page;
        $type = $compModel->selectCompanyAll($compid);
        foreach (I('get.') as $key => $value) {
            if(array_key_exists($key, $this->QUERY_MAPPER) && $value) {
                $where[$this->QUERY_MAPPER[$key]] = $value;
            }
        }
        $where['limit'] = $where['limit'] ? $where['limit'] :15;
        if ($compid) {
            $key = ($type['cm_type'] == 2) ? 'rc_id' : 'cm_id';
            $where[$key] = $compid;    
        }
        return $where;
    }

    //组装发送到页面的数据
    public function setAndSendData($list, $tpl,$compid,$flag){
        $deviceModel = D('device');
        $device = $deviceModel->getDeviceList();
        foreach ($device as $k => $v) {
            $devices[$v['id']] = $v;
        }
        $phenomenonModel = D('phenomenon');
        $phenomenon = $phenomenonModel->getAllPhenomenon();
        foreach ($phenomenon as $k => $v) {
            $phenomenons[$v['id']] = $v;
        }
        foreach ($list['result'] as $k => $v) {
            $list['result'][$k]['device'] = $devices[$v['did']]['name'];
            $list['result'][$k]['phenomenon'] = $phenomenons[$v['fp_id']]['name'];
            $list['result'][$k]['sr_name'] = $v['sr_name'] ? $v['sr_name'] : '' ;
            $list['result'][$k]['sr_phone'] = $v['sr_phone'] ? $v['sr_phone'] : '' ;
        }
        if ($flag) {
            retMessage(true,$list);
        }
        $this->assign('device',$devices);
        $this->assign('list',$list);
        $this->assign('compid',$compid);
        $this->display($tpl);
    }

    public function showDetail($tpl){
        $id = I('get.id','');
        $data = $this->faultModel->getFaultById($id);
        //故障原因
        if ($data['fr_id']) {
            $reasonModel = D('reason');
            $reason = $reasonModel->causeById($data['fr_id']);
            $data['reason'] = $reason['name'];
        }
        //维修员信息
        if ($data['sr_id']) {
            $repairmanModel = D('Wxrepair');
            $repairman = $repairmanModel->getRepairman($data['sr_id']);
            $data['repairman'] = $repairman['name'];
            $data['rep_mobile'] = $repairman['phone'];
        }
        //获取评价
        if($data['status'] == C('FAULT_STATUS')['EVALUATED']){
            $evaluation = $this->faultModel->getEvaluation($id);
            $data['work_evaluation'] = $evaluation['work_evaluation'] . '分  ' . C('RANKING')[$evaluation['work_evaluation']];
            $data['service_evaluation'] = $evaluation['service_evaluation'] . '分  ' . C('RANKING')[$evaluation['service_evaluation']];
            $data['eva_content'] = $evaluation['eva_content'];
        }
        //获取所有附件
        $data['attachment'] = $this->faultModel->getAttachment($id);
        $this->assign('data',$data);
        $this->display($tpl);
    }
    //提交报修单
    public function do_repair(){
        //报修人
        $name = I('post.name');
        //联系电话
        $phone = I('post.phone');
        //楼盘id
        $cc_id = I('post.cc_id');
        //楼盘名字
        $cc_name = I('post.cc_name');
        //楼栋id
        $bu_id = I('post.bu_id');
        //楼栋名字
        $bu_name = I('post.bu_name');
        //详细地址
        $addr = $cc_name.$bu_name.I('post.addr');
        //设备id
        $dev_id = I('post.dev_id');
        //设备名称
        $d_name = I('post.d_name');
        //现象ID
        $ph_id = I('post.ph_id');
        //现象名称
        $ph_name = I('post.ph_name');
        //备注
        $remark = I('post.remark');
        //附件
        $accessory = explode(',',rtrim(I('post.accessory'),','));
        //楼盘所属地ID
        $addr_id = I('post.addr_id');
        //维修公司ID
        $rc_id = I('post.rcid');
        //物管公司ID
        $cm_id = I('post.cm_id');
        //报修人
        $user = I('post.user');
        //报修类型
        $type = I('post.type');
        //生成报修单流水号
        $faultMod = D('Fault');
        $No = $faultMod->getFaultCount();
        $No = str_pad($No,7,'0',STR_PAD_LEFT);
        $No = 'GZ'.substr(strval($No),-7);
        //报修单写数据库
        $faultForm = array(
            'fault_number' => $No, //故障号
            'contacts' => $name, //联系人
            'ct_mobile' => $phone,//联系电话
            'submitter' => $user,//报修人(微信账户为openid,pc为管理员userID)
            'type' => $type,//报修类型(1为pc报障,2为微信报障)
            'cid' => $addr_id,//城市id
            'cm_id' => $cm_id,//物管公司id
            'cc_id' => $cc_id,//楼盘id
            'bm_id' => $bu_id,//楼栋id
            'location' => $addr,//详细地址
            'did' => $dev_id,//故障设备id
            'fp_id' => $ph_id,//故障现象id
            'remark' => $remark,//报障时的备注
            'rc_id' => $rc_id,//维修公司id
            'status' => C('FAULT_STATUS.NOT_YET'),
            'origin_fd_id' => NULL,
            'create_time' => date('Y-m-d H:i:s',time())//报障时间或故障创建时间
        );
        $faultID = $this->publish($faultForm,$accessory);

        $status = $faultID?'success':"fail";
        exit($status);
    
    }
    public function publish($faultForm,$accessory,$originNumber=''){
        $faultMod = D('Fault');
        $faultID = $faultMod->add($faultForm);
        //报修附件写入数据库
        $picture = array();
        if(!empty($accessory[0])){ 
            $pictureMod = M('fault_picture');
            foreach($accessory as $pr){
                $url = $originNumber ? $pr['pic_url'] : $pr ;
                $picture[] = array(
                    'pic_url' => $url,
                    'fd_id' => $faultID
                );
            }
            $pictureMod->addAll($picture);
        }
        if($faultID){
            $faultMod = D('Fault');
            $result = $faultMod->getFaultById($faultID);
            $faultForm['id'] = $faultID;
            $faultForm['d_name'] = $result['d_name'];
            $faultForm['p_name'] = $result['p_name'];
            $faultForm['origin_number'] = $originNumber;
            //推送消息队列
            RabbitMQ::publish($this->_The_repair_query, json_encode($faultForm));
        }
        return $faultID;
    }
    
    //为授权上线前的公众号添加umid
    /*public function addUmidForPublicnos(){
        $publicnoModel = D('Publicno');
        $weixinModel = D('weixin');
        $results = $publicnoModel->addUmid();
        $publicnos = $publicnoModel->getAllPublicno();
        dump($results);
        foreach ($publicnos as $key => $publicno) {
            $access_token = $weixinModel->get_authorizer_access_token($publicno['cm_id']);
            $publicno_results[] = $weixinModel->set_menu($publicno['custom_menu'],$publicno['custom_url'],$access_token,$publicno['appid'],$publicno['um_id']);
        }
        dump($publicno_results);
        exit;
        $this->display('WXFault/mobile_list');
    }*/

    //设置维修号的菜单
    public function setMenu(){
        $weixinModel = D('Weixin');
        //获取维修号的access_token
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . C('REPAIR_PUBLICNO.APPID') . '&secret=' . C('REPAIR_PUBLICNO.APPSECRET') ;
        $access_token = json_decode(file_get_contents($url), true) ;
        $register_url = "http://" . $_SERVER['HTTP_HOST'] . "/WXRepair/register" ;
        $personal_url = "http://" . $_SERVER['HTTP_HOST'] . '/WXRepair/centrality' ;
        $uncatched_url = "http://" . $_SERVER['HTTP_HOST'] . '/WXRepair/uncatched/';
        $repair_url =  "http://" . $_SERVER['HTTP_HOST'] . '/WXRepair/faultList/title/repairing/st/1'; 
        $personlist_url = "http://" . $_SERVER['HTTP_HOST'] . '/WXRepair/faultList/title/personal';
        $menu =  '{
            "button":[
                {
                   "name":"用户中心",
                   "sub_button":[
                       {
                           "type":"view",
                           "name":"注册",
                           "url":"'. $register_url .'"
                        },
                        {
                           "type":"view",
                           "name":"个人中心",
                           "url":"'. $personal_url .'"
                        },
                        {
                           "type":"click",
                           "name":"签到",
                           "key":"V1001_SIGN"
                        }
                    ]
               },
               {
                   "name":"工单管理",
                   "sub_button":[
                       {    
                           "type":"view",
                           "name":"未接工单",
                           "url":"'. $uncatched_url .'"
                        },
                        {
                           "type":"view",
                           "name":"在修故障",
                           "url":"'. $repair_url .'"
                        },
                        {
                           "type":"view",
                           "name":"个人工单",
                           "url":"'. $personlist_url .'"
                        }
                    ]
               },
                {   
                  "type":"click",
                  "name":"反馈",
                  "key":"V1001_FEEDBACK"
                }
            ]
        }';
        $menu_url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $access_token['access_token'];
        $result = $weixinModel->http_post($menu_url,$menu);
        dump($result);exit;
        $this->display('WXFault/mobile_list');   
    }
    
}
