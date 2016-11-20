<?php
/***
 * 文件名：ImportModel.class.php
 * 功能：导入模型
 * 作者：XU
 * 日期：2015-11-23
 * 版权：Copyright 2015 @ 风馨科技 All Rights Reserved
 */

namespace Home\Model;
use Think\Model;

class ImportModel extends Model{
    protected $tableName = 'import_log';
    
    public function getImportLog($compid,$ilType){
        $table = array(
            'fx_import_log' => 'il',
            'fx_sys_user' => 'su',
        );
        $where = array(
            'il.cm_id' => $compid,
            'il.il_type' => $ilType,
            'il.user_id = su.id',
        );
        $field = array(
            'il.id' => 'id',
            'il.file_name' => 'file_name',
            'il.status' => 'status',
            'il_type' => 'type',
            'su.code' => 'code',
            'su.name' => 'user_name',
            'il.create_time' => 'create_time',
            'il.import_time' => 'import_time',
            'il.success' => 'success',
            'il.failures' => 'failures',
            'il.remark' => 'remark',
        );
        return $this->table($table)->where($where)->field($field)->order(array('il.create_time'=>'desc'))->select();
    }

/**
     * 添加导入日志（状态为-1）
     * 
     * @param array $data
     *            导入日志的数据
     * @return boolean
     */
    public function addImportLog(array $data)
    {
        if (! is_array($data))
            return false;
        $result = $this->add($data);
        if (! $result)
            return false;
        return $result;
    }

    public function getImportById($id){
        $field = array(
            'il.id' => 'id',
            'il.file_name' => 'file_name',
            'il.file_path' => 'file_path',
            'il.status' => 'status',
            'il_type' => 'type',
            'su.code' => 'code',
            'su.name' => 'user_name',
            'il.create_time' => 'create_time',
            'il.import_time' => 'import_time',
            'il.success' => 'success',
            'il.failures' => 'failures',
            'il.error_no' => 'error_no',
            'il.remark' => 'remark'
        );
        $where=array(
            'il.id'=>$id,
            'il.user_id=su.id'
        );
        $table=array(
            'fx_import_log'=>'il',
            'fx_sys_user'=>'su'
        );
        $result=$this->table($table)->field($field)->where($where)->find();
        return $result;
    }

}