<?php
/*************************************************
 * 文件名：HousechargesModel.class.php
 * 功能：     房产收费绑定模型
 * 日期：     2015.12.31
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model\AdvModel;

class HousechargesModel extends AdvModel{
    protected $tableName='house_charges_temp';

    protected $partition=[
        'field'=>'hm_id',
        'type'=>'id',
        'expr'=>'2000000',
        //'num'=>'50'
    ];

    /**
     * 房产费项绑定
     * @param string $cmId    企业ID
     * @param array $hmIds   绑定的所有房间ID
     * @param string $chId    费项ID
     * @return bool
     */
    public function doHouseCharges($cmId, array $hmIds, $chId, $status)
    {
        //分表
        $datas=[];
        foreach ($hmIds as $hm => $hmId) {
            $tableName=$this->getPartitionTableName(['hm_id'=>$hmId]);
            $datas[$tableName][$hm]['cm_id']=$cmId;
            $datas[$tableName][$hm]['hm_id']=$hmId;
            $datas[$tableName][$hm]['ch_id']=$chId;
            $datas[$tableName][$hm]['status']=$status;
            $datas[$tableName][$hm]['update_time']=date('Y-m-d H:i:s');
        }
        foreach ($datas as $table => $data) {
            //查询所选房间是否已经绑定该费项
            $houseChargesExists=$this->getHouseChargesLists($table,$cmId,$hmIds,$chId);
            //清除记录
            if($houseChargesExists){
                $clearResult=$this->clearHouseCharges($table,$cmId,$hmIds,$chId);
                if(!$clearResult) return false;
            }
            //根据分表添加记录
            if($status==1){
                $result=$this->table($table)->addAll($data);
                if(!$result) return false;
            }
        }
        return true;
    }

    /**
     * 查询该企业下所选房间绑定费项列表
     * @param string $table       分表名称
     * @param string $cmId       企业ID
     * @param array $hmIds      所选房间ID
     * @param string $chId       费项ID
     * @return mixed
     */
    public function getHouseChargesLists($table,$cmId,array $hmIds,$chId)
    {
        $where=[
            'cm_id'=>$cmId,
            'hm_id'=>['in',$hmIds],
            'ch_id'=>$chId
        ];
        $result=$this->table($table)->where($where)->select();
        return $result;
    }

    /**
     * 清除房间绑定的费项
     * @param string $table     分表名称
     * @param string $cmId     企业ID
     * @param array $hmIds     所选房间ID
     * @param string $chId      费项ID
     * @return bool
     */
    public function clearHouseCharges($table,$cmId, array $hmIds, $chId)
    {
        $where=[
            'cm_id'=>$cmId,
            'hm_id'=>['in',$hmIds],
            'ch_id'=>$chId
        ];
        $result=$this->table($table)->where($where)->delete();
        if(!$result) return false;return true;
    }

    //获取房间的绑定费项信息
    public function getBindItems($hmid){
        $tableName=$this->getPartitionTableName(['hm_id'=>$hmid]);
        return $this->table($tableName)->where(array('hm_id' => $hmid, 'status' => 1))->select();
    }

    //获取禁用的房间绑定费项消息
    public function getBanItems($hmid){
        $tableName=$this->getPartitionTableName(['hm_id'=>$hmid]);
        return $this->table($tableName)->where(array('hm_id' => $hmid, 'status' => -1))->select();
    }

    //保存房间的费项信息
    public function saveItems($add, $ban, $save, $hmid){
        $addResult = $banResult = $saveResult = true;
        $tableName=$this->getPartitionTableName(['hm_id'=>$hmid]);
        if ($add)  $addResult = $this->table($tableName)->addAll($add);
        if ($ban) {
            foreach ($ban as $key => $item) {
                $banResult = $this->table($tableName)->where(array('ch_id' => $item['ch_id'], 'hm_id' => $hmid))->save(array('status' => -1, 'update_time' => date('Y-m-d H:i:s')));
            }
        }
        if ($save) {
            foreach ($ban as $key => $chid) {
                $saveResult = $this->table($tableName)->where(array('ch_id' => $chid, 'hm_id' => $hmid))->save(array('status' => 1, 'update_time' => date('Y-m-d H:i:s')));
            }
        }
        if($addResult && $banResult && $saveResult) 
            return true;
        return false;
    }

    //清除费项后，清除相关的绑定记录
    public function clearCharge($id, $hm_ids){
        foreach ($hm_ids as $key => $hm_id) {
            $table = $this->getPartitionTableName(['hm_id'=>$hm_id]);
            $tables[$table] = $table;    //去重
        }
        foreach ( $tables as $key => $table) {
            $result[] = $this->table($table)->where(array('ch_id' => $id))->delete();
        }
        return $result;
    }
}