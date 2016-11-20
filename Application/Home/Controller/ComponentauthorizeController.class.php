<?php 
/*
* 文件名：ComponentauthorizeController.class.php
* 功能：第三方平台授权控制器
* 日期：2015-12-02
* 作者：XU
* 版权：Copyright @ 2015 风馨科技 All Rights Reserved
*/

namespace Home\Controller;
use Think\Controller;
use Emoji\emoji;
class ComponentauthorizeController extends Controller{
    protected $session_id;
    protected $appid;
    protected $openid;
    protected $umid;
    protected $compid;
    protected $UNCHECK_ACTIONS = array(
        'authorized',
        'authorize',
        'info',
        'save_user_info',
        'checkInfo',
        'update',
        'beginAuthiorize',
        'detail',
    );

    public function _initialize(){
        header("Content-Type:text/html;charset=utf-8");
        $this->umid = I('get.umid','');
        if (empty($this->umid)) {
            exit();
        }
        $publicno = D('Publicno')->getPublicnoByUmid($this->umid);
        $this->appid = $publicno['appid'];
        $this->compid = $publicno['cm_id'];
        $url = $_SERVER['REQUEST_URI'];
        if (in_array(ACTION_NAME, $this->UNCHECK_ACTIONS)) {
            return;
        }
        $this->session_id = $_COOKIE['SESSIONID'];
        $this->openid = session('openid');
        $this->authorize($url);
    }

    public function beginAuthorize($url){
        $return_url = "http://{$_SERVER['HTTP_HOST']}/Componentauthorize/authorized/?umid={$this->umid}&url=".urlencode("http://{$_SERVER['HTTP_HOST']}{$url}");
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->appid.'&redirect_uri='.urlencode($return_url).'&response_type=code&scope=snsapi_userinfo&component_appid='.C('CHUYUN_APPID').'#wechat_redirect';
        redirect($url);
    }

    //更新session和数据库
    public function update($wxuser){
        $wxuserModel = D('Wechatuser');
        $this->session_id = session_id();
        cookie('SESSIONID',$this->session_id,307584000);
        session('appid',$this->appid);
        session('openid',$this->openid);
        $wxuserModel->saveWxuser(array(
            'id' => $wxuser['id'],
            'session_id' => $this->session_id,
            'cm_id' => $this->compid,
        ));
    }

    public function authorize($url){
        if (!$this->session_id)
            $this->beginAuthorize($url);
        $wxuserModel = D('Wechatuser');
        $wxuser = $wxuserModel->getUser($this->session_id,$this->compid);
        if (!$wxuser) 
            $this->beginAuthorize($url);
        $this->openid = $wxuser['openid'];
        $this->update($wxuser);
        return true;
    }

    public function authorized(){
        $return_url = I('get.url');
        $code = I('get.code');
        $weixinModel = D('weixin');
        $wxuserModel = D('Wechatuser');
        $component_access_token = $weixinModel->get_component_access_token(C('CHUYUN_APPID'),C('CHUYUN_APPSECRET'));
        $url = 'https://api.weixin.qq.com/sns/oauth2/component/access_token?appid='.$this->appid.'&code='.$code.'&grant_type=authorization_code&component_appid='.C('CHUYUN_APPID').'&component_access_token='.$component_access_token;
        $result = json_decode(file_get_contents($url), true);
        if ($result['access_token']) {
            $this->openid = $result['openid'];
            $user = $wxuserModel->getUserByOpenid($this->openid);
            if ($user['nickname'] && $user['headimgurl']) {
                //若之前授权过
                $this->update($user);
            }else{
                $getUserUrl = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$result['access_token'].'&openid='.$this->openid.'&lang=zh_CN';
                $userInfo = json_decode(file_get_contents($getUserUrl), true);
                vendor('Emoji.emoji');
                $emoji = new emoji();
                $this->session_id = session_id();
                cookie('SESSIONID',$this->session_id,307584000);
                session('appid',$this->appid);
                session('openid',$this->openid);
                $userInfo['cm_id'] = $this->compid;
                $userInfo['session_id'] = $this->session_id;
                $userInfo['nickname'] = $emoji->emoji_unified_to_html($userInfo['nickname']);
                unset($userInfo['privilege']);
                $result = D('Wechatuser')->addUser($userInfo);
            }
            redirect($return_url);
        }else{
            echo '数据获取错误'.$result['errcode'].$result['errmsg'];
            exit;
        }
    }

    
}