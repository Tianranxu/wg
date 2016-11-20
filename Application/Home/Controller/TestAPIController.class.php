<?php
/*************************************************
 * 文件名：TestAPIController.class.php
 * 功能：     接口测试控制器
 * 日期：     2015.01.23
 * 作者：     XU
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/

namespace Home\Controller;
use Think\Controller;

class TestAPIController extends Controller{
    public function __initialize(){
        header("Content-Type:text/html;charset=utf-8");
    }

    public function index(){
        $compid = I('get.compid');
        $url = "http://wg.szfxhl.com/weixin/get_access_token/compid/";
        if ($compid) $this->assign('access_token', file_get_contents($url.$compid));
        $this->display();
    }

    //获取接口测试数据并返回数据
    public function getData(){
        $weixinModel = D('Weixin');
        $result = I('post.method') == 'post' ? json_decode($weixinModel->http_post(I('post.url'), json_encode(I('post.postData')))) : json_decode(file_get_contents(I('post.url')), true);
        retMessage(true, $result);
    }

    /**
    * 脚本，为之前绑定的物业号设置行业
    */
    public function setIndustry(){
        $publicnos = M('publicno')->select();
        $weixinModel = D('Weixin');
        foreach ($publicnos as $publicno) {
            $result[$publicno['cm_id'].$publicno['authorizer_info']] = $weixinModel->setIndustry($publicno['cm_id']);
        }
        dump($result);exit;
    }
}

