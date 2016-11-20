<?php
/*
* 文件名：LeaseindexController.class.php
* 功能：租赁管理主页控制器
* 日期：2016-01-23
* 作者：XU
* 版权：Copyright @ 2015 风馨科技 All Rights Reserved
*/

namespace Home\Controller;
use Think\Controller;

class LeaseindexController extends Controller{
    public function index(){
        $this->assign('compid', I('get.compid'));
        $this->display();
    }

}