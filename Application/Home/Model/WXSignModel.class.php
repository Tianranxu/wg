<?php
/*************************************************
 * 文件名：WXSignModel.class.php
 * 功能：     微信维修员签到模型
 * 日期：     2015.12.17
 * 作者：     XU
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;
use  Think\Model;

class WXSignModel extends Model{

    protected $tableName = 'repairman_sign';
    //检查签到信息
    public function checkSign($data){
        $where = array(
            'sr_id' => $data['sr_id'],
            'sign_time' => array( 'like' , '%' . substr($data['sign_time'],0,10) . '%'),
        );
        return $this->where($where)->find();
    }

    //添加签到信息
    public function addSignMessage($data){
        unset($data['flag'],$data['content']);
        return $this->add($data);
    }

    //根据公司id以及查询条件获取该维修公司所有维修员的签到情况
    public function getSignData($where){
        $table = array(
            'fx_sys_repairer' => 'sr',
        );
        $query = array(
            'sr.cm_id' => $where['compid'],
        );
        $field = array(
            'sr.id' => 'id',
            'sr.name' => 'name',
            'rs.sign_time' => 'sign_time',
        );
        if($where['sign_time'])
            $query['rs.sign_time'] = array('like','%' . $where['sign_time'] . '%');
        if ($where['start_time'] && $where['end_time']) {
            $query['rs.sign_time'] = array(
                'between',array(date('Y-m-d H:i:s',strtotime($where['start_time'])),date('Y-m-d H:i:s',strtotime($where['end_time'])+86400)));
        }
        $result = $this->table($table)->field($field)->join(' `fx_repairman_sign` AS `rs`ON sr.id=rs.sr_id' , 'LEFT')->where($query)->select();
        return $result;
    }

    public function getAllRepairers($compid){
        return $this->table('fx_sys_repairer')->where(array('cm_id' => $compid,'status' => ['gt' , 1] ))->field(array('id','name'))->select();
    }
}