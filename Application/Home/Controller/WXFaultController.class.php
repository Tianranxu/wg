<?php
/*
* 文件名：WXFaultController.class.php
* 功能：微信故障控制器
* 日期：2015-11-16
* 作者：XU
* 版权：Copyright @ 2015 风馨科技 All Rights Reserved
*/

namespace Home\Controller;
use Think\Controller;
use Predis\Client;
use Org\Util\RabbitMQ;

class WXFaultController extends Controller {
    protected $faultModel;
    protected $wxrepairModel;
    protected $srid;
    protected $srInfo;
    protected $catchQueue='catch_order_queue';
    protected $evaluateQueue='evaluate_order_queue';
    protected $title_map = [
        'list' => '故障列表',
        'personal' => '个人工单',
        'repairing' => '在修工单',
    ];

    public function _initialize(){
        header("Content-Type:text/html;charset=utf-8");
        $this->faultModel = D('fault');
        //查询维修员ID
        $this->wxrepairModel=D('wxrepair');
        $this->srInfo=$this->wxrepairModel->repairer($this->openid);
        $this->srid=$this->srInfo['id'];
        $this->faultDataController = A('Faultdata');
    }


    public function faultList($type,$openid,$compid=''){
        $title = I('get.title');
        $title = $this->title_map[$title];
        $where = $this->faultDataController->getWhere();
        $where['limit'] = $where['limit'] ? $where['limit'] : 10;
        if ($compid)
            $where['rc_id'] = $compid;
        if ($type == 1) 
            $where['submitter'] = $openid;
        if ($type == 2) 
            $where['repairman_openid'] = $openid;
        $this->assign('title',$title);
        $this->assign('templet', $this->templet);
        $this->assign('type',$type);
        $list = $this->faultModel->getFaultList($where);
        $this->faultDataController->setAndSendData($list, 'WXFault/mobile_list','','');
    }
    

    public function detail(){
        $umid = I('get.umid','');
        $type = I('get.type','');
        $this->assign('type',$type);
        if($umid){
            $this->assign('umid',$umid);
            $publicno = D('Publicno')->getPublicnoByUmid($umid);
            $templet = D('template')->selectTemplByAppid($publicno['appid']);
            $this->assign('templ', $templet['style']);
        }
        $this->faultDataController->showDetail('WXFault/mobile_detail');
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
        $redisModel=D('base');
        $redis=$redisModel->connectRedis();
        $keys=$redis->keys("unaccept_order:*:*:*:{$id}");
        foreach ($keys as $key){
            $redis->del($key);
        }
        $redisModel->disConnectRedis();
        if($result){
            retMessage(true,$result);
        }else{
            retMessage(false,null,"添加失败","添加失败",4001);
        }
    }
}
