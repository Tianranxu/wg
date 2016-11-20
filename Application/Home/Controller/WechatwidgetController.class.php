<?php

/*
 * 文件名：WechatwidgetController.class.php
 * 功能：     微信加载控件控制器
 * 日期：     2015.11.04
 * 作者：     XU
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
**/

namespace Home\Controller;
use Think\Controller;

class WechatwidgetController extends WidgetController{
    /*
    * 初始化类
    */
    protected function _initialize(){
        header("Content-Type:text/html;charset=utf-8");
        parent::_initialize();
    }

    /*
    * 重写的标签函数
    */
    public function labelHtml(array $datas, $name = '' ,$insertValue=""){  
        $html = '<p class="title">'.$datas['default_value'].'</p>';
        return htmlspecialchars($html);
    }

    /*
    * 重写的上传附件函数
    */
    public function uploadHtml(array $datas, $name = ''){
        $url = __ROOT__."/Public/js/jqueryFileUpload/server/index.php?type=affairs&compid={$datas['cm_id']}";
        $html = <<<HTML
        <span class="left">附件：</span>
        <ul class="adv_con1">
            <li>
            <label>
                <input type="file" class="fileupload"  id="{$datas['input_name']}" name="files[]" data-url="$url">
                <i class="fa fa-plus-square-o fa-5" id="icon"></i>
            </label>
            </li>
        </ul>
        <div class="clear"></div>
HTML;
        return htmlspecialchars($html);
    }
}