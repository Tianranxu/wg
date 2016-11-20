<?php
/*************************************************
 * 文件名：WxorderModel.class.php
 * 功能：     微信临时账单模型
 * 日期：     2015.10.14
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class WxorderModel extends Model
{

    protected $tableName = 'order_manage';

    /**
     * 提交待缴费订单
     * @param string $cmId 企业ID
     * @param array $acIds 账单ID集
     * @param string $id 账单所属房间ID
     * @param float $total 订单总金额
     * @param int $user 操作人id
     * @param string $idType 操作人id类型    pc-PC用户uid，weixin-微信用户openid
     * @param int $type 订单类型   1-房产，2-车辆，默认为1
     * @param int $payType 收支类型   1-收入，2-支出，默认为1
     * @return bool
     */
    public function submitUnPayOrder($cmId, array $acIds, $id, $total, $user, $idType = 'pc', $type = 1, $payType = 1)
    {
        $datas = [
            'cm_id' => $cmId,
            'ac_ids' => implode(',', $acIds),
            'total' => floatval($total),
            'type' => $type,
            'pay_type' => $payType,
            'status' => 1,
            'create_time' => date('Y-m-d H:i:s')
        ];
        if ($type == 1 || $type == 3) $datas['hm_id'] = $id;
        if ($type == 2) $datas['car_id'] = $id;
        if ($idType == 'pc') $datas['uid'] = $user;
        if ($idType == 'weixin') $datas['openid'] = $user;
        $result = $this->add($datas);
        if (!$result) return false;
        return $result;
    }

    /**
     * 获取某个订单的所有账单ID
     * 
     * @param string $id
     *            订单ID
     * @param string $type
     *            待缴账单类型 1-房产 2-车辆
     * @return \Think\mixed
     */
    public function getAccountIds($id, $type)
    {
        $field = ['ac_ids','total'];
        $where = ['id' => $id,'type' => $type];
        $result = $this->field($field)->where($where)->find();
        return $result;
    }

    /**
     * 查询订单详细信息
     * @param int $cmId 企业ID
     * @param int $id 订单ID
     * @param int $type 订单类型    house-房产，car-车位，默认为house
     * @return mixed
     */
    public function getTempOrderInfo($cmId, $id, $type='house')
    {
        $function = new \ReflectionMethod(get_called_class(), 'get' . ucwords($type) . 'TempOrderInfo');
        $result = $function->invoke($this, $cmId, $id);
        return $result;
    }

    /**
     * 查询房产订单详细信息
     * @param int $cmId     企业ID
     * @param int $id          订单ID
     * @return mixed
     */
    public function getHouseTempOrderInfo($cmId, $id)
    {
        $field=[
            'o.id','o.type','o.ac_ids','o.total','o.cm_id','o.openid','o.uid','o.status','o.pay_type',
            'h.id'=>'hm_id','h.name'=>'hm_owner',
            'u.id'=>'user_id','u.code','u.name'=>'user_name'
        ];
        $where=[
            'o.cm_id'=>$cmId,
            'o.id'=>$id,
        ];
        $table=['fx_order_manage'=>'o'];
        $result=$this->table($table)->field($field)->where($where)
            ->join('`fx_house_manage` AS `h` ON h.id=o.hm_id','LEFT')
            ->join('`fx_sys_user` AS `u` ON u.id=o.uid','LEFT')
            ->find();
        return $result;
    }

    /**
     * 查询车位订单详细信息
     * @param int $cmId     企业ID
     * @param int $id          订单ID
     * @return mixed
     */
    public function getCarTempOrderInfo($cmId, $id)
    {
        $field = [
            'o.id', 'o.type', 'o.ac_ids', 'o.total', 'o.cm_id', 'o.openid', 'o.uid', 'o.status', 'o.pay_type',
            'c.id' => 'car_id', 'c.user' => 'hm_owner',
            'u.id' => 'user_id', 'u.code', 'u.name' => 'user_name'
        ];
        $where = [
            'o.cm_id' => $cmId,
            'o.id' => $id,
        ];
        $table = ['fx_order_manage' => 'o'];
        $result = $this->table($table)->field($field)->where($where)
            ->join('`fx_car_manage` AS `c` ON c.id=o.car_id', 'LEFT')
            ->join('`fx_sys_user` AS `u` ON u.id=o.uid', 'LEFT')
            ->find();
        return $result;
    }
    /**
     * 查询合约订单详细信息
     * @param int $cmId     企业ID
     * @param int $id          订单ID
     * @return mixed
     */
    public function getLeaseTempOrderInfo($cmId, $id)
    {
        $field=[
            'o.id','o.type','o.ac_ids','o.total','o.cm_id','o.openid','o.uid','o.status','o.pay_type',
            'h.id'=>'hm_id','h.name'=>'hm_owner',
            'u.id'=>'user_id','u.code','u.name'=>'user_name'
        ];
        $where=[
            'o.cm_id'=>$cmId,
            'o.id'=>$id,
        ];
        $table=['fx_order_manage'=>'o'];
        $result=$this->table($table)->field($field)->where($where)
            ->join('`fx_house_manage` AS `h` ON h.id=o.hm_id','LEFT')
            ->join('`fx_sys_user` AS `u` ON u.id=o.uid','LEFT')
            ->find();
        $leaseModel=D('lease');
        $contractModel=D('contract');
        $result['customer']=$contractModel->getContractById($leaseModel->getLeaseBills(explode(',',$result['ac_ids']))[0]['contract_id'])['customer'];
        return $result;
    }

    /**
     * 更新订单状态
     * @param int $id 订单ID
     * @param int $status 订单状态
     * @param int $type 订单类型   1-房产，2-车辆，默认为1
     * @param array $acIds 订单所属账单ID集
     * @return bool
     */
    public function payOrder($id, $status, $type = 1, array $acIds = [])
    {
        $data = ['status' => $status];
        //更新订单状态
        $orderWhere = ['id' => $id, 'type' => $type];
        $orderResult = $this->where($orderWhere)->save($data);
        if (!$orderResult) return false;
        //更新订单所属账单状态
        if($type==1) $accountsModel = D('accounts');
        if($type==2) $accountsModel=D('carfee');
        if($type==3) $accountsModel=D('lease');
        $accountsResult = $accountsModel->updateAccounts($acIds, $status);
        if (!$accountsResult) return false;
        return true;
    }

    public function getOrderList($where){
        $table = array(
            'fx_order_manage' => 'om',
            'fx_house_manage' => 'hm',
            'fx_building_manage' => 'bm',
            'fx_community_comp' => 'cc',
            'fx_pay_order' => 'po',
        );
        $query = array(
            'om.hm_id = hm.id',
            'hm.bm_id = bm.id',
            'bm.cc_id = cc.id',
            'po.order_id = om.id',
        );
        $field = array(
            'om.create_time' => 'create_time','om.id','om.uid',
            'om.total' => 'total',
            'om.type' => 'type',
            'om.pay_type' => 'ptype',
            'om.ac_ids' => 'ac_ids',
            'cc.name' => 'cc_name',
            'bm.name' => 'b_name',
            'hm.number' => 'h_name',
            'po.out_trade_no' => 'out_trade_no', 'po.openid',
        );
        foreach ($where as $key => $value) {
            if ($key == 'create_time') {
                $query['om.create_time'] = array('like', '%'.$value.'%'); 
            }elseif ($key == 'hmids') {
                $query['om.hm_id'] = array('IN', $value);
            }elseif ($key == 'page' || $key == 'limit'){
            }else{
                $query['om.'.$key] = $value;
            }
        }
        return $this->table($table)->where($query)->limit(($where['page']-1)*$where['limit'], $where['limit'])->field($field)->order('om.create_time desc')->select();
    }

    public function getCarPayDetails($where){
        $table = [
            'fx_order_manage' => 'om',
            'fx_car_manage' => 'cm',
            'fx_community_comp' => 'cc',
            'fx_pay_order' => 'po',
        ];
        $query = [
            'om.car_id = cm.id',
            'cm.cc_id = cc.id',
            'po.order_id = om.id',
        ];
        $field = [
            'om.id','om.create_time', 'om.total', 'om.pay_type', 'om.ac_ids', 'om.type', 'om.uid',
            'cm.car_number', 'cm.card_number',
            'cc.name' => 'cc_name', 
            'po.out_trade_no' => 'out_trade_no', 'po.openid',
        ];
        foreach ($where as $key => $value) {
            if ($key == 'create_time') {
                $query['om.' . $key] = ['like', '%'.$value.'%'];
            }elseif ($key == 'card_number'){
                $query['cm.' . $key] = ['like', '%'.$value.'%'];
            }elseif ($key == 'cc_id') {
                $query['cm.'.$key] = $value;
            }elseif ($key == 'page' || $key == 'limit') {
            }else{
                $query['om.'.$key] = $value;
            }
        }
        return $this->table($table)->where($query)->limit(($where['page']-1)*$where['limit'], $where['limit'])->order('om.create_time desc')->field($field)->select();
    }
}