<?php
/*************************************************
 * 文件名：ParkingController.class.php
 * 功能：     停车费账单控制器
 * 日期：     2015.12.29
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
use Org\Util\RabbitMQ;
class ParkingController extends AccessController
{
    public function _initialize()
    {
        header("Content-Type:text/html;charset=utf-8");
        parent::_initialize();
    }

    public function index()
    {
        //获取数据页号
        $delPage = I('get.page', '');
        //生成10年前和10后的年份，月份
        $date = explode('-', date('Y-m'));
        //当前年份
        $currentYear = $date[0];
        //当前月份
        $currentMonth = $date[1];
        //10年前年份
        $tenYearAgo = $currentYear-10;
        //循环输出年份数组
        $years = array();
        for($i=0;$i<=20;$i++){
            $selectde = '';
            $year = $tenYearAgo+$i;
            if($year == $currentYear){
                $selectde = 'selected';
            }
            $years[] = array(
                'year' => $year,
                'selected' => $selectde
            );
        }
        //循环输出月份组
        $months = array();
        for($i=1;$i<=12;$i++){
            $selectde = '';
            if($i == $currentMonth){
                $selectde = 'selected';
            }
            $months[] = array(
                'month' => $i,
                'selected' => $selectde
            );
        }
        //查询所有楼盘
        $properMod = D('property');
        $propertys = $properMod->getPropertys($this->companyID);
        //查询已生成但未发布的账单
        $carfeeMod = D('Carfee');
        $star = $delPage != ''?($delPage-1)*10:0;
        $bills = $carfeeMod->selectBill($this->companyID, -1, $star);
        foreach($bills as &$bill){
            $bill['p_money'] = $bill['p_money']?$bill['p_money']:'0.00';
            $bill['penalty'] = $bill['penalty']?$bill['penalty']:'0.00';
            $bill['total'] = sprintf(" %1\$.2f", $bill['money']-$bill['p_money']+$bill['penalty']);
        }
        //总记录条数
        $count = $carfeeMod->parkingCount($this->companyID);

        $this->assign('years', $years);
        $this->assign('months', $months);
        $this->assign('propertys', $propertys);
        $this->assign('list', $bills);
        $this->assign('count', $count);
        $this->assign('compid', $this->companyID);
        $this->display();
    }
    //查找楼盘下的所有停车卡
    public function card(){
        $ccid = I('post.cc_id', '');
        $carMod = D('Car');
        $card = $carMod->getParkForProperty($ccid);
        $card = empty($card)?['fail']:$card;
        exit(json_encode($card));

    }
    //生成账单
    public function generation(){
        $compid = I('post.compid', '');
        $year = I('post.year', '');
        $month = I('post.month', '');
        $property = I('post.property', '');
        $card = I('post.card', '');
        $data = array(
            'compid' => $compid,
            'year' => $year,
            'month' => $month,
            'property' => $property,
            'card' => $card
        );
        //推送到队列中
        $queue = 'car_generation_queue';
        RabbitMQ::publish($queue, json_encode($data));
        exit('任务已提交后台处理<br/>3秒后将自动刷新页面');
    }
    //分页
    public function page(){
        $compid = I('post.compid', '');
        $star = I('post.page', 0);
        $billsMod = D('Carfee');
        $bills = $billsMod->selectBill($compid, -1, $star, 9);
        foreach($bills as &$bill){
            $bill['p_money'] = $bill['p_money']?$bill['p_money']:'0.00';
            $bill['penalty'] = $bill['penalty']?$bill['penalty']:'0.00';
            $bill['total'] = sprintf(" %1\$.2f", $bill['money']-$bill['p_money']+$bill['penalty']);
        }
        exit(json_encode($bills));
    }
    //删除账单
    public function delete(){
        $billID = I('post.id', '');
        $billsMod = D('Carfee');
        $del = $billsMod->delete($billID);
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
            $field = 'preferential_money';
        }
        if($type == 'late'){
            $field = 'penalty';
        }

        //金额
        $money = I('post.value', 0);
        $billsMod = D('Carfee');
        $modifly = $billsMod->where('id=%d', $billID)
            ->setField($field, $money);
        if($modifly){
            $result = 'success';
        }else{
            $result = $modifly == 0?'empty':'fail';
        }
        exit($result);
    }
    //发布账单
    function publish(){
        $compid = I('post.compid', '');
        $billsMod = D('Carfee');
        $update = $billsMod->publishBill($compid);
        if($update){
            $result = 'success';
        }else{
            $result = $update == 0?'empty':'fail';
        }
        exit($result);

    }
    //待缴费账单
    function payment(){
        //生成10年前和10后的年份，月份
        $date = explode('-', date('Y-m'));
        //当前年份
        $currentYear = $date[0];
        //当前月份
        $currentMonth = $date[1];
        //10年前年份
        $tenYearAgo = $currentYear-10;
        //是否有搜索提交过来的数据
        if(I('get.search', '') == 'submit'){
            $condition = array(
                //年份
                'year' => I('get.year', $currentYear),
                //月份
                'month' => I('get.month', $currentMonth),
                //楼盘
                'property' => I('get.property', ''),
                //停车卡号
                'card' => I('get.card', ''),
            );
        }else{
            $condition = array();
        }
        //循环输出年份数组
        $years = array();
        for($i=0;$i<=20;$i++){
            $selectde = '';
            $year = $tenYearAgo+$i;
            if(empty($condition['year']) && $year == $currentYear){
                $selectde = 'selected';
            }
            if(!empty($condition['year']) &&  $year == $condition['year']){
                $selectde = 'selected';
            }
            $years[] = array(
                'year' => $year,
                'value' => $year,
                'selected' => $selectde
            );
        }
        //循环输出月份组
        $months = array();
        for($i=1;$i<=12;$i++){
            $selectde = '';
            if(!empty($condition['month']) &&  $i == $condition['month']){
                $selectde = 'selected';
            }
            $months[] = array(
                'month' => $i,
                'value' => $i,
                'selected' => $selectde
            );
        }
        //查询所有楼盘
        $properMod = D('property');
        $propertys = $properMod->getPropertys($this->companyID);
        foreach($propertys as &$pro){
            if($pro['id'] == $condition['property']){
                $pro['selected'] = 'selected';
            }
        }
        //停车卡号
        if(!empty($condition) || !empty($condition['property']) || !empty($condition['card'])){
            $carMod = D('Car');
            $card = $carMod->getParkForProperty($condition['property']);
            foreach($card as &$ca){
                if($ca['id'] == $condition['card']){
                    $ca['selected'] = 'selected';
                }
            }
        }

        //查询待缴费账单
        $billsMod = D('Carfee');
        //查询账单
        $bills = $billsMod->selectBill($this->companyID, 1, 0, 10, $condition);
        foreach($bills as &$bill){
            $bill['p_money'] = $bill['p_money']?$bill['p_money']:'0.00';
            $bill['penalty'] = $bill['penalty']?$bill['penalty']:'0.00';
            $bill['total'] = sprintf(" %1\$.2f", $bill['money']-$bill['p_money']+$bill['penalty']);
        }
        //总记录条数
        $count = $billsMod->parkingCount($this->companyID, 1, $condition);
        //发送数据到页面
        $this->assign('years', $years);
        $this->assign('months', $months);
        $this->assign('propertys', $propertys);
        $this->assign('card', $card);
        $this->assign('list', $bills);
        $this->assign('count', $count);
        $this->assign('compid', $this->companyID);
        $this->display();
    }
    public function search(){
        $date = explode('-', date('Y-m'));
        //当前年份
        $currentYear = $date[0];
        $star = I('post.page', 0);
        $compid = I('post.compid', '');
        $condition = array(
            //年份
            'year' => I('post.year', $currentYear),
            //月份
            'month' => I('post.month', ''),
            //楼盘
            'property' => I('post.property', ''),
            //停车卡ID
            'card' => I('post.card', ''),
        );

        //查询待缴费账单
        $billsMod = D('Carfee');
        $bills = $billsMod->selectBill($compid, 1, $star, 10, $condition);
        foreach($bills as &$bill){
            $bill['p_money'] = $bill['p_money']?$bill['p_money']:'0.00';
            $bill['penalty'] = $bill['penalty']?$bill['penalty']:'0.00';
            $bill['total'] = sprintf(" %1\$.2f", $bill['money']-$bill['p_money']+$bill['penalty']);
        }
        exit(json_encode($bills));
    }



}