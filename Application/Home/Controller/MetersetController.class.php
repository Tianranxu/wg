<?php

/***
 * 文件名：MetersetController.class.php
 * 功能：仪表设置控制器
 * 作者：XU
 * 日期：2015-08-21
 * 版权：CopyRight @ 2015 风馨科技 All Rights Reserved
 */

namespace Home\Controller;
use Think\Controller;

class MetersetController extends AccessController{
    /**
     * 初始化函数
     */
    public function _initialize(){
        header("Content-Type:text/html;charset=utf-8");
        parent::_initialize();
        $this->_meterModel = D('meter');
    }
    
    /**
     * 仪表设置主界面函数
     */
    public function index(){
        if(!$this->userID){
            $this->error("请先登录！",U('User/login'));
        }
        //获取页面ajax传递过来的page和flag信息
        $page       = I('post.page','');
        $flag       = I('post.flag','');
        //获取搜索信息
        if(I('post.meter')||I('post.status')){
            $param['meter']      = I('post.meter','');
            $param['status']     = I('post.status','');
        }
        
        //获取所有的已经设置好的仪表信息
        $allSetMeter      = $this->_meterModel->getSetMeter($page,$param);
        //将所有仪表信息拆分为正常和禁用两个数组
        foreach ($allSetMeter['data'] as $k => $v){
            if($v['status']==1){
                $abled[$k]  = $v;
            }else{
                $disabled[$k] = $v;
            }
        }
        
        if($flag==1){
            //若是没有搜索条件下
            if($allSetMeter['data']){
                retMessage(true,$allSetMeter,"加载成功","加载成功",2000);
            }else{
                retMessage(false,null,"加载完毕","加载完毕",4001);
            }
        }elseif ($flag==2){
            //若有搜索条件
            if($allSetMeter['data']){
                retMessage(true,$allSetMeter,"加载成功","加载成功",2000);
            }else{
                if($page){
                    //若是点击了加载更多
                    //retMessage(false,null,"加载完毕","加载完毕",4001);
                }else{
                    retMessage(false,null,"查询没有结果","查询没有结果",4002);
                }
            }
        }
        $this->assign('total',$allSetMeter['page']);
        $this->assign('abled',$abled);
        $this->assign('disabled',$disabled);
        $this->display();
    }
    
    /**
     * 仪表添加函数
     */
    public function add(){
        $param['name']       = I('post.name','');
        $param['unit']       = I('post.unit','');
        $param['description']= I('post.description','');
        $param['create_time']= date("Y-m-d H:i:s",time());
        $param['modify_time']= date("Y-m-d H:i:s",time());
        //检查状态正常的仪表表名是否重复并添加
        $result = $this->_meterModel->checkAndAddMeter($param);
        if($result==1){
            retMessage(true,1,"添加成功","添加成功",2000);
        }elseif ($result==2){
            retMessage(false,2,"仪表重复","仪表重复",4001);
        }elseif ($result==3){
            retMessage(false,3,"添加失败","添加失败",4002);
        }
    }
    
    /**
     * 仪表编辑函数
     */
    public function edit(){
        $param['id']         = I('post.id','');
        $param['name']       = I('post.name','');
        $param['unit']       = I('post.unit','');
        $param['description']= I('post.description','');
        $param['status']     = I('post.status','');
        $param['modify_time']= date("Y-m-d H:i:s",time());
        //检查检查状态正常的仪表表名是否重复并修改
        $result = $this->_meterModel->checkAndEditMeter($param);
        if($result==1){
            retMessage(true,1,"修改成功","修改成功",2000);
        }elseif ($result==2){
            retMessage(false,2,"仪表重复","仪表重复",4001);
        }elseif ($result==3){
            retMessage(false,3,"修改失败","修改失败",4002);
        }
    }
}