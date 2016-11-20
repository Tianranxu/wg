<?php
/*************************************************
 * 文件名：GrouptempModel.class.php
 * 功能：     群组和企业中间模型
 * 日期：     2015.7.23
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;
use Think\Model;

class GrouptempModel extends Model{

    protected $trueTableName = 'fx_comp_group_temp';
    
    /**
     * 根据企业id和群组id查询所有人员信息
     * @param int $companyID         企业id
     * @param int $groupID           群组id
     */
    public function selectPeople($companyID){
        $field = 'u.id as id,u.name,u.code,u.last_login_ip as ip,u.modify_time as mtime';
        $where = "gt.cm_id=%d AND gt.user_id=u.id";
        $table = array('fx_comp_group_temp'=>'gt','fx_sys_user'=>'u');
        $result= $this->table($table)->distinct(true)->field($field)->where($where,$companyID)->order('u.create_time desc')->select();
        return $result;
    }

    /**
     * 根据企业ids查询所有人员信息
     * @param int $companyIDs         企业id
     * @param int $groupID           群组id
     */
    public function selectPeopleCountByCompanies($companyIDs){
        $map = array(
            'gt.cm_id' => array('in', $companyIDs)
        );
        $field = 'gt.cm_id, count(*) as count';
        $table = array('fx_comp_group_temp'=>'gt');
        $result= $this->table($table)->field($field)->where($map)->group('gt.cm_id')->select();
        return $result;
    }
    
    /**
     * 根据企业id和用户id查询所有属分组ID
     * @param int $companyID         企业id
     * @param int $userID            用户id 默认为所有人
     */
    public function selectgroupID($companyID,$userID=0){
        if($userID==0){
            $result = $this->field('group_id,user_id')->where(array('cm_id'=>$companyID))->select();
        }else{
            $result = $this->field('group_id')->where(array('cm_id'=>$companyID,'user_id'=>$userID))->find();
        }
        return $result;
    }
    /**
     * 根据用户id查询所有属企业ID
     * @param int $userID            用户id
     */
   public function selectAllCompany($userID){
        $field = 'cm_id';
        $where = "user_id=%d"; 
        $result= $this->field($field)->where($where,$userID)->select();
        return $result;
    }
    /**
     * 查询此用户下所有企业
     * @param int $userID          用户id
     */
    public function selectcompanyforUser($userID){
    
        $field  = 'cm.id,cm.name,cm.cm_type as category,cm.number';
        $where  = "cg.user_id=%d AND cm.status=1 AND is_delete=1 AND cm.id=cg.cm_id ";
        $table  = array('fx_comp_group_temp'=>'cg','fx_comp_manage'=>'cm');
        $result = $this->table($table)->field($field)->where($where,$userID)->select();
        return $result;
    }
    /**
     * 根据用户id和分组ID查询是否存在
     * @param int $userID            用户id
     * @param int $compID            企业id
     */
    public function be_exist($userID,$compID){
        $field = 'group_id';
        $where = "user_id=%d AND cm_id=%d";
        $result= $this->field($field)->where($where,$userID,$compID)->find();
        return $result;
    }
    /* 根据分组ID查询所有记录
    * @param int $groupID           分组id
    */
    public function be_group($groupID){
        $field = 'cm_id,user_id';
        $where = "group_id=%d";
        $result= $this->field($field)->where($where,$groupID)->select();
        return $result;
    }
    /* 根据分组ID更新所有记录
     * @param int $groupID           分组id
     * @param int $def_groupID           默认分组id
     */
    public function update_group($groupID, $def_groupID){
        $result = $this->where("group_id=%d",$groupID)
                        ->save(array('group_id'=>$def_groupID));
        if($result===0){          //分组下没任何公司 
           
            return true;
        }else{
            return $result;
        }
        
    }
    /* 根据分组ID停止企业
     * @param int $groupID           分组id
     *  @param int $type             更新类型id 1：恢复企业  3：停止企业
     *
     */
    public function updateGroupAll($groupID, $type=1){
        
        $sql = "update fx_comp_group_temp
                inner join fx_sys_group on fx_comp_group_temp.user_id = fx_sys_group.user_id
                set group_id = fx_sys_group.id 
                where fx_comp_group_temp.cm_id ={$groupID} and fx_sys_group.type ={$type}";
        $result = $this->execute($sql);       
        return $result;
    }

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}