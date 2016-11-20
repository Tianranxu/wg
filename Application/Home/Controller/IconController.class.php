<?php
/*************************************************
 * 文件名：IconController.class.php
 * 功能：     图标控制器
 * 日期：     2015.7.23
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
use Think\Controller;
class IconController extends AccessController
{
    public function index(){
        $compid = I('get.compid');
        $type = I('get.type'); //要更换图标的类型
        $iconid = I('get.iconid');
        $menuid = I('get.id');
        $iconMod = D('icon');

        $currIcon = $iconMod->selectIcon($type, $iconid);
        $allIcon = $iconMod->selectIcon($type);
        $icon_array = array();
        foreach($allIcon as $icon){
            $path = $icon['url_address'];
            $file_name = basename($path,".png");
            $fileArr = explode('_',$file_name);
            $icon_array[$fileArr[0]][] = $icon;
        }

        $this->assign('current', $currIcon);
        $this->assign('icon', $icon_array);
        $this->assign('type', $type);
        $this->assign('compid', $compid);
        $this->assign('menuid', $menuid);
        $this->display();
    }
    public function modify_icon(){
        $type = I('post.type'); //要更换图标的类型
        $newiId = I('post.iconid'); //更换后的图标id
        $iconMod = D('icon');
        $icon = $iconMod->selectIcon($type, $newiId);
        if(empty($icon)){
            $icon['sate'] = 'empty';
            exit(json_encode($icon));
        }else{
            exit(json_encode($icon));
        }
        
    }
    
}