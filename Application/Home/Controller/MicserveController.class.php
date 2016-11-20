<?php
/*************************************************
 * 文件名：MicserveController.class.php
 * 功能：     微服务管理控制器
 * 日期：     2015.9.1
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
use Think\Controller;

class MicserveController extends AccessController
{
    
    /**
     * 微服务首页
     */
    public function index(){
        $compid = I('get.compid');
        $homecompMod = D('Homecompose');
        //所有微服务
        $micServe = $homecompMod->selectServe();
        //企业选择的微服务
        $serveCompMod = D('serve');
        $is_serve = $serveCompMod->selectIsServe($compid);
        
        $gallery = $micServe;
        foreach($micServe as $key=>$var){
            foreach($is_serve as $arr){
                if($var['id']==$arr['id']){
                    unset($gallery[$key]);
                }
            }
        }
        $this->assign('serve', $gallery);
        $this->assign('is_serve', $is_serve);
        $this->assign('compid', $compid);
        $this->display();
    }
    
    /**
     * 确认微服务排序及功能显示
     */
    public function sort(){
        $serve_arr = $_POST['order'];
        if($serve_arr=='[]'){
            exit('empty');
        }
        $compid = I('post.compid');
        $serve_arr = json_decode($serve_arr,true);
        $micMod = D('serve');
        $result = $micMod->availableServeOrd($compid, $serve_arr);//更新企业下所有菜单
        if($result){
            exit('success');
        }else{
            exit('fail');
        }
        
    }
}


