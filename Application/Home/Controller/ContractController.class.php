<?php
/*************************************************
 * 文件名：ContractController.class.php
 * 功能：     合约管理控制器
 * 日期：     2016.01.25
 * 作者：     XU
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/

namespace Home\Controller;

use Org\Util\RabbitMQ;

class ContractController extends AccessController {

    protected $contractModel;

    protected $query_map = [
        'compid' => 'cm_id',
        'nm' => 'number',
        'name' => 'name',
        'ct' => 'customer',
        's_time' => 'start_time',
        'e_time' => 'end_time',
        'dt' => 'date_type',
        'st' => 'status',
        'wn' => 'expire',
    ];

    protected $leaseQueue='lease_generation_queue';

    public function _initialize(){
        header("Content-Type:text/html;charset=utf-8");
        parent::_initialize();
        $this->contractModel=D('contract');
    }

    //合约管理页面
    public function index(){
        $where = $this->getWhere();
        $limit = I('get.limit', 10);
        $page = I('get.page', 1);
        $contracts = D('Contract')->getContractList($where);
        $data = array_slice($contracts, ($page-1)*$limit, $limit);
        $data = $this->setData($data);
        $this->assign('date_type', ['start_date' => '起始日期', 'end_date' => '结束日期', 'sign_date' => '签约日期']);
        $this->assign('where', $where);
        $this->assign('statistics', $this->statistic($contracts));
        $this->assign('data', $data);
        $this->assign('warning', D('Warning')->getWarning($where['cm_id'], C('WARNING_TYPE.CONTRACT'))['days']);
        $this->assign('status', C('CONTRACT_STATUS'));
        $this->assign('compid', $where['cm_id']);
        $this->display();
    }

    //过滤查询条件
    public function getWhere(){
        foreach (I('get.') as $key => $value) {
            if (array_key_exists($key, $this->query_map) && $value) {
                $where[$this->query_map[$key]] = $value;
            }
        }
        return $where;
    }

    //统计数据
    public function statistic($contracts){
        $statistic = [
            'total' => count($contracts),
            'valid' => 0,
            'expire' => 0,
            'stop' => 0,
        ];
        foreach ($contracts as $key => $contract) {
            if (C('CONTRACT_STATUS')[$contract['status']] == '已中止') $statistic['stop']++;
            if (C('CONTRACT_STATUS')[$contract['status']] == '已到期') $statistic['expire']++;
            if (C('CONTRACT_STATUS')[$contract['status']] == '已生效') $statistic['valid']++;
        }
        return $statistic;
    }

    public function setData($data){
        foreach ($data as $key => $value) {
            $data[$key]['start_date'] = substr($value['start_date'], 0, 10);
            $data[$key]['end_date'] = substr($value['end_date'], 0, 10);
        }
        return $data;
    }

    //合同详情界面
    public function details(){
        $compid = I('get.compid');
        $id = I('get.id');
        $contract = $this->contractModel->getContractById($id);
        $pictures = $this->contractModel->getPictureById($id);
        $pictures = $this->setPicture($pictures);
        $this->assign('pictures', $pictures);
        $this->assign('data', $contract);
        $this->assign('compid', $compid);
        $this->display();
    }

    public function setPicture($pictures){
        foreach ($pictures as $key => $picture) {
            $pictures[$key]['thumbnail'] = substr_replace($picture['pic_url'], '/thumbnail', strrpos($picture['pic_url'], '/'), 0);
        }
        return $pictures;
    }

    public function stopContract(){
        $contract_id = I('post.id');
        $result = D('Contract')->stopContract($contract_id);
        $result ? retMessage(true, null) : retMessage(false, null);
    }

    /**
     * 新建合约页面
     */
    public function addcontract()
    {
        $rsid = I('get.rsid', '');
        $csid = I('get.csid', '');
        $roomSourceInfo = '';
        $customerSourceInfo = '';
        //获取楼盘列表
        $propertyCommon = A('propertycommon');
        $ccLists = $propertyCommon->getCommunityLists($this->companyID);
        //查询房源信息
        if ($rsid) {
            $roomSourceInfo = $this->getRoomSourceInfo($rsid);
            $propertyCommon=A('propertycommon');
            $buildLists=$propertyCommon->getBuildLists($roomSourceInfo['cc_id']);
            $houseLists=$propertyCommon->getHouseLists($roomSourceInfo['bm_id'],false,'',true);
            $this->assign('roomSourceInfo', $roomSourceInfo);
            $this->assign('buildLists', $buildLists);
            $this->assign('houseLists', $houseLists);
        }
        //查询客源信息
        if ($csid) {
            $customerSourceInfo = $this->getCustomerSourceInfo($csid);
            $this->assign('customerSourceInfo', $customerSourceInfo);
        }
        $this->assign('ccLists', $ccLists);
        $this->display();
    }

    /**
     * 获取房源信息
     * @param int $id 房源ID
     * @return mixed
     */
    public function getRoomSourceInfo($id)
    {
        $roomModel = D('room');
        $info = $roomModel->getRoomInfo($id);
        return $info;
    }

    /**
     * 获取客源信息
     * @param int $id 客源ID
     * @return mixed
     */
    public function getCustomerSourceInfo($id)
    {
        $customerSourceModel = D('customersource');
        $info = $customerSourceModel->getOneCustomer($id);
        return $info;
    }

    /**
     * 检查合同编号是否重复
     */
    public function checkContractNumber()
    {
        $number = I('post.number', '');
        if (!$this->companyID || !$number) retMessage(false, null, '参数错误，请检查参数', '参数错误，请检查参数', 4001);
        $result = $this->contractModel->checkNumberExists($this->companyID, $number);
        (!$result) ? retMessage(true, null) : retMessage(false, null, '合同编号重复', '合同编号重复', 4002);
    }

    /**
     * 根据企业ID和客户手机号判断客源是否存在
     */
    public function getCustomerSourceExists()
    {
        $page = I('post.page', 1);
        $phone = I('post.phone', '');
        if (!$this->companyID || !$phone) retMessage(false, null, '参数错误，请检查参数', '参数错误，请检查参数', 4001);
        $customerSourceModel = D('customersource');
        $customerExists = $customerSourceModel->checkCustomer($this->companyID, $phone);
        if (!$customerExists) retMessage(false, null, '查找不到客户', '查找不到客户', 4002);
        $customerSourceLists = $customerSourceModel->getCustomerList(['cm_id' => $this->companyID, 'phone' => $phone ,'status'=>1]);
        $propertyCommon=A('propertycommon');
        $ccLists=$propertyCommon->getCommunityLists($this->companyID);
        foreach ($customerSourceLists as $cus => $customerSourceList) {
            $customerSourceLists[$cus]['type_name']=$customerSourceList['type']?C('TYPE_DEMAND')[$customerSourceList['type']]:'不限';
            $customerSourceLists[$cus]['room_type_name']=$customerSourceList['room_type']?C('ROOM_TYPE')[$customerSourceList['room_type']]:'不限';
            foreach ($ccLists as $ccList) {
                if(!$customerSourceList['cc_id']) {
                    $customerSourceLists[$cus]['cc_name']='不限';
                }else{
                    $customerSourceLists[$cus]['cc_name']=$ccList['name'];
                }
                break;;
            }
        }
        $customerSourceLists ? retMessage(true, ['lists' => array_slice($customerSourceLists, ($page - 1) * 10, 10), 'total' => count($customerSourceLists), 'totalPages' => ceil(count($customerSourceLists) / 10)]) : retMessage(false, null, '查找不到客源', '查找不到客源', 4003);
    }

    /**
     * 根据房间ID查询房源是否存在
     */
    public function getRoomSourceExists()
    {
        $id = I('post.id', '');
        if (!$this->companyID || !$id) retMessage(false, null, '参数错误，请检查参数', '参数错误，请检查参数', 4001);
        $roomModel = D('room');
        $roomExists = $roomModel->getRoomInfo($id, 'house');
        $roomExists ? retMessage(true, $roomExists) : retMessage(false, null, '查询不到房源', '查询不到房源', 4002);
    }

    /**
     * 保存合约并生效
     */
    public function saveContract()
    {
        $datas = [
            'cm_id' => $this->companyID,
            'number' => I('post.contractNumber', ''),
            'name' => I('post.contractName', ''),
            'start_date' => I('post.startDate', date('Y-m-d')),
            'end_date' => I('post.endDate', ''),
            'sign_date' => I('post.signDate', ''),
            'customer_mobile' => I('post.customerMobile', ''),
            'customer_name' => I('post.customerName', ''),
            'custom_id' => I('post.customerId', ''),
            'cert_number' => I('post.certNumber', ''),
            'room_id' => I('post.roomId', ''),
            'out_accounts_date' => I('post.outAccountsDate', ''),
            'deposit' => I('post.deposit', number_format(0, 2)),
            'cycle' => I('post.cycle', ''),
            'month' => I('post.month', ''),
            'days' => I('post.days', ''),
            'rent' => I('post.rent', number_format(0,2)),
            'is_increase' => I('post.isIncrease', -1),
            'increase_cycle' => I('post.increaseCycle', null),
            'increase_rent' => I('post.increaseRent', null),
            'ins_rent_type' => I('post.insRentType', null),
            'pictures' => I('post.pictureArr', []),
            'marketer' => I('post.marketer', null),
            'remark' => I('post.remark', null),
        ];
        if (!$this->filterAddContractDatas($datas)) retMessage(false, null, '参数错误，请检查参数', '参数错误，请检查参数', 4001);
        if (!$datas['custom_id']) {
            $datas['custom_id'] = $customerSourceResult = $this->autoCreateCustomerSource($this->companyID, $this->userID, $datas['customer_mobile'], $datas['customer_name']);
            if (!$customerSourceResult) retMessage(false, null, '创建客源失败', '创建客源失败', 4003);
        }
        unset($datas['customer_mobile']);
        unset($datas['customer_name']);
        $result = $this->contractModel->addContract($datas);
        if (!$result) retMessage(false, null, '新建合约失败', '新建合约失败', 4002);
        RabbitMQ::publish($this->leaseQueue, json_encode(['compid' => $this->companyID, 'contract_id' => $result]));
        retMessage(true, null);
    }

    /**
     * 检查新增合约数据
     * @param array $datas
     * @return bool
     */
    public function filterAddContractDatas(array $datas)
    {
        if (!$datas['cm_id'] || !$datas['number'] || !$datas['name'] || !$datas['sign_date'] || !$datas['end_date'] || !$datas['customer_mobile'] || !$datas['customer_name'] || !$datas['cert_number'] || !$datas['room_id'] || !$datas['out_accounts_date'] || !$datas['deposit'] || !$datas['cycle'] || !$datas['rent']) return false;
        if (($datas['cycle'] > 1) && !($datas['month'] || $datas['days'])) return false;
        if (($datas['is_increase'] == 1) && !($datas['increase_cycle'] || $datas['increase_rent'] || $datas['ins_rent_type'])) return false;
        return true;
    }

    /**
     * 自动创建客户或客源
     * @param int $compid
     * @param int $uid
     * @param int $phone
     * @param string $name
     * @return bool|string
     */
    public function autoCreateCustomerSource($compid, $uid, $phone, $name)
    {
        $customerSourceModel = D('customersource');
        $customerExists = $customerSourceModel->checkCustomer($compid, $phone);
        if(!$customerExists){
            $customerExists['id']=$customer=$this->createCustomer($customerSourceModel,$compid,$phone,$name);
            if(!$customer) return false;
        }
        $customerSourceExists = $customerSourceModel->getCustomerList(['cm_id' => $compid, 'phone' => $phone]);
        $customerSource=$this->createCustomerSource($customerSourceModel,$customerExists['id'],$compid,$uid,$name);
        return $customerSource;
    }

    /**
     * 创建客户
     * @param object $customerSourceModel
     * @param ine $compid
     * @param int $phone
     * @param string $name
     * @return bool
     */
    public function createCustomer($customerSourceModel, $compid, $phone, $name)
    {
        $customerCounts = $customerSourceModel->countCustomer($compid);
        $customerDatas = ['name' => $name, 'contact_number' => $phone, 'create_time' => date('Y-m-d H:i:s'), 'number' => 'KH' . str_pad(($customerCounts + 1), 5, 0, STR_PAD_LEFT), 'cm_id' => $compid];
        $customerModel = D('customer');
        $customer = $customerModel->addCustomer($customerDatas);
        if (!$customer) return false;
        return $customer;
    }

    /**
     * 创建客源
     * @param object $customerSourceModel
     * @param int $customerId
     * @param int $compid
     * @param int $uid
     * @param string $name
     * @return bool
     */
    public function createCustomerSource($customerSourceModel, $customerId, $compid, $uid, $name)
    {
        $customerSourceDatas = ['customer_id' => $customerId, 'cm_id' => $compid, 'area' => '0-9999', 'price' => '0-99999', 'status' => 1, 'uid' => $uid, 'sign_time' => date('Y-m-d H:i:s'), 'name' => $name];
        $customerSource = $customerSourceModel->addCustomer($customerSourceDatas);
        if (!$customerSource) return false;
        return $customerSource;
    }
}