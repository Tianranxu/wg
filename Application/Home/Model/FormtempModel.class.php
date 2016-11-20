<?php
/*
 * 文件名：FormtempModel.class.php
 * 功能：表单模型
 * 作者：XU
 * 日期：2015-10-21
 * 版权：CopyRight @ 2015 风馨科技 All Rights Reserved
 */
namespace Home\Model;

use Think\Model;

class FormtempModel extends Model
{

    protected $tableName = 'form_group_temp';

    /**
     * 根据表单ID查询表单分组记录
     * 
     * @param string $formId
     *            表单ID
     * @return \Think\mixed
     */
    public function getTempByFormId($formId)
    {
        $where = array(
            'form_id' => $formId
        );
        $result = $this->where($where)->select();
        return $result;
    }
    
    public function updateFormIdsByFormId(array $formIds,$formId){
        $where=array(
            'form_id'=>array('in',$formIds)
        );
        $data=array(
            'form_id'=>$formId
        );
        $result=$this->where($where)->save($data);
        if (!$result) return false; return true;
    }

    public function addGroup($data){
        $groupModel = M('sys_group');
        return $groupModel->add($data);
    }

    public function getAllGroup($user_id,$compid){
        $where = array(
            'user_id' => $user_id,
            'cm_id' => $compid,
            'type' => 4
        );
        $field = array(
            'id','title','user_id','cm_id'
        );
        return $this->table('fx_sys_group')->where($where)->field($field)->select();
    }

    public function getFormGroup(array $form_ids,$user_id){
        $table = array(
            'fx_sys_group' => 'sg',
            'fx_form_group_temp' => 'fg'
        );
        $where = array(
            'fg.user_id' => $user_id,
            'fg.form_id' => array('in',$form_ids),
            'fg.group_id = sg.id'
        );
        $field = array(
            'fg.form_id','fg.group_id','sg.title'
        );
        return $this->table($table)->where($where)->field($field)->order('fg.group_id')->select();
    }

    public function changeGroup($group_id,$form_id,$user_id){
        $where = array(
            'form_id' => $form_id,
            'user_id' => $user_id
        );
        $result = $this->where($where)->delete();
        if ($group_id != -1) {
            $where['group_id'] = $group_id;
            $result = $this->add($where);
        }
        return $result;
    }

    public function deleteGroup($group_id){
        $where = array(
            'group_id' => $group_id
        );
        $temp_res = $this->where($where)->delete();
        $group_res = $this->table('fx_sys_group')->where(array('id'=>$group_id))->delete();
        if ($temp_res && $group_res) {
            return true;
        }else{
            return false;
        }
    }
    
    public function editGroup($data){
        return M('sys_group')->save($data);
    }

    
}