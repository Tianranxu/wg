<?php

/**
 * 文件名：CarController.class.php
 * 功能：车辆控制管理器
 * 作者：XU    
 * 日期：2015-08-03
 * 版权：Copyright ? 2014-2015 风馨科技 All Rights Reserved
 */

namespace Home\Controller;
use Think\Controller;

class CarController extends AccessController{
    /***
     * 初始化函数
     */
    public function _initialize(){
        header("Content-Type:text/html;charset=utf-8");
        parent::_initialize();
        $this->_carmodel        = D('Car');
    }
    /**
     * 车辆展示界面展示函数，包括搜索后的页面展示
     */
    public function index(){
        //初始化模型
        $car                    = M('car_manage','fx_');
        
        $param                  = array();                  //初始化查询条件变量
        $allCar                 = array();                   //初始化车辆信息变量
        //获取从上一个页面传过来的楼盘id
        if(I('get.community_id')){
            $param['cc_id']         = I('get.community_id');
        }else{
            $this->error("请先登录！",U('user/user_setting'));
        }
        $Community       = $this->_carmodel->getCommunityName($param['cc_id']);
        $companyId       = $this->_carmodel->getCompanyId($param['cc_id']);
        
        //判断是否有表单传递过来的搜索条件，若有，组装条件并查询
        if(I('post.cc_id')){
            $param['cc_id']         = I('post.cc_id');
        }
        if(I('post.status')){
            $param['status']        = I('post.status');
        }
        if(I('post.car_number')){
            $carNumber = I('post.car_number');
            $temp   = "car_number LIKE '%{$carNumber}%'";
            array_push($param,$temp);
        }
        if(I('post.card_number')){
            $cardNumber = I('post.card_number');
            $temp   = "card_number LIKE '%{$cardNumber}%'";
            array_push($param,$temp);
        }
        
        //获取符合条件的车辆的信息的页数
        $count              = ceil(($car->where($param)->count())/10);
        //获取ajax传过来的页面参数和flag参数
        $page         = I('post.page','');
        $flag         = I('post.flag','');
        //根据搜索条件或上个页面传过来的企业id查询车辆信息（每次显示10条）
        $carInfo        = $car->where($param)->limit($page*10,10)->order(array('create_time'=>'desc','modify_time'=>'desc'))->select();
        
        if($carInfo){
            foreach ($carInfo as $k=>$v){
                //添加楼盘名字信息
                $v['cc_name']   = $Community['name'];
                //将禁用的车辆和正常的车辆分成两个数组
                if($v['status']==1){
                    $abled[$k]       = $v;
                }elseif ($v['status']==-1){
                    $disabled[$k]    = $v;
                }
            }
            //若页面有点击加载更多操作或者搜索操作
            if($page||$flag==1){
                if($abled&&$disabled){
                    //将所有车辆写进一个数组内
                    $allCar         = array_merge($abled,$disabled);
                }elseif ($abled&&!$disabled){
                    $allCar         = $abled;
                }elseif (!$abled&&$disabled){
                    $allCar         = $disabled;
                }
                retMessage(true,$allCar,"","",$count);
                exit;
            }
        }else{
            if($page){
                retMessage(false,null,'已加载完毕','已加载完毕',4003);
                exit;
            }
            if($flag==1&&!$page){
                retMessage(false,null,'未搜到相关信息','未搜到相关信息',4002);
                exit;
            }
        }
        
        $this->assign('total',$count);
        $this->assign('compid',$companyId['cm_id']);
        $this->assign('cc_id',$param['cc_id']);
        $this->assign('abled',$abled);
        $this->assign('disabled',$disabled);
        $this->display();
    }
    
