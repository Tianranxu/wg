<?php
/*************************************************
 * 文件名：PersonController.class.php
 * 功能：     素材管理控制器
 * 日期：     2015.9.28
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
use Think\Controller;

class PersonController extends WXClientController{
    protected $_personModel;
    protected $compid;
    protected $openid;

    /**
     * 初始化
     */
    public function _initialize(){
        header("Content-Type:text/html;charset=utf-8");
        parent::_initialize();
        $this->_personModel=D("person");
    }
    
    /**
     * 获取access_token
     */
    public function get_access_token(){
        $this->_weixinModel=D("weixin");
        $access_token = $this->_weixinModel->get_authorizer_access_token($this->compid);
        if ($access_token == -1) {
            $this->error("授权不可用或已过期，请重新授权",U('publicno/access',array('compid'=>$this->compid)));
        }
        return $access_token;
    }
    
    /**
     * 个人中心
     */
    public function index(){
        //获取access_token
        $access_token=$this->get_access_token($this->compid);
        //根据openID查询是否有该用户的信息
        $userInfo=$this->_personModel->getWeixinUserInfo($access_token,$this->openid,$this->compid);

        $this->assign('compid',$this->compid);
        $this->assign('userInfo',$userInfo);
        $this->display();
    }
    
    /**
     * 完善个人信息
     */
    public function info(){
        //获取access_token
        $access_token=$this->get_access_token($this->compid);
        //根据openID查询是否有该用户的信息
        $userInfo=$this->_personModel->getWeixinUserInfo($access_token,$this->openid,$this->compid);
        $this->assign('userInfo',$userInfo);
        $this->assign('redirectUrl',$_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:null);
        $this->display();
    }
    
    /**
     * 执行完善个人信息
     */
    public function save_user_info(){
        //接收数据
        $getData=array(
            'umid'=>I('get.umid',''),
            'nickname'=>I('post.nickname',''),
            'mobile'=>I('post.mobile',''),
            'redirectUrl'=>I('post.redirectUrl',null),
        );
        if (!$getData['umid'] || !$getData['nickname'] || !$getData['mobile']){
            retMessage(false,null,'未接收到数据','未接收到数据',4001);
            exit;
        }
        
        //保存用户信息
        $result=$this->_personModel->saveUserInfo($this->compid,$this->openid,$getData['nickname'],$getData['mobile']);
        $result?retMessage(true,$getData['redirectUrl']): retMessage(false,null,'保存失败','保存失败',4002);
    }
}