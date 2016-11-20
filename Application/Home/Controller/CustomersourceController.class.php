<?php
/*
* 文件名：CustomersourceController.class.php
* 功能：客源控制器
* 日期：2016-01-18
* 作者：XU
* 版权：Copyright @ 2015 风馨科技 All Rights Reserved
*/

namespace Home\Controller;
use Think\Controller;

class CustomersourceController extends AccessController{
    protected $search_map = [
        'compid' => 'cm_id',
        'ccid' => 'cc_id',
        'name' => 'name',
        'tp' => 'type',
        'rt' => 'room_type',
        'ft' => 'furnish_type',
        'inten' => 'intention',
        'st' => 'status',
    ];

    public function _initialize(){
        header("Content-Type:text/html;charset=utf-8");
        parent::_initialize();
    }

    //客源管理页面
    public function index(){
        $where = $this->getWhere();
        $page = I('get.page', 1);
        $limit = I('get.limit', 10);
        $flag = I('get.flag', '');
        $csModel = D('Customersource');
        $total_data = $csModel->getCustomerList($where);
        $data = array_slice($total_data, ($page-1)*$limit, $limit);
        $community = D('property')->getCommunityByCompid($where['cm_id']);
        $data = $this->getDataDetails($data, $community);
        $statistics = $this->statistic($total_data, $limit);
        if ($flag) 
            retMessage(true,['data' => $data, 'total' => $statistics['page']]);
        $this->assign('data', $data);
        $this->assign('statistics', $statistics);
        $this->assign('furnish_type', C('FURNISH_TYPE'));
        $this->assign('type', C('TYPE_DEMAND'));
        $this->assign('room_type', C('ROOM_TYPE'));
        $this->assign('intention', C('INTENTION'));
        $this->assign('status', C('CUSTOMER_STATUS'));
        $this->assign('community', $community);
        $this->assign('compid', $where['cm_id']);
        $this->assign('where', $where); //用于保存搜索条件
        $this->display();
    }

    //获取查询条件
    public function getWhere(){
        foreach (I('get.') as $key => $value) {
            if (array_key_exists($key, $this->search_map) && $value) {
                $where[$this->search_map[$key]]  = $value;
            }
        }
        return $where;
    }

    //获取数据信息
    public function getDataDetails($data, $community=''){
        foreach ($community as $key => $value) {
            $comm[$value['id']] = $value;
        }
        foreach ($data as $k => $v) {
            $data[$k]['cc_name'] = $v['cc_id'] ? $comm[$v['cc_id']]['name'] : '不限';
            $data[$k]['rt_name'] = $v['room_type'] ? C("ROOM_TYPE.".$v['room_type']) : '不限';
            $data[$k]['t_name'] = $v['type'] ? C("TYPE_DEMAND.".$v['type']) : '不限';
            $data[$k]['ft_name'] = $v['furnish_type'] ? C("FURNISH_TYPE.".$v['furnish_type']) : '不限'; 
            $data[$k]['st_name'] = $v['status'] ? C("CUSTOMER_STATUS.".$v['status']) : '不限';
            $data[$k]['it_name'] = $v['intention'] ? C("INTENTION.".$v['intention']) : '不限';
            $data[$k]['other_demand'] = $v['other_demand'] ? $v['other_demand'] : '';
            $data[$k]['sign_time'] = substr($v['sign_time'], 0, 10);
        }
        return $data;
    }

    //组合统计信息
    public function statistic($customers, $limit){
        $statistic = [
            'total' => count($customers),
            'signed' => 0,
            'finished' => 0,
            'unsigned' => 0,
            'page' => ceil(count($customers)/$limit),
        ];
        foreach ($customers as $key => $customer) {
            if(C('CUSTOMER_STATUS.'.$customer['status']) == '未签约') $statistic['unsigned']++;
            if(C('CUSTOMER_STATUS.'.$customer['status']) == '已签约') $statistic['signed']++;
            if(C('CUSTOMER_STATUS.'.$customer['status']) == '终止委托') $statistic['finished']++;
        }
        return $statistic;
    }

    //匹配房源
    public function match(){
        $id = I('post.id');
        $page = I('get.page', 1);
        $limit = I('get.limit', 5);
        $customer = D('Customersource')->getCustomerById($id);
        $customer_data = [
            'room_type' => $customer['room_type'],
            'type' => $customer['type'],
            'cm_id' => $customer['cm_id'],
            'furnish_type' => $customer['furnish_type'],
            'cc_id' => $customer['cc_id'],
            'status' => 1,
            'minarea' => explode('-', $customer['area'])[0],
            'maxarea' =>  explode('-', $customer['area'])[1],
        ];
        $rooms = D('Room')->getRoomData($customer_data);
        $data = $this->getDataDetails(array_slice($rooms, ($page-1)*$limit, $limit));
        retMessage(true, ['data' => $data, 'total' => count($rooms), 'page' => ceil(count($rooms)/$limit)]);
    }

