<?php
/*************************************************
 * 文件名：PayorderController.class.php
 * 功能：     素材管理控制器
 * 日期：     2016.1.5
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;

class PayorderController extends AccessController
{

    protected $accountsModel;

    protected $wxorderModel;

    protected $payorderModel;

    protected $carfeeModel;

    protected $leaseModel;

    /**
     * 初始化
     */
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 缴费项目明细页面
     */
    public function index()
    {
        //获取该房间待机费项列表
        $type = I('get.type', 'house');
        $id = I('get.id', '');
        $function = new \ReflectionMethod(get_called_class(), 'get' . ucwords($type) . 'Unpay');
        $result = $function->invoke($this, $id);

        $this->assign('uid', $this->userID);
        $this->assign('unPayLists', $result['list']);
        $this->assign('propertyInfo', $result['info']);
        $this->display();
    }

    /**
     * 获取房产待缴费列表
     * @param int $id 房间ID
     * @return array
     */
    public function getHouseUnpay($id)
    {
        $this->accountsModel = D('accounts');
        $unPayLists = $this->accountsModel->getPayAccounts($this->companyID, [$id]);
        $unPayLists = $this->calculationAccountsMoney($unPayLists);
        //查询该房间所属信息
        $propertyModel = D('property');
        $propertyInfo = $propertyModel->getPropertyBelog($id, 'house');
        return ['list' => $unPayLists, 'info' => $propertyInfo];
    }

    /**
     * 获取车位待缴费列表
     * @param int $id 车位ID
     * @return array
     */
    public function getCarUnpay($id)
    {
        $this->carfeeModel = D('carfee');
        $unPayLists = $this->carfeeModel->getPayAccounts($this->companyID, $id);
        $unPayLists = $this->calculationAccountsMoney($unPayLists);
        //查询该楼盘名称
        $propertyModel = D('property');
        $propertyInfo = $propertyModel->getPropertyBelog($unPayLists[0]['cc_id'], 'community');
        return ['list' => $unPayLists, 'info' => $propertyInfo];
    }
    /**
     * 获取租赁合约待缴费列表
     * @param int $id
     * @return array
     */
    public function getLeaseUnpay($id)
    {
        $this->leaseModel = D('Lease');
        $unPayLists = $this->leaseModel->getPayAccounts($this->companyID, $id);
        $unPayLists = $this->calculationAccountsMoney($unPayLists);
        //楼盘名称
        $propertys = explode(' ', $unPayLists[0]['property']);
        $payList['info'] =  [
            'cc_name' => $propertys[0],
            'bm_name' => $propertys[1],
            'hm_name' => $propertys[2]
        ];
        foreach($unPayLists as $list){
            $date = explode('-', $list['payment']);
            $list['date'] = $date[0].'年'.$date[1].'月'.$date[2].'日';
            $payList['list'][] = $list;
        }
        return $payList;
    }

    /**
     * 计算账单最终金额
     * @param array $unPayLists 账单列表
     * @return array
     */
    public function calculationAccountsMoney(array $unPayLists)
    {
        foreach ($unPayLists as $un => $unPayList) {
            $unPayLists[$un]['preferential_money'] = $unPayList['preferential_money'] = $unPayList['preferential_money'] ? $unPayList['preferential_money'] : 0;
            $unPayLists[$un]['penalty'] = $unPayList['penalty'] = $unPayList['penalty'] ?: 0;
            $unPayLists[$un]['total'] = number_format((floatval($unPayList['money']) - floatval($unPayList['preferential_money']) + floatval($unPayList['penalty'])), 2, '.','');
        }
        return $unPayLists;
    }

    /**
     * 生成订单
     */
    public function generateOrder()
    {
        //接收数据
        $compid = I('post.compid', '');
        $id = I('post.id', '');
        $uid = I('post.uid', '');
        $idType = I('post.idType', '');
        $arr = I('post.arr', '');
        $total = I('post.total', []);
        switch(I('post.type', 'house')){
            case 'house':
                $type = 1;
                break;
            case 'car':
                $type = 2;
                break;
            case 'lease':
                $type = 3;
                break;
        }
        $payType = I('post.payType', '');
        if (!$compid || !$id || !$uid || !$idType || empty($arr) || !$total || !$type || !$payType) {
            retMessage(false, null, '参数错误，请检查参数', '参数错误，请检查参数', 4001);
            exit;
        }
        $this->wxorderModel = D('wxorder');
        $result = $this->wxorderModel->submitUnPayOrder($compid, $arr, $id, $total, $uid, $idType, $type, $payType);
        $result ? retMessage(true, $result) : retMessage(false, null, '提交订单失败', '提交订单失败', 4002);
        exit;
    }

    /**
     * 缴费确认页面
     */
    public function confirmpay()
    {

        $oid = I('get.oid', '');
        $type=I('get.type','house');
        //查询订单详情
        $this->wxorderModel = D('wxorder');
        $orderInfo = $this->wxorderModel->getTempOrderInfo($this->companyID, $oid, $type);

        $this->assign('uid', $this->userID);
        $this->assign('orderInfo', $orderInfo);
        $this->display();
    }

    /**
     * 检查订单中是否存在已缴账单
     */
    public function checkAccountsByOrder()
    {
        //接收数据
        $id = I('post.id', '');
        $type = I('post.type', '');
        if (!$id || !$type) {
            retMessage(false, null, '参数错误，请检查参数', '参数错误，请检查参数', 4001);
            exit;
        }
        $orderType = 1;
        if ($type == 'car') $orderType = 2;
        $this->wxorderModel = D('wxorder');
        $acIds = $this->wxorderModel->getAccountIds($id, $orderType)['ac_ids'];
        if($type=='car'){
            $this->carfeeModel=D('carfee');
            $results=$this->carfeeModel->getCarBills($acIds);
        }else{
            $this->accountsModel = D('accounts');
            $results = $this->accountsModel->getBills($acIds);
        }
        $flag = 0;
        foreach ($results as $result) {
            if ($result['status'] == 2) {
                $flag = 1;
                break;
            }
        }
        ($flag == 1) ? retMessage(false, null, '该订单存在已缴账单', '该订单存在已缴账单', 4002) : retMessage(true, null);
        exit;
    }

    /**
     * 执行缴费确认
     */
    public function doConfirmPay()
    {
        //接收数据
        $compid = I('post.compid', '');
        $uid = I('post.uid', '');
        $oid = I('post.oid', '');
        switch(I('post.type', 'house')){
            case 'house':
                $type = 1;
                break;
            case 'car':
                $type = 2;
                break;
            case 'lease':
                $type = 3;
                break;
        }
        $arr = I('post.arr', []);
        $total = I('post.total', '');
        $payUser = I('post.payUser', '');
        if (!$compid || !$uid || !$oid || !$type || empty($arr) || !$total || !$payUser) {
            retMessage(false, null, '参数错误，请检查参数', '参数错误，请检查参数', 4001);
            exit;
        }

        //查询订单所包含的账单
        $this->wxorderModel = D('wxorder');
        $acIds = $this->wxorderModel->getAccountIds($oid, $type)['ac_ids'];
        $datas = [
            'cm_id' => $compid,
            'uid' => $uid,
            'order_id' => $oid,
            'ac_ids' => $acIds,
            'total' => $total,
            'status' => 2,
            'pay_date' => date('Y-m-d H:i:s'),
            'create_time' => date('Y-m-d H:i:s'),
            'pay_user' => $payUser,
            'arr' => $arr,
            'type' => $type
        ];
        $this->payorderModel = D('payorder');
        $result = $this->payorderModel->recordPayOrder($datas);
        $result ? retMessage(true, $result) : retMessage(false, null, '收款失败', '收款失败', 4002);
        exit;
    }
}