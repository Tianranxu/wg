<?php
/*************************************************
 * 文件名：AccessController.class.php
 * 功能：     访问控制控制器
 * 日期：     2015.7.23
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;

use Think\Controller;

class AccessController extends Controller
{

    protected $userID;
    // 登入用户id
    protected $userName;
    // 登入用户名
    protected $code;
    // 登入账号
    protected $companyID;
    // 企业ID
    protected $companyName;
    // 企业名字
    protected $loginSig;
    // 登入状态
    protected $menuName;
    // 此控制器下所有操作 二维数组
    protected $power;
    // 用户佣有的权限
    protected $contrName;
    // 控制器名称
    protected $actionName;
    // 控制器行为名称
    protected $ruleID;

    protected $sessionId;
    // 服务器sessionId
    public function _initialize()
    {
        MODULE_NAME; // 当前模块名｛大
        MODULE_PATH; // 当前模块路径
        CONTROLLER_NAME; // 当前控制器名｛大
        ACTION_NAME; // 当前操作名｛小
                     
        // 获取cookie中的PHPSESSID
        $this->sessionId = $_COOKIE['PHPSESSID'];
        // 根据cookie中的PHPSESSID查询是否有用户存在
        $userMod = D('user');
        $userResult = $userMod->find_user_by_session_id($this->sessionId);
        if (! $userResult) {
            session(null);
            $this->error('对不起，请登入！！', U('User/login'));
        }
        // 查询当前session里是否存在用户ID
        $this->userID = session('user_id');
        if (! $this->userID) {
            // session中无用户ID，将用户ID写入session
            $this->sessionId = session_id();
            $sessionResult = $userMod->updateSession($userResult, $this->sessionId);
            session('user_id', $userResult);
            cookie('PHPSESSID', session_id(), 30758400);
            $this->userID = $userResult;
        }
        
        $userInfo = $userMod->find_user_info($this->userID);
        $this->userName = $userInfo['name'];
        $this->code = $userInfo['code'];
        $compid = I('get.compid', '');
        $this->companyID = empty($compid)?I('post.companyid', ''):$compid;
        
        $this->actionName = MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME;
        $this->contrName = MODULE_NAME . '/' . CONTROLLER_NAME;
        $this->power = $this->checkAuthority($this->userID, $this->companyID);
        $this->ruleID = array();
        foreach ($this->power as $var) {
            $moduleArr[] = $var['name'];
            $this->ruleID[] = $var['id'];
        }
        if (! in_array($this->actionName, $moduleArr)) {
            $this->error('对不起，你无此权限进入！！', U('User/login'));
        }
    }

    public function checkAuthority($userID, $companyID)
    { // 检查出所佣有权限
        $model = D('access');
        $ruleArray = $model->selectRole($userID, $companyID);
        $result = $model->selectRule($ruleArray);
        return $result;
    }
}