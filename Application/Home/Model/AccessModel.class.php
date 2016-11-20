<?php
/*************************************************
 * 文件名：AccessModel.class.php
 * 功能：     访问控制模型
 * 日期：     2015.7.23
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;
use Think\Model;

class AccessModel extends Model{
    
    protected $trueTableName = 'fx_sys_role';
    
    /**
     * 查询出所属角色 
     * @param string $userid       用户id
     * @param string $companyid    企业id
     */
    public function selectRole($userid,$companyid){       //查询出所属角色 (同一个用户在同一个企业有多种角色)
        $field  = 'r.id,r.name,r.rule_id as ruleID,r.type';
        if($companyid==NULL){
            $where = "rt.user_id=%d AND rt.role_id=r.id AND r.status=1";
        }else{
            $where = "rt.user_id=%d AND rt.cm_id=%d AND rt.role_id=r.id AND r.status=1";
        }
        $table  = array('fx_user_role_temp'=>'rt','fx_sys_role'=>'r');
        $result = $this->table($table)
            ->field($field)
            ->where($where,$userid,$companyid)
            ->distinct(true)
            ->select();
        return $result;
    }
    
    /**
     * 查询出权限详情
     * @param array $ruleArray         权限数组
     */
    public function selectRule( array $ruleArray){                    //查询出权限详情
        
        $field = 'ar.id,ar.type,ar.name,ar.module,ar.title,ar.rule_cond as attach';
        $table = array('fx_sys_auth_rule'=>'ar');

        $ruleID = array();
        foreach($ruleArray as $var){
            $ruleID = array_merge($ruleID, explode(",",$var['ruleID']));
        }
        $ruleID = array_unique($ruleID);
        $ruleIdIn =  "('". implode("','", $ruleID) ."')";
        $result = $this->table($table)->field($field)->where("id in {$ruleIdIn} AND status = '1'")->select(); 
        return $result;
    }
    
    /**
     * 查出此模块下所有操作
     * @param string $moduleName         模块名字
     */
    public function selectModule($moduleName){                                                             //查出此模块下所有操作
        $field  = 'ar.id,ar.name,ar.title';
        $table  = array('fx_sys_auth_rule'=>'ar');
        $where  = "ar.module='%s'";
        $result = $this->table($table)->field($field)->where($where,$moduleName)->select();
        return $result;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}