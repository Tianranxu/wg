<?php
/*************************************************
 * 文件名：FollowModel.class.php
 * 功能：     客源和房源跟进模型
 * 日期：     2016.1.21
 * 作者：     XU
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;
use Think\Model;

class FollowModel extends Model{
    protected $tableName = 'follow';

    public function getFollow($id, $type){
        $table = [
            'fx_follow' => 'f',
            'fx_sys_user' => 'su',
        ];
        if ($type == 'customer') $query['f.customer_id'] = $id;
        if ($type == 'room') $query['f.rs_id'] = $id;
        $query[] = 'f.uid = su.id';
        $field = [
            'f.id', 'f.msg', 'f.create_time', 'f.customer_id', 'f.rs_id',
            'su.name', 'su.code',
        ];
        return $this->table($table)->where($query)->field($field)->order('f.create_time desc')->select();
    }

    public function addFollow($data){
        return $this->add($data);
    }
}