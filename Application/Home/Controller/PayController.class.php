<?php
/*************************************************
 * 文件名：PayController.class.php
 * 功能：     收支明细控制器
 * 日期：     2015.01.05
 * 作者：     XU
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/

namespace Home\Controller;

class PayController extends AccessController{
    protected $query_map = [
        'compid' => 'cm_id',
        'lt' => 'limit',
        'page' => 'page',
        'ptype' => 'pay_type',
        'hmid' => 'hm_id',
        'st' => 'status',
        'cc_id' => 'cc_id',      //楼盘id，车辆收支明细用
        'cardnum' => 'card_number',     //卡号id，车辆收支明细用
    ];
    
    public function _initialize(){
        header("Content-Type:text/html;charset=utf-8");
        parent::_initialize();
    }

    //获取并组装查询条件
    private function getWhere(){
        $data = I('get.');
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $this->query_map) && $value)
                $where[$this->query_map[$key]] = $value;
        }
        if ($data['year']) {
            $where['create_time'] = $data['month'] ? $data['year'] . '-' . str_pad($data['month'], 2, '0', STR_PAD_LEFT) : $data['year'];
        }
        if ($data['ccid'] && (!$data['hmid'])) {
            $propertyController = A('propertycommon');
            $where['hmids'] = $data['bmid'] ? $propertyController->getPropertyIds($data['bmid'], 'build') : $propertyController->getPropertyIds($data['ccid'], 'community') ;
        }
        $where['page'] = $where['page'] ? $where['page'] : 1;
        $where['limit'] = $where['limit'] ? $where['limit'] : 10;
        $where['status'] = $where['status'] ? $where['status'] : C('BILL_STATUS.PAYED');
        return $where;
    }
    
    //获取当前年份的前后10年
    private function getYear(){
        $cYear = date('Y');
        $year = [];
        for ($i=0; $i <20 ; $i++) { 
            $year[] = $cYear-10+$i;
        }
        return $year;
    }

    //收支明细页面
    public function payDetails(){
        $where = $this->getWhere();
        $where['type'] = C('BILL_TYPE.PROPERTY');
        $orderModel = D('Wxorder');
        $orderList = $orderModel->getOrderList($where);
        $orderList = $this->getAccount($orderList);
        $community = D('property')->getCommunityByCompid($where['cm_id']);
        $propertycommon = A('Propertycommon');
        if (I('get.ccid')) $this->assign('buildings', $propertycommon->getBuildLists(I('get.ccid')));
        if (I('get.bmid')) $this->assign('rooms', $propertycommon->getHouseLists(I('get.bmid')));
        $this->assign('community', $community);
        $this->assign('orderList', $orderList);
        $this->assign('compid', $where['cm_id']);
        $this->assign('year', $this->getYear());
        $this->display();
    }

    //获取操作人信息
    public function getAccount($orderList){
        foreach ($orderList as $order) {
            $uids[$order['uid']] = $order['uid']; 
        }
        $users = D('User')->getUserById($uids);
        foreach ($users as $user) {
            $accounts[$user['id']] = $user['code'];
        }
        foreach ($orderList as $key => $order) {
            $orderList[$key]['code'] = $accounts[$order['uid']];
        }
        return $orderList;
    }

    //获取订单的各个账单详情
    public function getBills(){
        $ac_ids = I('post.ac_ids');
        $type = I('post.type');
        $ids = explode(',', $ac_ids);
        switch($type){
            case C('BILL_TYPE.PROPERTY'):
                $data = D('Accounts')->getBills($ids, $type);
                break;
            case C('BILL_TYPE.CAR'):
                $data = D('Carfee')->getCarBills($ids);
                break;
            case C('BILL_TYPE.LEASE'):
                $data = D('Lease')->getLeaseBills($ids);
                break;
        }
        foreach ($data as $key => $item) {
            $data[$key]['total'] = $item['money'] - $item['preferential_money'] + $item['penalty'];
            $data[$key]['total'] = number_format($data[$key]['total'], 2);
            foreach ($item as $k => $v) {
                if (!$v) $data[$key][$k] = ($k == 'description') ? '' : 0;
            }
        }
        $data ? retMessage(true, $data) : retMessage(false, null, '获取不到数据', '', 4001);
    }

    //停车费收支明细界面
    public function carPayDetails(){
        $where = $this->getWhere();
        $where['type'] = C('BILL_TYPE.CAR');
        $orderModel = D('Wxorder');
        $orderList = $orderModel->getCarPayDetails($where);
        $orderList = $this->getAccount($orderList);
        $community = D('property')->getCommunityByCompid($where['cm_id']);
        $this->assign('community', $community);
        $this->assign('orderList', $orderList);
        $this->assign('compid', $where['cm_id']);
        $this->assign('year', $this->getYear());
        $this->display();
    }
    //合约账单收支明细界面
    public function leasePayDetails(){
        $where = $this->getWhere();
        $where['type'] = C('BILL_TYPE.LEASE');
        $orderModel = D('Wxorder');
        $orderList = $orderModel->getOrderList($where);
        $orderList = $this->getAccount($orderList);
        $community = D('property')->getCommunityByCompid($where['cm_id']);
        $propertycommon = A('Propertycommon');
        if (I('get.ccid')) $this->assign('buildings', $propertycommon->getBuildLists(I('get.ccid')));
        if (I('get.bmid')) $this->assign('rooms', $propertycommon->getHouseLists(I('get.bmid')));
        $this->assign('community', $community);
        $this->assign('orderList', $orderList);
        $this->assign('compid', $where['cm_id']);
        $this->assign('year', $this->getYear());
        $this->display();
    }
}