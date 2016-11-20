<?php
/*************************************************
 * 文件名：HouseModel.class.php
 * 功能：     房间模型
 * 日期：     2015.12.31
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;
use Think\Model;

class HouseModel extends Model
{
    protected $tableName = 'house_manage';


    /**
     * 根据楼栋查询所有房间
     * @param $bu_id    楼栋ID
     */
    public function selectAllHouse($bu_id){

        $field = array(
            'id',
            'number' => 'name',
            'hm_number' => 'number',
            'floor'
        );
        $where = 'bm_id=%d and status<>-1';
        return $this->field($field)
            ->where($where, $bu_id)
            ->select();
    }
    /**
     * 根据ID查询房间
     * @param $hmid    房间ID
     */
    public function selectHouseForID($hmid){
        return $this->field('id,number,floor,hm_number,bm_id,cm_id')
                    ->where('id=%d', $hmid)
                    ->find();
    }
    /**
     * 根据条件查询所有房间ID
     * @param $hmid    房间ID
     * @param $ccid    楼盘ID
     * @param $buid    楼栋ID
     * @param $compid  公司ID
     */
    public function houseForCondition($ccid='', $buid='', $hmid='', $compid=''){

        $table = array(
            'fx_comp_manage' => 'cm',
            'fx_community_comp' => 'cc',
            'fx_building_manage' => 'bm',
            'fx_house_manage' => 'hm',
        );
        if(!empty($hmid)){
            $where = 'hm.id=%d';
            $param = array($hmid);
        }elseif(!empty($buid)){
            $where = 'bm.id=%d and bm.id=hm.bm_id';
            $param = array($buid);
        }elseif(!empty($ccid)){
            $where = 'cc.id=%d and bm.cc_id=cc.id and bm.id=hm.bm_id';
            $param = array($ccid);
        }else{
            $table = array(
                'fx_comp_manage' => 'cm',
            );
            return $this->table($table)
                        ->field('hm.id')
                        ->where('cm.id=%d', $compid)
                        ->join('`fx_community_comp` AS `cc` ON cc.cm_id=cm.id','RIGHT')
                        ->join('`fx_building_manage` AS `bm` ON cc.id=bm.cc_id','RIGHT')
                        ->join('`fx_house_manage` AS `hm` ON bm.id=hm.bm_id','RIGHT')
                        ->select();
           // echo $this->getLastSql();exit;
        }
        return $this->table($table)
                    ->field('hm.id')
                    ->where($where, $param)
                    ->distinct(true)
                    ->select();

       //echo $this->getLastSql();exit;
    }


}




















