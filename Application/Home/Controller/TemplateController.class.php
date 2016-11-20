<?php
/*************************************************
 * 文件名：TemplateController.class.php
 * 功能：     模板管理控制器
 * 日期：     2015.9.1
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
use Think\Controller;

class TemplateController extends AccessController{
    
    /**
     * 模板管理首页
     */
    public function index(){
        //接收数据
        $tempMod = D('template');
        $compMod = D('company');
        $getData=array(
            'compid'=>I('get.compid',''),
        );
        // 判断是否有企业ID
        if (!$getData['compid']) {
            $this->error('对不起，你无此权限进入！！', U('User/login'));
            exit();
        }
        //查出所有可用模板
        $templets = $tempMod->selectTempl();
        //查出公司所属模板
        $tempId = $compMod->selectCompanyDetail($getData['compid'])['templet'];

        $this->assign('getData',$getData);
        $this->assign('templets',$templets);
        $this->assign('tempId',$tempId);
        $this->display();
    }
    public function templ(){
        //接收数据
        $tid = I('post.tid');
        $cid = I('post.compid');
        $compMod = D('company'); 
        $tempMod = D('template');
        $templet = $tempMod->selectTempl($tid)['reject'];
        if($templet!=null && $templet!=$cid){
            exit('errer');
        }
         
        $result = $compMod->where("id=%d",$cid)->setField("templet",$tid);
        $result = $result?'success':'fail';
        exit($result);
        
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
}