    //获取跟进信息并显示
    public function follow(){
        $id = I('post.id');
        $page = I('get.page', 1);
        $limit = I('post.limit', 5);
        $total_data = D('follow')->getFollow($id, 'customer');
        $data = array_slice($total_data,  ($page-1)*$limit, $limit);
        retMessage(true, ['data' => $data, 'total' => count($total_data), 'page' => ceil(count($total_data)/$limit)]);
    }
    //添加跟进信息
    public function addFollow(){
        $data = I('post.');
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['uid'] = $this->userID;
        if (D('follow')->addFollow($data)){
            $msg = D('user')->getOneUser($this->userID);
            $msg['create_time'] = $data['create_time'];
            retMessage(true, $msg);
        }else{
            retMessage(false, null, '添加失败', '', 4001);
        }
    }

    //登记客源页面
    public function addcustomer(){
        $compid = I('get.compid');
        $community = D('property')->getCommunityByCompid($compid);
        $this->assign('user', D('User')->getOneUser($this->userID));
        $this->assign('furnish_type', C('FURNISH_TYPE'));
        $this->assign('type', C('TYPE_DEMAND'));
        $this->assign('room_type', C('ROOM_TYPE'));
        $this->assign('community', $community);
        $this->assign('compid', $compid);
        $this->assign('sign_time', date('Y-m-d'));
        $this->display();
    }

    //登记客源操作
    public function doAdd(){
        //检测客源手机号是否存在并作相应处理
        $data = I('post.');
        $customerModel = D('Customersource');
        $check = $customerModel->checkCustomer($data['cm_id'], $data['phone']);
        $data['customer_id'] = $check ? $check['id'] : $this->add($data['cm_id'], $data['phone'], $data['name']);
        $data['area'] = $data['minArea'] . '-' . $data['maxArea'];
        $data['price'] = $data['minPrice'] . '-' . $data['maxPrice'];
        $data['uid'] = $this->userID;
        unset($data['phone'], $data['minArea'], $data['maxArea'], $data['minPrice'], $data['maxPrice']);
        if ($data['customer_id']) {
             foreach ($data as $key => $value) {
                $data[$key] = $value ? $value : NULL ;
             }
             ($customerModel->addCustomer($data)) ? retMessage(true, null) : retMessage(false, null, '添加客源失败', '', 4001);
        }else{
            retMessage(false, null, '添加客户失败', '', 4002);
        }
    }

    //若找不到客户，则进行添加
    public function add($compid, $phone, $name){
        $csModel = D('Customersource');
        $count = $csModel->countCustomer($compid);
        $count++;
        $count = str_pad($count, 5, '0', STR_PAD_LEFT);
        $count = substr(strval($count), -5);
        $data = [
            'cm_id' => $compid,
            'contact_number' => $phone,         
            'name' => $name,
            'create_time' => date('Y-m-d H:i:s'),
            'number' => 'KH'.$count,
        ];
       return M('property_pc_user')->add($data);
    }

    //客源详情
    public function details(){
        $compid = I('get.compid');
        $id = I('get.id');
        $data = D('Customersource')->getOneCustomer($id);
        $data['rt_name'] = $data['room_type'] ? C("ROOM_TYPE.".$data['room_type']) : '不限';
        $data['t_name'] = $data['type'] ? C("TYPE_DEMAND.".$data['type']) : '不限';
        $data['ft_name'] = $data['furnish_type'] ? C("FURNISH_TYPE.".$data['furnish_type']) : '不限'; 
        $data['st_name'] = $data['status'] ? C("CUSTOMER_STATUS.".$data['status']) : '不限';
        $data['it_name'] = $data['intention'] ? C("INTENTION.".$data['intention']) : '不限';
        $data['sign_time'] = substr($data['sign_time'], 0, 10);
        $this->assign('data', $data);
        $this->assign('id', $id);
        $this->assign('compid', $compid);
        $this->display();
    }

    public function save(){
        $data = I('post.');
        $result = D('Customersource')->saveCustomer($data);
        $result ? retMessage(true, null) : retMessage(false, null);
    }
}