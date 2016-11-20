<?php
/**
 * 文件名：AuthModel.class.php  
 * 功能：系统角色和权限规则模型
 * 作者：XU    
 * 日期：2015/07-30
 * 版权：Copyright @2015 风馨科技 All Right Reserved
 */

namespace Home\Model;
use Think\Model;

class AuthModel extends Model{
    /**
     * 获取所有的角色（id和name）
     */
    public function getRole(){
        $where = array(
            'sr.status' => 1
        );
        $result = $this->table(array('fx_sys_role'=>'sr'))->field(array('id','name','type'))->where($where)->select();
        return $result;
    }
    
    
    /**
     * 检测新增的角色名是否重复
     */
    public function checkRole($rn,$type){
        $where = array(
            'sr.name' => $rn,
            'sr.type' => $type
        );
        $result = $this->table(array('fx_sys_role'=>'sr'))->field('sr.id')->where($where)->find();//var_dump($result);exit;
        return $result;
    }
    
    /**
     * 根据角色的id获取其原有的所有权限的id
     */
    public function getRuleId($ri){
        $where = array(
            'sr.id' => $ri
        );
        $result = $this->table(array('fx_sys_role'=>'sr'))->field('rule_id')->where($where)->find();
        return $result;
    }
}