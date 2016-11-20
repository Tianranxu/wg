<?php
/*************************************************
 * 文件名：RoletempModel.class.php
 * 功能：     模型
 * 日期：     2015.7.23
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;
use Think\Model;

class RoletempModel extends Model{

    protected $trueTableName = 'fx_user_role_temp';
    
    /** 
     * 根据用户ID和企业IDs,查询用户在这些企业下的角色
     */
    public function selectRuleIdByUserInCompany($userId, $companyIDs) {
        $map = array(
            'rt.cm_id' => array('in', $companyIDs),
            'rt.user_id' => $userId,
            'r.status' => 1,
            'r.type' => array('neq', 1)
        );
        $field = 'rt.user_id,rt.cm_id,r.rule_id';
        $table = array('fx_user_role_temp'=>'rt','fx_sys_role'=>'r');
        $result= $this->table($table)->field($field)->where($map)->where('rt.role_id = r.id')->select();
        return $result;
    }


    /**
     * 查询出该企业下所有管理员   
     * @param string $companyid    企业id
     * @param string $userid    用户id
     * @param string $admin    企业管理员id
     */
    
    public function selectManage($companyid,$userid,$admin){
        $result = $this->where("cm_id=%d AND role_id=".$admin,$companyid)->select();
        $manage = $this->where("cm_id=%d AND user_id=%d",$companyid,$userid)->select();
        $count  = count($result);
        foreach($manage as $var){
            $roleid[] = $var['role_id'];
        }
        if(in_array($admin,$roleid)){
            $result['count']  = $count;
            $result['Manage'] = true;
        }else{
            $result['count']  = $count;
            $result['Manage'] = false;
        }
        return $result;
    }
    /**
     * 编辑用户角色 
     * @param string $uid    用户id
     * @param string $cid    企业id    
     */
     public function editpowe($uid,$cid){
        $field = 'role_id,user_id,cm_id';
        $where = "user_id=%d AND cm_id=%d";
        $result= $this->field($field)->where($where,$uid,$cid)->select();     
        return $result;
         
         
    }
    /**
     * 查询用户所有角色和企业名 
     * @param string $uid    用户id
     */
    public function inviteUser($uid){
        $field = 'role_id as rid,cm_id as cid';
        $where = "user_id=%d AND cm_id<>''";
        $result= $this->field($field)->where($where,$uid)->select();
        return $result;
    }

    /**
     * 根据企业ID和角色ID查找相关用户
     * @param array $cmIds 企业ID
     * @param array $roleIds 角色ID
     * @return mixed
     */
    public function getUsersByCompAndRole(array $cmIds, array $roleIds)
    {
        $where = ['cm_id' => ['in', $cmIds], 'role_id' => ['in', $roleIds]];
        $userLists = $this->where($where)->select();
        return $userLists;
    }
}