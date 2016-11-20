<?php
/*************************************************
 * 文件名：FaulttimeoutModel.class.php
 * 功能：     故障超时模型
 * 日期：     2015.12.15
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class FaulttimeoutModel extends Model
{
    protected $tableName = 'fault_timeout';

    public function getTimeoutStatistics($wheres)
    {
        //组装显示字段
        $field=[
            't.type',
            't.update_time',
        ];
        //组装where条件
        foreach ($wheres as $index => $where) {
            $query[$index]=$where;
        }
        if($wheres['start_time'] && $wheres['end_time']){
            $query['t.update_time']=['between',[$wheres['start_time'],$wheres['end_time']]];
            unset($query['start_time']);
            unset($query['end_time']);
        }
        //组装需要查询的表
        $table=['fx_fault_timeout'=>'t'];
        $result=$this->table($table)->field($field)
            ->join("`fx_fault_details` AS `f` ON f.id=t.fault_id",'LEFT')
            ->where($query)->order(['t.type,t.update_time'=>' asc'])->select();
        return $result;
    }
}