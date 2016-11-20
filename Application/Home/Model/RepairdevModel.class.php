<?php
/*************************************************
 * 文件名：RepairdevModel.class.php
 * 功能：     维修-设备中间表模型
 * 日期：     2015.10.14
 * 作者：     DA fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class RepairdevModel extends Model{

    protected $trueTableName = 'fx_repairer_device_temp';
    
    //根据维修员ID查出 所有项目
    public function device($rid){
        $field = 'd.id,d.name';
        $table = array('fx_repairer_device_temp'=>'rd','fx_device'=>'d');
        $where = 'rd.rid=%d and rd.dev_id=d.id';
        $result = $this->table($table)->field($field)->where($where, $rid)->select();
        return $result;
    }
    //根据维修ID查询所佣有的项目
    public function repairDevi($rid){
        $field = 'd.id,d.name';
        $table = array('fx_repairer_device_temp'=>'dt','fx_device'=>'d');
        $where = 'dt.rid=%d and dt.dev_id=d.id and d.status=1';
        $result = $this->table($table)->field($field)->where($where, $rid)->select();
        return $result;
    }
    //给维修员分配项目
    public function allotDevi($rid, $cid, $devid){
        $this->where('rid=%d and cid=%d',$rid,$cid)->delete();
        $devid = explode(',',$devid);
        foreach($devid as $dev){
            $data[] = array(
                'rid' => $rid,
                'cid' => $cid,
                'dev_id' => $dev
            );   
        }
        $result = $this->addAll($data);
        return $result;
        
    }

    //获取维修设备，维修公司和楼盘的信息
    public function getPropertyByCompid($compid){
        $table = array(
            'fx_comp_device_temp' => 'cdt',
            'fx_device' => 'd',
            'fx_community_comp' => 'cc',
            'fx_comp_manage' => 'cm',
        );
        $where = array(
            'cdt.rc_id' => $compid,
            'cdt.did = d.id',
            'cdt.cc_id = cc.id',
            'cc.cm_id = cm.id',
        );
        $field = array(
            'cm.id' => 'cm_id',
            'cm.name' => 'c_name',
            'cm.number' => 'c_number',
            'cc.id' => 'cc_id',
            'cc.name' => 'cc_name',
            'd.id' => 'did',
            'd.name' => 'd_name',
            'cdt.status' =>'status',
        );
        return $this->table($table)->where($where)->field($field)->select();
    }
    
    /**
     * 根据物业企业ID查询绑定的维修公司、楼盘、设备信息
     * @param unknown $compid
     * @return \Think\mixed
     */
    public function getPropertyByProCompid($compid){
        $field=[
            'cm.id'=>'cm_id',
            'cm.name'=>'cm_name',
            'cm.number'=>'cm_number',
            'cc.id'=>'cc_id',
            'cc.name'=>'cc_name',
            'd.id'=>'did',
            'd.name'=>'d_name',
            't.status'=>'status'
        ];
        $where=[
            't.cc_id=cc.id',
            't.did=d.id',
            'cc.cm_id=cm.id',
            'cm.id'=>$compid
        ];
        $table=[
            'fx_comp_device_temp'=>'t',
            'fx_device'=>'d',
            'fx_community_comp'=>'cc',
            'fx_comp_manage'=>'cm'
        ];
        $result=$this->table($table)->field($field)->where($where)->select();
        return $result;
    }

}