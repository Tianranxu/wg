<?php
/*************************************************
 * 文件名：WorkstationController.class.php
 * 功能：    物管首页控制器
 * 日期：     2015.10
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
use Think\Model;
class WorkstationController extends AccessController{


    protected $_userModel;

    protected $_companyModel;

    /**
     * 初始化
     */
    public function _initialize()
    {
        parent::_initialize();
        
        $this->_userModel = D('user');
        $this->_companyModel = D('company');
    }
    public function index(){
        // 接收企业ID
        $compId = I('get.compid', '');
        // 判断是否有企业ID
        if (! $compId) {
            $this->error('对不起，你无此权限进入！！', U('User/login'));
            exit();
        }
        
        // 判断是否有用户ID
        if ($this->userID) {
            // TODO 查询用户信息，以及头像url
            $userInfo = $this->_userModel->find_user_info($this->userID);
            $userPhotoUrl = $this->_userModel->find_user_photo($userInfo['photo'])['url_address'];
            // TODO 查询该企业的名称
            $companyInfo = $this->_companyModel->selectCompanyDetail($compId);
        
            $this->assign('compid', $compId);
            $this->assign('userInfo', $userInfo);
            $this->assign('photo', $userPhotoUrl);
            $this->assign('companyInfo', $companyInfo);
        } else {
            $this->error('对不起，你无此权限进入！！', U('User/login'));
            exit();
        }
        $this->display();
    }
}