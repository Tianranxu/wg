<?php
/*************************************************
 * 文件名：ChargeController.class.php
 * 功能：     收费项目管理控制器
 * 日期：     2015.8.17
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
use Home\Controller\AccessController;

class ChargeController extends AccessController{
    protected $_chargeModel;
    protected $_meterModel;
    
    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
        
        $this->_chargeModel=D('charge');
        $this->_meterModel=D('meter');
    }
    
    /**
     * 收费项目管理页面
     */
    public function index(){
        //接收企业ID
        $compId=I('request.compid','');
        //判断是否有企业ID
        if (!$compId){
            $this->error('对不起，你无此权限进入！！',U('User/login'));
            exit;
        }
        
        $flag=I('post.flag','');
        if (!$flag){
            //TODO 未带搜索条件
            //查询该企业下的收费项目列表
            $chargeList=$this->_chargeModel->get_charge_list($compId,0,10);
            $this->assign('chargeList',$chargeList[1]['list']);
            $this->assign('total',$chargeList[1]['total']);
            $this->assign('compid',$compId);
            $this->display();
        }else {
            //TODO 附带搜索条件
            //接收数据
            $category=I('post.category','');
            $billing=I('post.billing','');
            $name=I('post.name','');
            $number=I('post.number','');
            $charging_cycle=I('post.charging_cycle','');
            
            $chargeList=$this->_chargeModel->get_charge_list($compId,0,10,$category,$billing,$name,$number,$charging_cycle);
            if ($chargeList[0]!=C('OPERATION_SUCCESS')){
                retMessage(false,null,'查询不到记录','查询不到记录',4002);
                exit;
            }
            retMessage(true,$chargeList[1]);
            exit;
        }
    }
    
    /**
     * 新建收费项目页面
     */
    public function add_charge(){
        //接收企业ID
        $compId=I('get.compid','');
        //判断是否有企业ID
        if (!$compId){
            $this->error('对不起，你无此权限进入！！',U('User/login'));
            exit;
        }
        
        $this->assign('compid',$compId);
        $this->display();
    }
    
    /**
     * 编辑收费项目页面
     */
    public function edit_charge(){
        //接收企业ID
        $compId=I('get.compid','');
        $chargeId=I('get.chargeid','');
        //判断是否有企业ID
        if (!($compId && $chargeId)){
            $this->error('对不起，你无此权限进入！！',U('User/login'));
            exit;
        }
        
        //查询该收费项目的信息
        $chargeInfo=$this->_chargeModel->find_charge_info($chargeId);
        
        $this->assign('chargeList',$chargeInfo[1]);
        $this->assign('compid',$compId);
        $this->assign('chargeid',$chargeId);
        $this->display();
    }
    
    /**
     * 加载更多
     */
    public function loadMore(){
        //接收数据
        $compId=I('post.compid','');
        $flag=I('post.flag','');
        $page=I('post.page','');//var_dump($page);exit;
        
        if ($compId && $flag && $page){
            if ($flag==1){
                //TODO 未带搜索条件
                $chargeList=$this->_chargeModel->get_charge_list($compId,$page,10);
                if (($page/10)>=$chargeList[1]['total']){
                    retMessage(false,null,'已加载完毕','已加载完毕',4003);
                    exit;
                }
                if ($chargeList[0]!=C('OPERATION_SUCCESS')){
                    retMessage(false,null,'查询不到记录','查询不到记录',4002);
                    exit;
                }
                retMessage(true,$chargeList[1]['list']);
                exit;
            }elseif ($flag==2){
                //TODO 附带搜索条件
                $category=I('post.category','');
                $billing=I('post.billing','');
                $name=I('post.name','');
                $number=I('post.number','');
                $charging_cycle=I('post.charging_cycle','');
                
                $chargeList=$this->_chargeModel->get_charge_list($compId,$page,10,$category,$billing,$name,$number,$chargeList);
                if (($page/10)>=$chargeList[1]['total']){
                    retMessage(false,null,'已加载完毕','已加载完毕',4003);
                    exit;
                }
                if ($chargeList[0]!=C('OPERATION_SUCCESS')){
                    retMessage(false,null,'查询不到记录','查询不到记录',4002);
                    exit;
                }
                retMessage(true,$chargeList[1]['list']);
                exit;
            }
        }else {
            retMessage(false,null,'未接收到数据','未接收到数据',4001);
            exit;
        }
    }
    
    /**
     * 读取仪表管理数据
     */
    public function get_meter_list(){
        //查询该仪表读数列表
        $meterList=$this->_meterModel->getAllSetMeter();
        if ($meterList){
            retMessage(true,$meterList);
            exit;
        }else {
            retMessage(false,null,'查询不到记录','查询不到记录',4002);
            exit;
        }
    }
    
    /**
     * 新建收费项目
     */
    public function do_add_charge(){
        //接收数据
        $cm_id=I('post.compid','');
        $name=I('post.name','');
        $category=I('post.category','');
        $billing=I('post.billing','');
        $measureStyle=I('post.measure_style','');
        $price=I('post.price',0);
        $chargingCycle=I('post.charging_cycle','');
        $remark=I('post.remark','');
        
        if ($cm_id && $name && $category && $billing && $measureStyle && ($price>=0)){
            $chargingCycle=implode(',', $chargingCycle);
            
            //组装添加数据
            $data=array(
                'cm_id'=>$cm_id,
                'name'=>$name,
                'price'=>$price,
                'category'=>$category,
                'billing'=>$billing,
                'measure_style'=>$measureStyle,
                'charging_cycle'=>$chargingCycle,
                'remark'=>$remark,
                'create_time'=>date('Y-m-d H:i:s',time())
            );
            
            $result=$this->_chargeModel->add_charge($cm_id,$data);
            if ($result!=C('OPERATION_SUCCESS')){
                retMessage(false,null,'新建收费项目失败','新建收费项目失败',4002);
                exit;
            }
            retMessage(true,null);
            exit;
        }else {
            retMessage(false,null,'接收不到数据','接收不到数据',4001);
            exit;
        }
    }
    
    /**
     * 编辑收费项目
     */
    public function do_edit_charge(){
        //接收数据
        $cm_id=I('post.compid','');
        $chargeId=I('post.chargeid','');
        $price=I('post.price',0);
        $chargingCycle=I('post.charging_cycle','');
        $remark=I('post.remark','');
        
        if ($cm_id && $chargeId  && ($price>=0)){
            $chargingCycle=implode(',', $chargingCycle);
            
            //组装更新数据
            $data=array(
                'cm_id'=>$cm_id,
                'id'=>$chargeId,
                'price'=>$price,
                'charging_cycle'=>$chargingCycle,
                'remark'=>$remark,
                'modify_time'=>date('Y-m-d H:i:s',time())
            );
            $result=$this->_chargeModel->edit_charge($cm_id,$data);
            if ($result!=C('OPERATION_SUCCESS')){
                retMessage(false,null,'编辑收费项目失败','编辑收费项目失败',4002);
                exit;
            }
            retMessage(true,null);
            exit;
        }else {
            retMessage(false,null,'接收不到数据','接收不到数据',4001);
            exit;
        }
    }
}


