<?php
namespace Home\Behaviors;

use Think\Behavior;

class UseragentBehavior extends Behavior{

    //检测浏览器标识符
    public function run(&$param)
    {
        $userAgent=$_SERVER['HTTP_USER_AGENT'];
        if(strpos($userAgent,'MSIE')!==false || strpos($userAgent,'rv:11.0')) {
            if(strtolower(explode('/',$_SERVER['REDIRECT_URL'])[1])!='useragent'){
                redirect(U('useragent/index'));
            }
        }
    }
}