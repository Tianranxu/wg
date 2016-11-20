<?php
/*************************************************
 * 文件名：CustomersourceModel.class.php
 * 功能：     客源模型
 * 日期：     2016.01.18
 * 作者：     XU
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/

namespace Home\Model;
use Think\Model;

class CustomersourceModel extends Model{
    protected $tableName = 'customer_source';

    //根据搜索条件获取客源信息
    public function getCustomerList($where){
        $table = [
            'fx_customer_source' => 'cs',
            'fx_property_pc_user' => 'ppu',
        ];
        $query = [
            'cs.customer_id = ppu.id', 
        ];
        $field = [
            'cs.name' => 'name',
            'ppu.contact_number' => 'phone',
            'cs.customer_id', 'cs.id', 'cs.cc_id', 'cs.room_type', 'cs.type', 'cs.area', 'cs.status',
            'cs.intention', 'cs.sign_time', 'cs.price', 'cs.other_demand', 'cs.furnish_type',
        ];
        foreach ($where as $key => $value) {
            if ($key == 'name') {
                $query['cs.'.$key] = ['like', '%'. $value .'%'];
            }elseif ($key == 'phone'){
                $query['ppu.contact_number']=$value;
            }else{
                $query['cs.'.$key] = $value;
            }
        }
        return $this->table($table)->where($query)->field($field)->order(['cs.sign_time'=>'desc', 'cs.id' => 'desc'])->select();
    }

    public function getOneCustomer($id){
        $table = ['fx_customer_source' => 'cs'];
        $field = [
            'cs.name', 'cs.customer_id', 'cs.id', 'cs.cc_id', 'cs.room_type', 'cs.type', 'cs.area', 'cs.status', 'cs.intention', 'cs.sign_time',
            'cs.price', 'cs.other_demand', 'cs.furnish_type','cs.remark', 'cc.name' => 'cc_name', 
            'ppu.contact_number' => 'phone', 'su.code', 'su.name' => 'u_name',
        ];
        $query = [
            'cs.id' => $id,
        ];
        return $this->table($table)->where($query)->field($field)
            ->join('`fx_property_pc_user` AS `ppu` ON cs.customer_id=ppu.id', 'LEFT')
            ->join('`fx_community_comp` AS `cc` ON cs.cc_id=cc.id', 'LEFT')
            ->join('`fx_sys_user` AS `su` ON cs.uid=su.id', 'LEFT')->find();
    }
    //匹配房源用（不联查）
    public function getCustomerById($id){
        return $this->where(['id' => $id])->find();
    }

    //检查客户是否存在
    public function checkCustomer($compid, $phone){
        return $this->table('fx_property_pc_user')->where(['cm_id' => $compid, 'contact_number' => $phone])->find();
    }

    public function countCustomer($compid){
        return $this->table('fx_property_pc_user')->where(['cm_id' => $compid])->count();
    }

    public function addCustomer($data){
        return $this->add($data);
    }

    public function saveCustomer($data){
        return $this->save($data);
    }

    /**
     * 匹配客源
     * @param array $roomInfo 房源信息
     * @return mixed
     */
    public function matchCustomers($comid, array $roomInfo)
    {
        $field=['cs.id','pu.id'=>'customer_id','cc.id'=>'cc_id','cs.sign_time','cs.name','pu.contact_number'=>'mobile','cc.name'=>'cc_name','cs.room_type','cs.type','cs.furnish_type'];
        $where = [
            'cs.cm_id' => $comid,
            'cs.cc_id' => [['eq', explode('-', $roomInfo['parent_id'])[1]], ['exp', 'IS NULL'], 'or'],
            'cs.type' => [['eq', $roomInfo['type']], ['exp', 'IS NULL'], 'or'],
            'cs.room_type' => [['eq', $roomInfo['room_type']], ['exp', 'IS NULL'], 'or'],
            'cs.furnish_type' => [['eq', $roomInfo['furnish_type']], ['exp', 'IS NULL'], 'or'],
			'cs.status' => 1,
			'cs.intention' => 1,
        ];
        $table=['fx_customer_source'=>'cs'];
        $lists = $this->table($table)->field($field)->where($where)->join('`fx_community_comp` AS `cc` ON cc.id=cs.cc_id','LEFT')->join('`fx_property_pc_user` AS `pu` ON cs.customer_id=pu.id','LEFT')->select();
        foreach ($lists as $li => $list) {
            $area = explode('-', $list['area']);
            if (($roomInfo['inside_area'] < $area[0]) && ($roomInfo['inside_area'] > $area[1])) unset($lists[$li]);
        }
        return $lists;
    }
    //查询企业下所有客源
    public function getCustomerToCompany($compid, $status=''){
        $exp = empty($status)? ['neq', -10]: ['eq', $status];
        $field = 'id,customer_id,cm_id,status,sign_time as time,name';
        $where = [
            'cm_id' => $compid,
            'status' => $exp,
        ];
        return $this->field($field)->where($where)->select();
    }
}
