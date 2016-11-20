<?php

/**
 * 文件名：MetermanageController.class.php
 * 功能：仪表管理控制器
 * 作者：XU
 * 日期:2015-08-17
 * 版权： Copyright @ 2015 风馨科技 All Rights Reserved
 */
namespace Home\Controller;
use Home\Controller\AccessController;
use Org\Util\RabbitMQ;



class MetermanageController extends AccessController{
    protected $import_queue     = 'meter_import_queue';
    /**
     * 初始化函数
     */
    public function _initialize(){
        header("Content-Type:text/html;charset=utf-8");
        parent::_initialize();
        $this->_meterModel = D('meter');
    }
    /**
     * 仪表读数展现函数
     */
    public function index(){
        $commId        = I('get.cm_id','');
        if(!$commId){
            $this->error("请先登录！",U('user/login'));
            exit;
        }
        
        $community      = $this->_meterModel->getCommunity($commId);     //楼盘名称和公司id
        
        $param['cm_id'] = $commId;                 //将楼盘列入搜索条件之一                
        /***接收页面传过来的搜索条件，并组装成数组***/
        if(I('post.year','')){
            $param['year']  = I('post.year','');
        }
        if(I('post.month','')){
            $param['month'] = I('post.month','');
        }
        if(I('post.building','')){
            $param['bm_id']  = I('post.building','');
        }
        if(I('post.house','')){
            $param['house']  = I('post.house','');
        }
        if(I('post.meterset','')){
            $param['ms_id']  = I('post.meterset','');
        }
        
        //获取ajax传过来的页面参数和flag参数
        $page         = I('post.page','');
        $flag         = I('post.flag','');
        /**根据楼盘id或页面传过来的搜索条件获取其楼盘下的所有仪表信息，每次显示10条，按修改时间排序 **/
        $meter          = $this->_meterModel->getAllMeter($param,$page);
        $data           = array();      //初始化页面数组
        //获取仪表的楼盘信息并组装传入页面的数组
        foreach ($meter['data'] as $k => $v){
            $data[$k]['id']     = $v['id'];
            $data[$k]['c_name'] = $community['name'];
            $data[$k]['year']   = $v['year'];
            $data[$k]['month']  = $v['month'];
            $data[$k]['degree'] = $v['degree'];
            $property[$k]       = $this->_meterModel->getProperty($v['hm_id']);//楼栋和房间信息
            $data[$k]['number'] = $property[$k]['number'];
            $data[$k]['b_name'] = $property[$k]['name'];
            $meterSet[$k]       = $this->_meterModel->getMeterSet($v['ms_id']);//设置的仪表名称和单位
            $data[$k]['m_name'] = $meterSet[$k]['name'];
            $data[$k]['unit']   = $meterSet[$k]['unit'];
        }
        
        /****对ajax的参数查询后进行数据返回******/
        if($data&&$flag){
           //若对ajax传过来的条件获取的数据不为空
           $result['data']  = $data;
           $result['total'] = $meter['total'];  
           retMessage(true,$result,"加载成功","加载成功",2000);
        }else{
            if($flag==1){
                //若是在非搜索的情况下
                retMessage(false,null,"全部加载完毕","全部加载完毕",4001);
            }elseif ($flag==2){//若是搜索的情况下
                if(!$page){//若是初次搜索
                    retMessage(false,null,"查无相关数据","查无相关数据",4002);
                }else{//若是搜索下的加载更多
                    retMessage(false,null,"全部加载完毕","全部加载完毕",4001);
                }
            }
        }
        
        /**获取搜索栏和录入栏上的信息**/
        $allBuilding    = $this->_meterModel->getAllBuilding($commId); //获取楼盘下所有的楼栋信息
        $allSetMeter    = $this->_meterModel->getAllSetMeter();         //获取所有的仪表信息
        //添加月份信息
        for($i=1;$i<=12;$i++){
            $month[$i-1]['number']  = "$i";
        }
        $this->assign('total',$meter['total']);             //页数信息
        $this->assign('month',$month);                      //月份信息
        $this->assign('cm_id',$commId);                     //楼盘id                    
        $this->assign('community',$community['name']);      //楼盘名称
        $this->assign('compid',$community['cm_id']);        //公司id
        $this->assign('meter',$data);                       //仪表主要信息
        $this->assign('building',$allBuilding);             //所有楼栋信息
        $this->assign('meterset',$allSetMeter);             //所有仪表
        $this->display();
    }
    
    /**
     * 仪表录入函数
     */
    public function addMeter(){
        //获取页面传过来的数据
        $param['year']        = I('post.year','');
        $param['month']       = I('post.month','');
        $param['bm_id']       = I('post.building','');
        $house                = I('post.house','');
        $param['ms_id']       = I('post.meterset','');
        $param['degree']      = I('post.degree','');
        $param['cm_id']       = I('post.cm_id','');
        //查看是否有对应的房间号，若有则添加数据并返回对应值，若没有，返回相应的值
        $result     = $this->_meterModel->checkAndAdd($param,$house);
        if($result==-1){
            retMessage(false,-1,"无对应房间号","无对应房间号",4002);
        }elseif($result==1){
            retMessage(true,1,"添加成功","添加成功",2000);
        }elseif($result==2){
            retMessage(false,2,"添加失败","添加失败",4001);
        }
    }
    
    /**
     * 仪表编辑函数
     */
    public function editMeter(){
        //获取页面传过来的数据
        $param['id']        = I('post.meterId','');
        $param['degree']    = I('post.degree','');
        //补充修改时间数据
        if($param){
            $param['modify_time'] = date("Y-m-d H:i:s",time());
        }
        $MD     = M('meter_degree','fx_');
        if($MD->save($param)){//保存修改数据
            retMessage(true,1,"修改成功","修改成功",2000);
        }else{
            retMessage(false,null,"修改失败","修改失败",4001);
        }
    }
}
