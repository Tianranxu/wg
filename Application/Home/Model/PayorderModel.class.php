<?php
/*************************************************
 * 文件名：PayorderModel.class.php
 * 功能：     微信支付订单模型
 * 日期：     2015.11.07
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class PayorderModel extends Model
{

    protected $tableName = 'pay_order';

    /**
     * 获取用户缴费记录
     * @param string $openid 用户openid
     * @param string $id ID，默认为空
     * @param string $type 订单类型  1-房产，2-车辆，默认为1
     * @param string $payDate 付款日期，默认为空
     * @return number
     */
    public function getPayRecordList($openid, $id = '', $type = 1, $payDate = '')
    {
        // 查询支付订单信息
        $payField = ['p.id', 'p.pay_date', 'o.id', 'p.ac_ids', 'p.total'];
        $payWhere = ['p.openid' => $openid, 'p.status' => 2, 'o.type' => $type, 'p.order_id=o.id'];
        if ($payDate) $payWhere['p.pay_date'] = ['like', "{$payDate}%"];
        if($id && ($type==1)) $payWhere['o.hm_id'] = $id;
        if($id && ($type==2)) $payWhere['o.car_id'] = $id;
        $payOrder = ['p.pay_date' => 'desc'];
        $payTable = ['fx_order_manage' => 'o', 'fx_pay_order' => 'p'];
        $payLists = $this->table($payTable)->field($payField)->where($payWhere)->order($payOrder)->select();
        // 查询订单所属的账单信息
        $acIds = [];
        foreach ($payLists as $p => $payList) {
            foreach (explode(',', $payList['ac_ids']) as $acId) {
                array_push($acIds, $acId);
                continue;
            }
        }
        $typeName = 'house';
        if ($type == 2) $typeName = 'car';
        $function = new \ReflectionMethod(get_called_class(), 'get' . ucwords($typeName) . 'Accounts');
        $accountsLists = $function->invoke($this, $acIds);
        $result = [];
        foreach ($payLists as $p => $payList) {
            foreach ($accountsLists as $accountsList) {
                if (in_array($accountsList['id'],explode(',',$payList['ac_ids']))) {
                    $result[$payList['ac_ids']]['order'] = $payList;
                    $result[$payList['ac_ids']]['accounts'][] = $accountsList;
                }
            }
        }
        return $result;
    }

    /**
     * 获取房间账单列表
     * @param array $acIds 账单ID集
     * @return mixed
     */
    public function getHouseAccounts(array $acIds)
    {
        $accountsModel = D('accounts');
        $result = $this->recombinPayRecord($accountsModel->getBills($acIds));
        return $result;
    }

    /**
     * 获取车位账单列表
     * @param array $acIds 账单ID集
     * @return mixed
     */
    public function getCarAccounts(array $acIds)
    {
        $carfeeModel = D('carfee');
        $result = $this->recombinPayRecord($carfeeModel->getCarBills($acIds));
        return $result;
    }

    /**
     * 计算账单总额
     * @param $accountsLists    账单列表
     * @return mixed
     */
    public function recombinPayRecord($accountsLists)
    {
        $result = [];
        foreach ($accountsLists as $ac => $accountsList) {
            // 计算账单最终金额
            $accountsLists[$ac]['total'] = $accountsList['money'] - $accountsList['preferential_money'] + $accountsList['penalty'];
            $accountsLists[$ac]['total'] = number_format($accountsLists[$ac]['total'],2);
        }
        return $accountsLists;
    }

    /**
     * 添加订单支付记录
     * @param array $data 支付数据
     * @return boolean|\Think\mixed
     */
    public function recordPayOrder(array $datas)
    {
        $tempDatas = [];
        if (isset($datas['uid'])) {
            foreach ($datas['arr'] as $temp => $arr) {
                $tempDatas[$temp]['type'] = $arr['type'];
                $tempDatas[$temp]['total'] = $arr['total'];
                $tempDatas[$temp]['remark'] = $arr['remark'] ? $arr['remark'] : null;
                $tempDatas[$temp]['update_time'] = date('Y-m-d H:i:s');
            }
            unset($datas['arr']);
            $datas['out_trade_no'] = date('Ymd').'-'.$datas['order_id'].'-'.time();
        }
        if(isset($datas['openid'])){
            $tempDatas['type'] = C('PAY_TYPE')['WEIXIN_PAY']['VALUE'];
            $tempDatas['total'] = $datas['total'];
            $tempDatas['update_time'] = date('Y-m-d H:i:s');
        }
        $orderType = $datas['type'];
        unset($datas['type']);
        $this->startTrans();
        $payOrderResult = $this->add($datas);
        if (!$payOrderResult) {
            $this->rollback();
            return false;
        }
        if (!empty($tempDatas)) {
            if($datas['uid']){
                foreach ($tempDatas as $temp => $tempData) {
                    $tempDatas[$temp]['pay_id']=$payOrderResult;
                }
            }
            if($datas['openid']) $tempDatas['pay_id']=$payOrderResult;
            //添加付款方式
            $payTypeModel = D('paytype');
            $typeResult = $payTypeModel->addPayType($tempDatas);
            if (!$typeResult) {
                $this->rollback();
                return false;
            }
        }
        //更改订单状态
        $wxorderModel = D('wxorder');
        $orderResult = $wxorderModel->payOrder($datas['order_id'], $datas['status'], $orderType, $datas['ac_ids']);
        if (!$orderResult) {
            $this->rollback();
            return false;
        }
        $this->commit();
        return $payOrderResult;
    }

    //获取支付账单及其相关信息
    public function getPayOrder($orderId){
        $table = [
            'fx_pay_order' => 'po',
            'fx_order_manage' => 'om',
            'fx_pay_type_temp' => 'ptt',
            'fx_comp_manage' => 'cm',
            'fx_house_manage' => 'hm',
            'fx_building_manage' => 'bm',
            'fx_community_comp' => 'cc',
        ];
        $where = [
            'po.order_id' => $orderId,
            'po.order_id = om.id',
            'po.cm_id = cm.id',
            'po.id = ptt.pay_id',
            'om.hm_id = hm.id',
            'hm.bm_id = bm.id',
            'bm.cc_id = cc.id',
        ];
        $field = [
            'po.id', 'po.pay_user', 'po.total', 'po.ac_ids', 'po.out_trade_no', 'po.order_id', 'po.uid', 'po.openid',
            'ptt.type', 'ptt.total' => 'p_total', 'ptt.remark',
            'cm.name' => 'c_name', 'cc.name' => 'cc_name',
            'bm.name' => 'b_name', 'hm.number' => 'h_name',
        ];
        return $this->table($table)->where($where)->field($field)->select();
    }

    public function getCarPayOrder($orderId){
        $table = [
            'fx_pay_order' => 'po',
            'fx_order_manage' => 'om',
            'fx_pay_type_temp' => 'ptt',
            'fx_comp_manage' => 'cm',
            'fx_community_comp' => 'cc',
            'fx_car_manage' => 'car',
        ];
        $where = [
            'po.order_id' => $orderId,
            'po.order_id = om.id',
            'po.cm_id = cm.id',
            'po.id = ptt.pay_id',
            'om.car_id = car.id',
            'car.cc_id = cc.id', 
        ];
        $field = [
            'po.id', 'po.pay_user', 'po.total', 'po.ac_ids', 'po.out_trade_no', 'po.order_id', 'po.uid', 'po.openid',
            'ptt.type', 'ptt.total' => 'p_total', 'ptt.remark',
            'cm.name' => 'c_name', 'cc.name' => 'cc_name',
            'car.car_number', 'car.card_number', 
        ];
        return $this->table($table)->where($where)->field($field)->select();
    }

    //获取合约的订单信息
    public function getLeasePayOrder($orderId){
        $table = [
            'fx_pay_order' => 'po',
            'fx_pay_type_temp' => 'ptt',
            'fx_comp_manage' => 'cm',
        ];
        $where = [
            'po.order_id' => $orderId,
            'po.cm_id = cm.id', 
            'po.id = ptt.pay_id',
        ];
        $field = [
            'po.id', 'po.pay_user', 'po.total', 'po.ac_ids', 'po.out_trade_no', 'po.order_id', 'po.uid', 'po.openid',
            'ptt.type', 'ptt.total' => 'p_total', 'ptt.remark', 'cm.name' => 'c_name', 
        ];
        return $this->table($table)->where($where)->field($field)->select();
    }
}