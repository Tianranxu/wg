<?php
/*************************************************
 * 文件名：WXSignController.class.php
 * 功能：     微信维修员签到控制器
 * 日期：     2015.12.17
 * 作者：     XU
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
use Think\Controller;
use Org\Util\RabbitMQ;

class WXSignController extends AuthorizeController{
    protected $weixinMsgQueue = 'weixin_msg_queue';
    protected $msg_map = [
        'flag',
        'latitude',
        'longitude',
        'accuracy',
        'errormsg',
    ];

    public function _initialize(){
        header("Content-Type:text/html;charset=utf-8");
        parent::_initialize();
    }

    //签到页面
    public function sign(){
        $this->display();
    }

    //数据处理
    public function processData(){
        foreach (I('post.') as $key => $value) {
            if (in_array($key, $this->msg_map) && $value) {
                $msg[$key] = strval($value);
            }
        }
        if($msg['flag'] == 1){
            $WXSignModel = D('WXSign');
            $msg['sign_time'] = date('Y-m-d H:i:s');
            $msg['sr_id'] = $this->srid;
            $check = $WXSignModel->checkSign($msg);
            $msg['content'] = $check ? '今天您已签到，请勿重复操作' : '签到成功';
            if (!$check){
                $result = $WXSignModel->addSignMessage($msg);
                $msg['content'] = $result ? '签到成功' : '签到失败(0)';
            }
        }elseif ($msg['flag'] == 2) {
            $msg['content'] = '签到失败:'.$msg['errormsg'] ; 
        }
        $msg['type'] = 'repair';
        $msg['openid'] = $this->r_openid;
        $msg['msgType'] = 'text';
        RabbitMQ::publish($this->weixinMsgQueue, json_encode($msg));
        retMessage(true,null);
    }

}