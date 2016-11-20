<?php
/*************************************************
 * 文件名：LeaseController.class.php
 * 功能：     租赁控制器
 * 日期：     2015.1.18
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
use Think\Model;
class LeaseController extends AccessController
{

    public function _initialize()
    {
        parent::_initialize();
    }

    public function index(){

        //获取数据页号
        $delPage = I('get.page', '');
        $star = $delPage != ''?($delPage-1)*10:0;
        $condition = array();
        //当前年月日
        $currentDate = date('Y-m-d');
        //本月第一天(日期默认值)
        $dateArray = explode('-', $currentDate);
        $currentFirst = $dateArray[0].'-'.$dateArray[1].'-'.'01';
        //有传合约ID过来
        $contractID = I('get.contract_id', '');
        if(!empty($contractID)){
            $condition = [
                'contract' => $contractID,
            ];
        }
        //是否有预警筛选
        $warning = I('get.warning', -1);
        //是否有搜索提交过来的数据
        if(I('get.select','') == 'select') {
            $condition = [
                'end_date' => I('get.end_date', $currentDate),
                'start_date' => I('get.start_date', $currentFirst),
                'property' => I('get.property', ''),
                'build' => I('get.build', ''),
                'house' => I('get.house', ''),
                'payer' => I('get.payer', ''),
                'warning' => $warning
            ];
            if(!empty($condition['property'])){
                $buildMod = D('building');
                $condition['b_select'] = $buildMod->selectAllBuild($condition['property'],0,0,1);
            }
            if(!empty($condition['build'])){
                $houseMod = D('house');
                $condition['h_select'] = $houseMod->selectAllHouse($condition['build']);
                foreach($condition['b_select'] as &$b){
                    if($b['id'] == $condition['build']){
                        $b['sele'] = 'selected';
                    }
                }
            }
            if(!empty($condition['house'])){
                foreach($condition['h_select'] as &$h){
                    if($h['id'] == $condition['house']){
                        $h['sele'] = 'selected';
                    }
                }
            }
        }
        //dump($condition);
        $warningMod = M('sys_warning');
        $days = $warningMod->where(['type'=>2,'cm_id'=>$this->companyID])
            ->getField('days');
        //查询所有楼盘($compid, $condition='', $switch=1, $type=-1, $start=0, $end=10)
        $properMod = D('property');
        $propertys = $properMod->getPropertys($this->companyID);
        foreach($propertys as &$pro){
            if($condition['property'] == $pro['id']){
                $pro['selected'] = 'selected';
            }
        }
        //查询订单
        $leaseMod = D('Lease');
        $bills = $leaseMod->selectLeaseBill($this->companyID, $condition, 1, 1, $star);
        $billsToHtml = $this->billsToHtml($bills);
        //总记录条数
        $count = $leaseMod->selectLeaseBill($this->companyID, $condition, 2);
        $this->assign('propertys', $propertys);
        $this->assign('count', $count);
        $this->assign('bills', $billsToHtml);
        $this->assign('month2', $currentFirst);
        $this->assign('month1', $currentDate);
        $this->assign('compid', $this->companyID);
        $this->assign('seach', $condition);
        $this->assign('warn', $warning);
        $this->assign('days', $days);
        $this->display();

    }
    //根据楼盘查询楼栋
    public function build(){
        $cc_id = I('post.p_id', '');
        //所有正常的楼栋
        $buildMod = D('building');
        $builds = $buildMod->selectAllBuild($cc_id, 0, 0, 1);
        $builds = $builds!=null?$builds:array('fail');
        exit(json_encode($builds));
    }
    //根据楼栋查询房间
    public function house(){
        $bu_id = I('post.bu_id');
        //所有正常的楼栋
        $houseMod = D('house');
        $houses = $houseMod->selectAllHouse($bu_id);
        $houses = $houses!=null?$houses:array('fail');
        exit(json_encode($houses));
    }
    //删除账单
    public function delete(){
        $billID = I('post.id', '');
        $billsMod = D('Lease');
        $del = $billsMod->where('id=%d', $billID)
            ->setField('status', -2);;
        if($del){
            $result = 'success';
        }else{
            $result = $del == 0?'empty':'fail';
        }
        exit($result);
    }
    //修改优惠和滞纳金额
    public function modifly(){
        $billID = I('post.id', '');
        //修改类型
        $type = I('post.type', '');
        $field = '';
        if($type == 'discount'){
            $field = 'discount';
        }
        if($type == 'late'){
            $field = 'delaying';
        }

        //金额
        $money = I('post.value', 0);
        $billsMod = D('Lease');
        $modifly = $billsMod->where('id=%d', $billID)
            ->setField($field, $money);
        if($modifly){
            $result = 'success';
        }else{
            $result = $modifly == 0?'empty':'fail';
        }
        exit($result);
    }
    //分页
    public function page(){
        $start = I('post.page', 0);
        $compid = I('post.compid', '');
        //是否预警
        $warning = I('get.warning', '');
        //查询账单预警天数
        $warningMod = M('sys_warning');
        $days = $warningMod->where(['type'=>2,'cm_id'=>$compid])
            ->getField('days');
        $condition = [
            'end_date' => I('get.end_date',''),
            'start_date' => I('get.start_date',''),
            'property' => I('post.property', ''),
            'build' => I('post.build', ''),
            'house' => I('post.house', ''),
            'payer' => I('post.payer', ''),
            'warning' => I('post.warning', -1),
        ];

        //查询订单
        $leaseMod = D('Lease');
        $bills = $leaseMod->selectLeaseBill($compid, $condition, 1, 1, $start);
        $billsToHtml = $this->billsToHtml($bills, $warning, $days);
        exit(json_encode($billsToHtml));
    }
    //租赁统计
    public function count(){
        //当前选择的日期
        $currentDate = strtotime(date('Y-m'));
        $postDate = strtotime(I('get.date', ''));
        $date = empty($postDate)? $currentDate: $postDate;
        //总拖管费
        $roomMod = D('Room');
        $entrustList = $roomMod->getRoomToCompany($this->companyID);
        $total = $this->total($entrustList, $date);
        $entrustTotal = [
            'total' => $total['total'],
            'tally' => $total['tally']
        ];

        //合约到期
        $contractMod = D('Contract');
        $contract = $contractMod->getCntractForStatus($this->companyID);
        $contractTotal = $this->total($contract, $date)['tally'];
       // dump($contract);exit;
        //月增房源
        $roomTotal = $entrustTotal['tally'];
        //月签约房源
        $roomSource = $roomMod->getRoomToCompany($this->companyID, 2);
        $signRoom = 0;
        foreach($roomSource as $rs){
            $signTime = strtotime(substr($rs['sign_time'], 0, 7));
            if($signTime == $date){
                $signRoom++;
            }
        }
        //房源终止拖管
        $stopRoom = count($roomMod->getRoomToCompany($this->companyID, 3));

        //月增客源
        $customerMod = D('Customersource');
        $customerList = $customerMod->getCustomerToCompany($this->companyID);
        $customer = 0;
        $signCustomer = 0;
        foreach($customerList as $cu){
            $signTime = strtotime(substr($cu['time'], 0, 7));
            if($signTime == $date){
                $customer++;
            }
            //月签约客源
            if($signTime == $date && $cu['status'] == 2){
                $signCustomer++;
            }
        }
        $customerTotal = $customer;
        //客源委托终止
        $stopCustomer = count($customerMod->getCustomerToCompany($this->companyID, 3));
        //统计
        $leaseTotal = [
            'entrust' => empty($entrustTotal['total'])? 0: $entrustTotal['total'],
            'tally' => $entrustTotal['tally'],
            'c_stop' => $contractTotal,
            'r_total' => $roomTotal,
            'r_sign' => $signRoom,
            'r_stop' => $stopRoom,
            'cu_total' => $customerTotal,
            'cu_sign' => $signCustomer,
            'cu_stop' => $stopCustomer
        ];
        $this->assign('lease', $leaseTotal);
        $this->display();
    }
    //账单计算
    private function total($arr, $date){
        $total = [];
        $tally = 0;
        foreach($arr as $en){
            $start = strtotime(substr($en['start_time'], 0, 7));
            $end = strtotime(substr($en['end_time'], 0, 7));
            if(($date >= $start) && ($date <= $end)){
                $total['total'] += isset($en['total'])? $en['total']: 1;
                $tally++;
            }
        }
        $total['tally'] += $tally;
        return $total;
    }
    //账单记录转成页面数据
    public function billsToHtml($bills)
    {
        $html = array();
        foreach($bills as $bill){
            $red = $bill['status'] == 3? ' lis_red': '';
            $date = explode('-', $bill['payment']);
            $dateString = $date[0].'年'.$date[1].'月'.$date[2].'日';
            $html[] = [
                'id' => $bill['id'],
                'cm_id' => $bill['cm_id'],
                'hm_id' => $bill['hm_id'],
                'class' => $red,
                'date' => $dateString,
                'estate' => $bill['property'],
                'payer' => $bill['payer'],
                'contact' => $bill['contact'],
                'money' => $bill['money'],
                'discount' => $bill['discount']?$bill['discount']:'0.00',
                'delaying' => $bill['delaying']?$bill['delaying']:'0.00',
                'status' => $bill['status'],
                'total' => sprintf(" %1\$.2f",$bill['money']-$bill['p_money']+$bill['penalty'])
            ];
        }
        return $html;
    }


}