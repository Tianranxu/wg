<?php
/*************************************************
 * 文件名：RoomModel.class.php
 * 功能：     房源管理模型
 * 日期：     2016.01.21
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class RoomModel extends Model
{

    protected $tableName = 'room_source';

    /**
     * 获取房源列表
     * @param int $cmId 企业ID
     * @param array $search 搜索条件
     * @return mixed
     */
    public function getRoomLists($cmId, array $search)
    {
        $field = [
            'cm.id' => 'cm_id', 'cm.name' => 'cm_name', 'cc.id' => 'cc_id', 'cc.name' => 'cc_name', 'bm.id' => 'bm_id', 'bm.name' => 'bm_name', 'hm.id' => 'hm_id', 'hm.number' => 'hm_name',
            'rm.id', 'rm.parent_id', 'hm.building_area', 'rm.type', 'rm.room_type', 'rm.furnish_type', 'rm.rent', 'rm.total_trustee_fee', 'rm.sign_time', 'rm.end_time', 'rm.follow_type', 'rm.status',
        ];
        $where = ['rm.cm_id' => $cmId, 'rm.hm_id=hm.id', 'hm.bm_id=bm.id', 'bm.cc_id=cc.id', 'cc.cm_id=cm.id'];
        $search['bm.cc_id']=$search['cc_id'];
        $search['hm.number'] = $search['number'];
        $search['rm.status'] = $search['status'];
        unset($search['cc_id'],$search['number'],$search['status']);
        foreach ($search as $key => $value) {
            if ($value) $where[$key] = $value;
        }
        $table = ['fx_room_source' => 'rm', 'fx_house_manage' => 'hm', 'fx_building_manage' => 'bm', 'fx_community_comp' => 'cc', 'fx_comp_manage' => 'cm'];
        $result = $this->table($table)->field($field)->where($where)->order('rm.update_time desc')->select();
        return $result;
    }

    /**
     * 根据房间ID查找房源
     * @param int $hmId 房间ID
     * @return mixed
     */
    public function getRoomByHmid($hmId, $section='', $status='')
    {
        $where = ['hm_id' => $hmId];
        if($section && $status) $where['status']=[$section,$status];
        $result = $this->where($where)->find();
        return $result;
    }

    public function getRoomByHmids(array $hmIds, $section='', $status='')
    {
        $field=['rs.id','rs.hm_id','hm.number','rs.type','rs.room_type','rs.furnish_type','rs.follow_type','rs.status','rs.cm_id','rs.start_time','rs.end_time','rs.sign_time','rs.limit','.rs.trustee_fee','rs.total_trustee_fee','rs.deposit','rs.rent','rs.furniture','rs.is_increase','rs.increase_type','rs.increase_price','rs.increasing_cycle','rs.uid','rs.remark'];
        $where=['rs.hm_id'=>['in',$hmIds],'rs.hm_id=hm.id'];
        if($section && $status) $where['rs.status']=[$section,$status];
        $table=['fx_room_source'=>'rs','fx_house_manage'=>'hm'];
        $result=$this->table($table)->field($field)->where($where)->select();
        return $result;
    }

    /**
     * 登记房源
     * @param array $datas 房源数据
     * @return bool
     */
    public function addRoom(array $datas)
    {
        //判断该房间是否已绑定房源
        $roomExists = $this->getRoomByHmid($datas['hm_id'],'lt',3);
        if ($roomExists) return false;
        $pictures = $datas['pictures'];
        unset($datas['pictures']);
        $this->startTrans();
        //如果结束托管
        $result = $this->add($datas);
        if (!$result) {
            $this->rollback();
            return false;
        }
        //添加附件
        if ($pictures) {
            $picDatas = [];
            foreach ($pictures as $p => $picture) {
                $picDatas[$p]['rs_id'] = $result;
                $picDatas[$p]['url'] = $picture;
            }
            $roompicModel = D('roompic');
            $picResult = $roompicModel->addPictures($picDatas);
            if (!$picResult) {
                $this->rollback();
                return false;
            }
        }
        $this->commit();
        return true;
    }

    /**
     * @param int $cmId 企业ID
     * @param int $id 房源ID
     * @param array $datas 房源数据
     * @return bool
     */
    public function saveRoom($cmId, $id, array $datas)
    {
        $where = ['cm_id' => $cmId, 'id' => $id];
        $result = $this->where($where)->save($datas);
        if (!$result) return false;
        return true;
    }

    /**
     * 获取房源信息
     * @param int $id 房源ID
     * @return mixed
     */
    public function getRoomInfo($id, $type = 'room')
    {
        $field = [
            'cm.id' => 'cm_id', 'cm.name' => 'cm_name', 'cc.id' => 'cc_id', 'cc.name' => 'cc_name', 'bm.id' => 'bm_id', 'bm.name' => 'bm_name', 'hm.id' => 'hm_id', 'hm.number' => 'hm_name',
            'rm.id', 'rm.parent_id', 'hm.building_area', 'hm.inside_area', 'rm.type', 'rm.room_type', 'rm.furnish_type', 'rm.rent', 'rm.trustee_fee', 'rm.total_trustee_fee', 'rm.deposit', 'rm.start_time', 'rm.end_time', 'rm.limit', 'rm.sign_time', 'rm.follow_type', 'rm.status',
            'rm.is_increase', 'rm.increase_type', 'rm.increase_price', 'rm.increasing_cycle', 'rm.furniture', 'su.id'=>'user_id','su.code', 'su.name' => 'user_name', 'rm.remark'
        ];
        $where = ['rm.hm_id=hm.id', 'hm.bm_id=bm.id', 'bm.cc_id=cc.id', 'cc.cm_id=cm.id', 'rm.uid=su.id'];
        $type == 'house' ? $where['hm.id'] = $id : $where['rm.id'] = $id;
        $table = ['fx_room_source' => 'rm', 'fx_house_manage' => 'hm', 'fx_building_manage' => 'bm', 'fx_community_comp' => 'cc', 'fx_comp_manage' => 'cm', 'fx_sys_user' => 'su'];
        $info = $this->table($table)->field($field)->where($where)->find();
        return $info;
    }

    public function getRoomData($where){
        $table = [
            'fx_community_comp' => 'cc',
            'fx_room_source' => 'rs',
            'fx_house_manage' => 'hm',
            'fx_building_manage' => 'bm',
        ];
        $query = ['hm.id = rs.hm_id', 'hm.bm_id = bm.id', 'bm.cc_id = cc.id'];
        foreach ($where as $k => $v) {
            if ($v) {
                if ($k == 'minarea' || $k == 'maxarea') {
                    $query['hm.inside_area'] = ['between', "{$where['minarea']},{$where['maxarea']}"];
                }elseif ($k == 'cc_id'){
                    $query['cc.id'] = $v;
                }else{
                    $query['rs.'.$k] = $v;
                }
            }
        }
        $field = [
            'cc.name' => 'ccname','hm.inside_area', 
            'rs.id','rs.room_type', 'rs.type', 'rs.furnish_type', 'rs.rent', 'rs.sign_time', 
        ];
        return $this->table($table)->where($query)->field($field)->select();
    }
    //查找企业下所有房源
    public function getRoomToCompany($compid, $status=''){
        $field = 'id,hm_id,cm_id,type,total_trustee_fee as total,start_time,sign_time,end_time,limit,trustee_fee,rent,deposit';
        $exp = empty($status)? ['neq', -10]: ['eq', $status];
        $where = [
            'cm_id' => $compid,
            'status' => $exp,
        ];
        return $this->field($field)->where($where)->select();
    }
}