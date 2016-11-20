<?php
/*************************************************
 * 文件名：UserController.class.php
 * 功能：     用户管理控制器
 * 日期：     2015.7.23
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
use Think\Controller;

class IndexController extends Controller {
    public function index(){
        //跳转到登陆页面
        $domain = I('get.domain', 'www');
        $arr = explode(".", $_SERVER['HTTP_HOST']);
        $length = count($arr);
        $url = "http://{$arr[$length-2]}.{$arr[$length-1]}/user/login?d={$domain}";
        redirect($url);
    }
}