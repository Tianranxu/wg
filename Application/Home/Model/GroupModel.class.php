<?php
/*************************************************
 * 文件名：GroupModel.class.class.php
 * 功能：     群组模型
 * 日期：     2015.7.23
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;
use Think\Model;

class GroupModel extends Model{

    protected $trueTableName = 'fx_sys_group';
    
    
    /**
     * 根据id查询分组详情
     * @param int $gid         分组id
     */
    public function selectGroupDetail($gid){
        $field  = 'id,type,title,description,user_id as uid,create_time as ctime';
        $where  = "id=%d AND status=1";
        $result = $this->field($field)->where($where, $gid)->find();
        return $result;
    }
    
    /**
     * 根据用户ID查出默认分组ID
     * @param int $uid         用户id
     */
    public function selectDefaultgid($uid){
        $field  = 'id,type,title,description,user_id as uid,create_time as ctime';
        $where  = "user_id=%d AND type=1 AND status=1";
        $result = $this->field($field)->where($where, $uid)->find();
        return $result;
    }
    /* 根据企业ID和分组类型查询所有记录
     * @param int $userIDs           所有用户ID * 
     * @param int $type              分组类型 1:默认分组  2：自定义分组 3：停止 分组  0：所有
     */
    public function selectGroupIdForType($userIDs, $type=0){
        $field = 'id as stop_gid,user_id';
        switch($type){
            case 1:
                $where = array(
                  'user_id' => array('in', $userIDs),
                  'type' => 1
                   );
                break;
            case 2:
                  $where = array(
                      'user_id' => array('in', $userIDs),
                      'type' => 2
                       );
                break;
            case 3:
                  $where = array(
                      'user_id' => array('in', $userIDs),
                      'type' => 3
                       );
                break;
            default:
                  $where = array(
                      'user_id' => array('in', $userIDs),
                       );
                break;
        }    
        $result = $this->field($field)->where($where)->select();
        return $result;
    
    }
    
    /**
     * 查询表单所属分组
     * @param string $cmId      企业ID
     * @param $type $userId     用户ID
     * @param integer $type     组类型
     * @return \Think\mixed
     */
    public function findFormGroup($cmId,$userId,$type=1){
        $where=array(
            'gt.cm_id'=>$cmId,
            'gt.user_id'=>$userId,
            'gt.group_id=g.id',
            'g.group_type'=>2,
            'g.type'=>$type
        );
        $table=array(
            'fx_sys_group'=>'g',
            'fx_comp_group_temp'=>'gt'
        );
        $result=$this->table($table)->where($where)->getField('g.id');
        return $result;
    }
}