<?php 
/*
* 文件名：AuthorizeController.class.php
* 功能：授权控制器
* 日期：2015-11-26
* 作者：XU
* 版权：Copyright @ 2015 风馨科技 All Rights Reserved
*/
namespace Home\Controller;
use Think\Controller;

class AuthorizeController extends Controller{
    protected $sessionId;
    protected $r_openid;
    protected $srid;
    protected $compid;
    protected $UNCHECK_ACTIONS = [
        'register',
        'authorize',
        'authorized',
        'repCompay',
        'repsubmit'
    ];

    public function  _initialize(){
        header("Content-Type:text/html;charset=utf-8");
        $url = $_SERVER['REQUEST_URI'];
        $this->sessionId = ($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : '';
        $this->r_openid = session('r_openid');
        $this->srid = session('srid');
        if (in_array(ACTION_NAME, $this->UNCHECK_ACTIONS)) {
            return ;
        }
        $this->authorize($url);
    }
    private function begin_authorize($url) {
        $return_url = "http://{$_SERVER['HTTP_HOST']}/Authorize/authorized/?url=".urlencode("http://{$_SERVER['HTTP_HOST']}{$url}");
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.C('REPAIR_PUBLICNO.APPID').'&redirect_uri='.urlencode($return_url).'&response_type=code&scope=snsapi_userinfo#wechat_redirect';
        redirect($url);
    }

    //授权检测
    public function authorize($url){
        $repairmanModel = D('Wxrepair');
        if (!$this->sessionId)
            $this->begin_authorize($url);

        $repairman = $repairmanModel->getRepairmanBySessionId($this->sessionId);
        if(!$repairman) {
            $this->begin_authorize($url);
        }
        $this->compid = $repairman['cm_id'];
        $this->sessionId = session_id();
        cookie('PHPSESSID', $this->sessionId, 307584000);
        session('r_openid', $repairman['openid']);
        session('srid',$repairman['id']);
        $this->r_openid = session('r_openid');
        $this->srid = session('srid');
        $repairmanModel->saveRepairman(array('id'=>$repairman['id'], 'session_id'=>$this->sessionId));
        if (ACTION_NAME !='register' && $repairman['status'] == C('REPAIR_STATUS.NOT_REGI')) {
            $this->redirect('/WXRepair/register');
        }
        return true;
    }


    //授权结束
    public function authorized(){
        $redirect_uri = I('get.url');
        $code = I('get.code','');
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.C('REPAIR_PUBLICNO.APPID').'&secret='.C('REPAIR_PUBLICNO.APPSECRET').'&code='.$code.'&grant_type=authorization_code';
        $weixinModel = D('weixin');
        $result = json_decode(file_get_contents($url), true);
        if ($result) {
            $this->sessionId = session_id();
            cookie('PHPSESSID', $this->sessionId, 307584000);
            session('r_openid',$result['openid']);
            $this->r_openid = session('r_openid');

            $repairerModel = D('Wxrepair');
            $check = $repairerModel->checkExist($result['openid']);

            if($check){
                $data['id'] = $check['id'];
                $data['session_id'] = $this->sessionId;
                $data['access_token'] = $result['access_token'];
                $data['refresh_token'] = $result['refresh_token'];
                $repairerModel->saveRepairman($data);
                session('srid',$check['id']);
                $this->compid = $check['cm_id'];
                $this->srid = session('srid');
                if (ACTION_NAME !='register' && $repairman['status'] == -1) {
                    $this->redirect('/WXRepair/register');
                }
            }else{
                $data = array(
                    'session_id' => $this->sessionId,
                    'access_token' => $result['access_token'],
                    'refresh_token' => $result['refresh_token'],
                    'openid' => $result['openid'],
                    'expires' => time(),
                    'status' => '-1',
                );
                $srid = $repairerModel->addRepairer($data);
                session('srid',$srid);
                $this->srid = session('srid');
            }
            redirect($redirect_uri);
        } else {
            echo "发生错误!";
        }
    }

}
