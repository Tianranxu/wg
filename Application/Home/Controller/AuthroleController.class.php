<?php
/***
 * 标题：AuthroleController.class.php  
 * 功能：系统角色控制器
 * 作者：XU    
 * 时间：2015-07-28
 * 版权：Copyright ? 2014-2015 风馨科技 All Right Reserved
 */

namespace Home\Controller;
use Think\Controller;

class AuthroleController extends AccessController{
    //根据登录的用户id及其企业id(系统超管则无)显示企业或系统(系统超管专属)下的角色
    public function index(){
        if($this->userID!=1){
            $this->error("对不起，你无权进入！",U('User/login'));
        }
        //实例化数据模型
        $auth           = D('Auth');
        //获取所有的角色
        $allRoles     = $auth->getRole();
        foreach ($allRoles as $key => $role) {
            $data[$role['type']][] = $role;
        }
        $this->assign('data',$data);
        $this->display();
    }
    
    //系统角色或业务角色的添加
    public function add(){
        if($this->userID!=1){
            $this->error("对不起，你无权进入！",U('User/login'));
        }
        //实例化数据模型
        $auth         = D('Auth');
        //获取新的系统角色名称
        $data['name'] = I('post.rolename');//var_dump($data['name']);exit;
        $data['type'] = I('post.type','');
        $type         = I('post.type','');
        //若角色名称重复，则提示错误并返回
        $flag         = $auth->checkRole($data['name'],$type);
        if($flag){
            retMessage(false,2,"角色名已存在，请勿重复添加或恢复角色！","",4001);
        }
        
        //添加角色创建时间
        $data['create_time'] = date('Y-m-d H:i:s',time());
        $role                = M('sys_role','fx_');
        if($role->add($data)){
            retMessage(true,1,"添加成功!","",2000);
        }else{
            retMessage(false,2,"添加失败！","",4001);
        }
        die(1);
    }
    
    //系统角色或业务角色的删除
    public function delete(){
        if($this->userID!=1){
            $this->error("对不起，你无权进入！",U('User/login'));
        }
        
        $id              = I('post.roleid','');
        $role            = M('sys_role','fx_');
        if($role->delete($id)) {
            retMessage(true,1,"删除成功!","",2000);
        }else{
            retMessage(false,2,"删除失败！","",4001);
        }
    }
    
    //根据登录的用户id及其企业id进行系统角色或业务角色的编辑（即权限的分配）
    public function modify(){
        if($this->userID!=1){
            $this->error("对不起，你无权进入！",U('User/login'));
        }
        
        //获取角色id
        $id             = I('get.id');
        $where['status'] = 1;
        $allRule        = M('sys_auth_rule','fx_')->where($where)->select();
        $auth           = D('Auth');
        //获取角色已有的权限规则
        $rule_id        = $auth->getRuleId($id);
        $rule_arr       = explode(',',$rule_id['rule_id']);
        foreach ($allRule as $k=>$v){
            $v['state'] = -1;
        }
        foreach ($allRule as $k=>$v){
            foreach ($rule_arr as $uk=>$uv){
                if($uv == $v['id']){
                    $v['state'] = 1;
                    break;
                }
            }
            $data[$v['module']][] = $v;
        }
        $this->assign('rule',$data);
        $this->assign('roleid',$id);
        $this->display();
    }
    public function doModify(){
        if($this->userID!=1){
            $this->error("对不起，你无权进入！",U('User/login'));
        }
        
        //收集表单信息
        $auth       = I('post.select','');
        $data['id'] = I('get.id');
        //将所选的权限规则组合成字符串
        $data['rule_id'] = implode(',', $auth);
        //实例化表格模型
        $role = M('sys_role','fx_');
        if($role->save($data)){
            $this->success("修改成功！",U('index'));
        }else{
            $this->error("修改失败！",U('index'));
        }
    }
}