<?php
/*************************************************
 * 文件名：WeixinController.class.php
 * 功能：     微信控制器
 * 日期：     2015.9.1
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
use Think\Controller;
use Predis\Client;

class WeixinController extends Controller{
    protected $_weixinModel;
    
    /**
     * 初始化
     */
    protected function _initialize(){
        vendor('Redis.autoload');
        $this->_weixinModel=D("weixin");
    }
    
    public function index(){
        $redis=new Client(array(
            'host' => C('REDIS_HOST'),
            'port' => C('REDIS_PORT'),
            'database' => C('REDIS_DB'),
        ));
        echo $redis->get('weixin:ticket');
        $redis->quit();
    }
    
    /**
     * 授权事件接收URL,微信每10分钟向此URL推送一次component_verify_ticket
     */
    public function ticket(){
        $data=array(
            'signature'=>I('get.signature',''),
            'timestamp'=>I('get.timestamp',''),
            'nonce'=>I('get.nonce',''),
            'encrypt_type'=>I('get.encrypt_type',''),
            'msg_signature'=>I('get.msg_signature','')
        );
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        $ticket=$this->_weixinModel->decrypt($postStr, $data);
        if ($ticket) echo 'success';
    }

    /**
     * 公众号消息与事件接收URL,公众号粉丝发送给公众号的消息,微信服务器会通过APPID转发给此接口
     * http://testwg.szfxhl.com/weixin/callback/appid/$APPID$
     */
    public function callback(){
        //接受appid
        $appId=I('get.appid','');
        if (!$appId) exit;
        header("Content-Type:text/xml;charset=utf-8");
        if(I('get.echostr','')) {
            $this->_weixinModel->valid(C('CHUYUN_TOKEN'),I('get.'));
        }else{
            ($appId == C('REPAIR_PUBLICNO.APPID')) ? $this->_weixinModel->responseRepairMsg(I('get.')) : $this->_weixinModel->responseWeixinMsg($appId, I('get.'));
        }
    }
    
    /**
     * 获取access_token
     */
    public function get_access_token()
    {
        //接收数据
        $compid=I('get.compid','');
        if (!$compid){
            retMessage(false,null,'接收不到compid','接收不到compid',4001);
            exit;
        }
        
        $this->_weixinModel = D("weixin");
        $ticket=$this->_weixinModel->testTicket();
        $access_token = $this->_weixinModel->get_authorizer_access_token($compid);
        if ($access_token == - 1) {
            $this->error("授权不可用或已过期，请重新授权", U('publicno/access', array(
                'compid' => $this->compid
            )));
        }
        echo $access_token;
    }

    /**
    * 获取长模板id
    */
    public function get_template_id(){
        $compid = I('get.compid', '');
        $short_id = I('get.short_id');
        $type = I('get.type');
        $result = D('weixin')->getTemplateId($compid, $short_id, $type);
        echo $result;
    }
}