    /**
     * 添加车辆函数
     */
    public function add(){
        //获取楼盘id
        $com_id          =  I('get.id');
        //获取楼盘所属公司名称
        $Community       = $this->_carmodel->getCommunityName($com_id);
        if(!$com_id){
            $this->error("请先登录！",U('User/login'));
        }
        
        //获取车辆收费信息
        $item            = '停车费';
        $type            = C("CHARGES.1");
        $method          = C("CHARGES.4");
        for($i=0;$i<12;$i++){
            $month[$i]['number']       = $i+1;
            $month[$i]['state']        = 1;
        }
        //向页面传递数据
        $this->assign('community',$Community['name']);
        $this->assign('cc_id',$com_id);
        $this->assign('month',$month);
        $this->assign('item',$item);
        $this->assign('type',$type);
        $this->assign('method',$method);
        $this->display();
    }
    public function doAdd(){
        //初始化表单模型
        $car                        = M('car_manage','fx_');
        //收集表单数据
        $data['card_number']        = I('post.card_number');
        $data['car_number']         = I('post.car_number');
        $data['user']               = I('post.user');
        $data['mobile_number']      = I('post.mobile_number');
        $data['monthly']            = I('post.monthly');
        $data['cc_id']              = I('post.cc_id');
        $data['address']     = I('post.address');
        $data['description']    = I('post.description');
        if(I('post.month')){
            $month                  = I('post.month');
            $data['cycle']          = substr($month,0,strlen($month)-1);
        }
        
        //添加车辆的时间信息
        if($data){
            $data['create_time']    = date("Y-m-d H:i:s",time());
            $data['modify_time']    = $data['create_time'];
        }
        
        //检测该楼盘内是否有相同的卡号
        $check                      = $this->_carmodel->checkCardNumber($data['card_number'],$data['cc_id']);
        if($check){
            retMessage(false,-1,"卡号重复","卡号重复",4003);
            exit;
        }
        if($car->add($data)){
            retMessage(true,1,"添加成功","添加成功",2000);
            exit;
        }else{
            retMessage(false,-1,"添加失败","添加失败",4002);
            exit;
        }
    }
    
    /**
     * 修改车辆函数（包括车辆禁用和恢复）
     */
    public function modify(){
        //获取要修改的车辆的id
        $id         = I('get.id');
        
        if(!$id){
            $this->error("请先登录！",U('User/login'));
        }
        //获取车辆的信息
        $carInfo    = $this->_carmodel->getCar($id);
        //获取楼盘名称信息
        $Community  = $this->_carmodel->getCommunityName($carInfo['cc_id']);
        //获取收费周期信息
        $cycle      = explode(',',$carInfo['cycle']);
        for($i=0;$i<12;$i++){
            $month[$i]['number']       = $i+1;
            foreach ($cycle as $k=>$v){
                if($v==$month[$i]['number']){
                    $month[$i]['state']= 1;
                    break;
                }
            }
        }
      //获取车辆收费信息
        $IC              = $carInfo['item_category'];
        $CS              = $carInfo['charges_style'];
        $item            = '停车费';
        $type            = C("CHARGES.1");
        $method          = C("CHARGES.4");
        
        //将车辆信息传递到页面
        $this->assign('month',$month);
        $this->assign('item',$item);
        $this->assign('type',$type);
        $this->assign('method',$method);
        $this->assign('community',$Community['name']);
        $this->assign('car',$carInfo);
        $this->display();
    }
    public function doModify(){
        //初始化表单模型
        $car                        = M('car_manage','fx_');
        //收集表单信息
        $data['id']                 = I('post.id'); //获取车辆id
        $data['car_number']         = I('post.car_number');
        $data['user']               = I('post.user');
        $data['mobile_number']      = I('post.mobile_number');
        $data['monthly']            = I('post.monthly');
        $data['description']        = I('post.description');
        $data['address']            = I('post.address');
        $data['status']             = I('post.status');
        //处理收费周期
        $month                      = I('post.month');
        $data['cycle']              = substr($month,0,strlen($month)-1);
        if($data){
            $data['modify_time']        = date("Y-m-d H:i:s",time());
            $data['create_time'] = date("Y-m-d H:i:s"); 
        }
        
        if($car->save($data)){
            retMessage(true,1,"修改成功","修改成功",2000);
            exit;
        }else{
            retMessage(false,-1,"修改失败","修改失败",4002);
            exit;
        }
    }

}