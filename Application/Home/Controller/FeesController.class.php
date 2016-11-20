<?php
/*************************************************
 * 文件名：FeesController.class.class.php
 * 功能：     收费管理控制器
 * 日期：     2015.7.23
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
use Think\Model;
class FeesController extends AccessController{
    
    protected $_userModel;
    
    protected $_companyModel;
    
    protected $_cityModel;
    
    protected $_propertyModel;
    public function _initialize() {
        parent::_initialize();
        $this->_userModel = D('user');
        $this->_companyModel = D('company');
        $this->_cityModel = D('city');
        $this->_propertyModel = D('property');
    }
    
    public function index(){
        $compid = I('get.compid');
        
        if ($this->userID) {
            // TODO 查询用户信息，以及头像url
            $userInfo = $this->_userModel->find_user_info($this->userID);
            $userPhotoUrl = $this->_userModel->find_user_photo($userInfo['photo'])['url_address'];
            // TODO 查询该企业的名称
            $companyInfo = $this->_companyModel->selectCompanyDetail($compid);
        
            $this->assign('compid', $compid);
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