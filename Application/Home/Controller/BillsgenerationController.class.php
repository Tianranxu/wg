<?php
/*************************************************
 * 文件名：BillsgenerationController.class.php
 * 功能：     生成账单控制器
 * 日期：     2015.12.29
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
use Org\Util\RabbitMQ;
class BillsgenerationController extends AccessController{
    public function _initialize() {
        header("Content-Type:text/html;charset=utf-8");
        parent::_initialize();
    }

    public function index(){
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
        //所有费项
        $chargesMod = D('charge');
        $charges = $chargesMod->getChargesToCmopid($this->companyID);
        //查询已生成但未发布的账单
        $billsMod = D('Accounts');
        $star = $delPage != ''?($delPage-1)*10:0;
        $bills = $billsMod->notReleasedBills($this->companyID, -1, $star);
        $billist = $this->tableData($bills);
        //总记录条数
        $count = $billsMod->billsCount($this->companyID);
        $this->assign('years', $years);
        $this->assign('months', $months);
        $this->assign('propertys', $propertys);
        $this->assign('charges', $charges);
        $this->assign('list', $billist);
        $this->assign('count', $count);
        $this->assign('compid', $this->companyID);
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
    //生成账单
    public function generation(){
        //{year:year,month:month,property:property,build:build,house:house,charges:charges}
        $compid = I('post.compid', '');
        $year = I('post.year', '');
        $month = I('post.month', '');
        $property = I('post.property', '');
        $build = I('post.build', '');
        $house = I('post.house', '');
        $charges = I('post.charges', '');
        $data = array(
            'compid' => $compid,
            'year' => $year,
            'month' => $month,
            'property' => $property,
            'build' => $build,
            'house' => $house,
            'charges' => $charges
        );
        //推送到队列中
        $queue = 'bills_generation_queue';
        RabbitMQ::publish($queue, json_encode($data));
        exit('任务已提交后台处理<br/>3秒后将自动刷新页面');
    }
    //数据分页
    public function page(){
        $compid = I('post.compid', '');
        $star = I('post.page', 0);
        $billsMod = D('Accounts');
        $bills = $billsMod->notReleasedBills($compid, -1, $star);
        $billist = $this->tableData($bills);
        exit(json_encode($billist));
    }
    //删除账单
    public function delete(){
        $billID = I('post.id', '');
        $billsMod = D('Accounts');
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
        $billsMod = D('Accounts');
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
        $billsMod = D('Accounts');
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
                //楼栋
                'build' => I('get.build', ''),
                //房间
                'house' => I('get.house', ''),
                //费项
                'charges' => I('get.charges', ''),
            );
        }else{
            $condition = array();
        }
        //实例化房产
        $houseMod = D('house');
        //实例化楼栋
        $buildMod = D('building');
        //查出楼栋
        if(!empty($condition['property']) && $condition['property'] != ''){
            $searchBuild = $buildMod->selectAllBuild($condition['property'], 0, 0, 1);
            foreach($searchBuild as &$bu){
                if($bu['id'] == $condition['build']){
                    $bu['selected'] = 'selected';
                }
            }
        }else{
            $searchBuild = '';
        }
        //查出房间
        if(!empty($condition['build']) && $condition['build'] != ''){
            $searchHouse = $houseMod->selectAllHouse($condition['build']);
            foreach($searchHouse as &$ho){
                if($ho['id'] == $condition['house']){
                    $ho['selected'] = 'selected';
                }
            }
        }else{
            $searchHouse = '';
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
        //所有费项
        $chargesMod = D('charge');
        $charges = $chargesMod->getChargesToCmopid($this->companyID);
        foreach($charges as &$ch){
            if($ch['id'] == $condition['charges']){
                $ch['selected'] = 'selected';
            }
        }
        //查询待缴费账单
        $billsMod = D('Accounts');
        //查出所有房间ID
        if(!empty($condition)){
            $houses = $houseMod->houseForCondition($condition['property'], $condition['build'], $condition['house'], $this->companyID);
            $hmid = '';
            foreach($houses as $ho){
                $hmid .= $ho['id'].',';
            }
            $hmid = rtrim($hmid, ',');

            //组成搜索条件
            $condition += array(
                'hmid' => $hmid,
            );
        };
        //查询账单
        $bills = $billsMod->notReleasedBills($this->companyID, 1, 0, 10, $condition);
        //组成页面数据
        $billist = $this->tableData($bills);
        //总记录条数
        $count = $billsMod->billsCount($this->companyID, 1, $condition);
        //发送数据到页面
        $this->assign('searchBuild', $searchBuild);
        $this->assign('searchHouse', $searchHouse);
        $this->assign('years', $years);
        $this->assign('months', $months);
        $this->assign('propertys', $propertys);
        $this->assign('charges', $charges);
        $this->assign('list', $billist);
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
            //楼栋
            'build' => I('post.build', ''),
            //房间
            'house' => I('post.house', ''),
            //费项
            'charges' => I('post.charges', ''),
        );
        //查出所有房间ID
        $houseMod = D('house');
        $houses = $houseMod->houseForCondition(
            $condition['property'],
            $condition['build'],
            $condition['house'],
            $this->companyID
        );
        $hmid = '';
        foreach($houses as $ho){
            $hmid .= $ho['id'].',';
        }
        $hmid = rtrim($hmid, ',');

        //组成搜索条件
        $condition += array(
            'hmid' => $hmid,
        );
        //查询待缴费账单
        $billsMod = D('Accounts');
        $bills = $billsMod->notReleasedBills($compid, 1, $star, 10, $condition);
        //组成页面数据
        $billist = $this->tableData($bills);
        exit(json_encode($billist));
    }
    //组装页面表格数据
    /*
     *  @param array $data    数据库查询出的数据
     */
    protected function tableData($data){
        foreach($data as $bill){
            $billist[] = array(
                //账期
                'date' => $bill['year'].'年'.$bill['month'].'月',
                //房间ID
                'hm_id' => $bill['hm_id'],
                //公司id
                'cm_id' => $bill['cm_id'],
                //费项ID
                'ch_id' => $bill['ch_id'],
                //账单ID
                'id' => $bill['id'],
                //房产名称
                'property' => $bill['cname'].$bill[bname].$bill['house'],
                //费项
                'charge' => $bill['formerly'],
                //金额
                'money' => $bill['money'],
                //优惠
                'discount' => $bill['p_money']?$bill['p_money']:'0.00',
                //滞纳金
                'late' => $bill['penalty']?$bill['penalty']:'0.00',
                //小计
                'subtotal' => sprintf(" %1\$.2f",$bill['money']-$bill['p_money']+$bill['penalty'])
            );
        }
        return $billist;
    }

}