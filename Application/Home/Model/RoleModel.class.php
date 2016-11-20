<?php
/*************************************************
 * 文件名：RoleModel.class.php
 * 功能：     模型
 * 日期：     2015.7.23
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;
use Think\Model;

class RoleModel extends Model{

    protected $trueTableName = 'fx_sys_role';
    
    /**
     * 查询出所有业务角色
     * @param $type          角色类型
     */
    public function allRole($type=''){
        $where=['status'=>1];
        if($type) $where['type']=$type;
        $result = $this->where($where)->select();
        return $result;
    }
    
    /**
     * 查询业务角色名称
     * @param       $rid     角色id
     */
    public function roleName($rid){
        $result = $this->field('id,name')->where("id=%d AND status=1",$rid)->find();
        return $result;
    }
    /**
     * 查询出所有除了系统角色的角色
     */
    public function allRoleExcept(){
        $result = $this->field('id,name,type')
            ->where("type<>1 AND status=1")
            ->select();
        return $result;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}