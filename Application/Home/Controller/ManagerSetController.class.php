<?php
/*************************************************
 * 文件名：ManagerSetController.class.php
 * 功能：     管理员控制器
 * 日期：     2016.03.02
 * 作者：     XU
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/

namespace Home\Controller;
use Think\Controller;

class ManagerSetController extends AccessController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
    * 管理员设置界面
    */
    public function index(){
        $compid = I('get.compid', '');
        $type = I('get.type', '');
        $managers = ($type == C('COMPANY_TYPE.REPAIR')) ? D('Wxrepair')->getManagerByCompid($compid) : D('WXuser')->getManagerByCompid($compid);
        //根据管理员的类型将数据分成对应数组
        $data = [];
        foreach ($managers as $key => $manager) {
            switch ($manager['user_type']) {
                case C('WXUSER_TYPE.FEEDBACK_MG'):
                    $data['feedback']['list'][$manager['phone'].$manager['name']] = $manager; 
                    break;
                case C('WXUSER_TYPE.FORM_MG'):
                    $data['form']['list'][$manager['phone'].$manager['name']] = $manager; 
                    break;
                case C('WXUSER_TYPE.REPAIR_MG'):
                    $data['repair']['list'][$manager['phone'].$manager['name']] = $manager; 
                    break;
                case C('WXUSER_TYPE.CHARGE_MG'):
                    $data['charge']['list'][$manager['phone'].$manager['name']] = $manager; 
                    break;    
            }
        }
        foreach ($data as $key => $value) {
            $data[$key]['count'] = count($value['list']);
        }
        $this->assign('data', $data);
        $this->assign('compid', $compid);
        $this->assign('type', $type);
        $this->display();
    }

    /**
    * 设置物业公司和工作站管理员
    */
    public function setPropertyManager(){
        $phone = I('post.phone');
        $type = I('post.type');
        $compid = I('post.compid');
        $cm_type = I('post.cm_type');
        $wxuserModel = D('WXuser');
        $users = ($cm_type == C('COMPANY_TYPE.WORKSTATION')) ? $wxuserModel->checkuser($phone) : $wxuserModel->checkuser($phone, $compid);
        if (empty($users)) {
            retMessage(false, null, '找不到相关人员，请关注微信公众号并在个人中心添加手机号', '', 4001);
        }
        $record = [];
        foreach ($users as $key => $user) {
            $record[$key] = [
                'cm_id' => $compid,
                'wu_id' => $user['id'],
                'type' => $type,
            ];
        }
        $result = $wxuserModel->addManager($record);
        if ($result == -1) retMessage(false, null, '该人员已设置<br/>请勿重复添加', '', 4002);
        ($result) ? retMessage(true, $result) : retMessage(false, null, '添加失败', '', 4003);
    }

    /**
    * 设置维修公司管理员
    */
    public function setRepairManager(){
        $data = [
            'user_type' => I('post.type'),
            'create_time' => date('Y-m-d H:i:s'),
        ];
        $compid = I('post.compid');
        $phone = I('post.phone');
        $result = D('Wxrepair')->setManager($data, $phone, $compid);
        ($result) ? retMessage(true, $result) : retMessage(false, null, '添加失败<br/>请关注微信公众号并注册', 4001);
    }
}