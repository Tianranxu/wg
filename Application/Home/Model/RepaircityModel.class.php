<?php
/*************************************************
 * 文件名：RepaircityModel.class.php
 * 功能：     维修-城市中间表模型
 * 日期：     2015.10.14
 * 作者：     DA fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class RepaircityModel extends Model{

    protected $trueTableName = 'fx_repairer_city';
    
    //根据维修员ID查出 所在佣有的区域
    public function city($rid ,$cid){
        $field = 'c.id,c.name,c.pid';
        $table = array('fx_repairer_city'=>'rc','fx_city'=>'c');
        $where = 'rc.rid=%d and rc.cid=%d and rc.city_id=c.id ';
        $result = $this->table($table)->field($field)->where($where, $rid, $cid)->select();
        return $result;
    }
    //根据ID查城市名称
    public function selectRegion($pid){
        $field = 'c.id,c.name,c.pid';
        $table = array('fx_city'=>'c');
        $where = 'c.id=%d';
        $result = $this->table($table)->field($field)->where($where, $pid)->find();
        return $result;
    }
    //给维修员分配区域
    public function allotArea($rid, $cid, $areaid){
        $this->where('rid=%d and cid=%d',$rid,$cid)->delete();
        $areaid = explode(',',$areaid);
        foreach($areaid as $ar){
            $data[] = array(
                'rid' => $rid,
                'cid' => $cid,
                'city_id' => $ar
            );   
        }
        $result = $this->addAll($data);
        return $result;
        
    }
    //查出维修员已分配的区域
    public function repairCity($rid, $cid){
        $field = 'city_id';
        $where = 'rid=%d and cid=%d';
        $result = $this->field($field)->where($where, $rid, $cid)->select();
        return $result;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}