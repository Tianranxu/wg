<?php
/*************************************************
 * 文件名：WarningModel.class.php
 * 功能：     收费项目管理模型
 * 日期：     2016.01.25
 * 作者：     XU
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;
use Think\Model;

class WarningModel extends Model{
    protected $tableName = 'sys_warning';
    
    public function getWarning($compid, $type){
        return $this->where(['cm_id' => $compid, 'type' => $type])->find();
    }
    
}