<?php
/*************************************************
 * 文件名：ChargeitemsController.class.php
 * 功能：     收费项目管理控制器
 * 日期：     2015.12.29
 * 作者：     XU
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/

namespace Home\Controller;

class ChargeitemsController extends AccessController {
    protected $charge_map = [
        'nm' => 'number',
        'name' => 'name',
        'bl' => 'billing',
        'ms' => 'measure_style',
        'cc' => 'charging_cycle',
        'compid' => 'cm_id',
        'cc_id' => 'cc_id',
        's_date' => 'start_date',
        'e_date' => 'end_date',
        'chid' => 'ch_id',
    ];

    protected $propertyModel;

    protected $buildingModel;

    protected $houseChargesModel;

    protected $houseBindRecModel;

    public function _initialize(){
        header("Content-Type:text/html;charset=utf-8");
        parent::_initialize();
        $this->propertyModel=D('property');
    }

    public function getWhere(){
        foreach (I('get.') as $key => $value) {
            if(array_key_exists($key, $this->charge_map) && $value){
                $where[$this->charge_map[$key]] = $value;
            }
        }
        $where['page'] = I('get.page',1);
        $where['limit'] = I('get.limit',10);
        return $where;
    }

    //费项管理页面
    public function index(){
        $where = $this->getWhere();
        $flag = I('get.flag','');
        $chargeModel = D('Charge');
        $data = $chargeModel->getChargesList($where);
        $meter = D('Meter')->getAllSetMeter();
        $js_meter = json_encode($meter);
        foreach ($meter as $k => $v) {
            $meter[$v['id']] = $v;
        }
        foreach ($data['data'] as $key => $value) {
            if($value['billing']){
                $billing = intval($value['billing']);
                $ms = intval($value['measure_style']);
                $data['data'][$key]['billing'] = C("CHARGES.$billing");
                if ($value['billing'] == 4) {
                    $data['data'][$key]['measure_style'] = '定额';
                }else{
                    $data['data'][$key]['measure_style'] = ($value['billing'] == 1) ? C("CHARGES.$ms") : $meter[$ms]['name'];
                }
            }
        }
        for ($i=1; $i <= 32; $i++) { 
            $number[] = $i;
            if($i <= 12){
                $cycle[] = "$i" . '月';
            }
        }
        if ($flag) {
            foreach ($data['data'] as $key => $item) {
                foreach ($item as $k => $value) {
                    if($value == null){
                        $data['data'][$key][$k] = '';
                    }
                }
            }
            retMessage(true, $data);
            exit;
        }
        $name = $chargeModel->getNameByCompid($where['cm_id']);
        $this->assign('where', $where);  //供搜索栏使用
        $this->assign('compid', $where['cm_id']);
        $this->assign('meter',$js_meter);
        $this->assign('name', $name);
        $this->assign('cycle', $cycle);
        $this->assign('number', $number);
        $this->assign('data', $data);
        $this->display();
    }

    //清除费项
    public function clearItem(){
        $id = I('post.id');
        $compid = I('post.compid');
        $propertyCommon=A('propertycommon');
        $hmIds = $propertyCommon->getPropertyIds($compid, 'company');
        $result = D('Charge')->clearItem($id, $hmIds);
        if ($result) {
            retMessage(true,$result);
        }else{
            retMessage(false,null);
        }
    }

    //编辑费项页面
    public function editItem(){
        $compid = I('get.compid', '');
        $id = I('get.chid');
        $chargeModel = D('Charge');
        $data = $chargeModel->getChargeItem($id);
        $meters = D('Meter')->getAllSetMeter();
        $billing = array(
            '1' => C("CHARGES.1"),
            '2' => C("CHARGES.2"),
            '4' => C("CHARGES.4"),
        );
        if ($data['billing'] == 1) {
            $measure = array(
                '-5' => C("CHARGES.-5"),
                '-6' => C("CHARGES.-6"),
                '-7' => C("CHARGES.-7"),
            );  
        }elseif ($data['billing'] == 2) {
            foreach ($meters as $meter) {
                $measure[$meter['id']] = $meter['name'];
            }
        }elseif ($data['billing'] == 4) {
            $measure = array(
                '' => '定额');
        }else{
            $measure = array(
                '' => '全部');
        }
        $cycle = explode(',', $data['charging_cycle']);
        for ($i=1; $i <= 12; $i++) {
            if(in_array($i, $cycle)) {
                $c_cycle[$i]['month'] = $i;
                $c_cycle[$i]['status'] = 1;
                continue;
            }
            $c_cycle[$i]['month'] = $i;
            $c_cycle[$i]['status'] = -1;
        }
        $this->assign('compid', $compid);
        $this->assign('cycle', $c_cycle);
        $this->assign('meter', json_encode($meters));
        $this->assign('billing', $billing);
        $this->assign('measure', $measure);
        $this->assign('data', $data);
        $this->display();
    }

    //保存费项信息
    public function doEdit(){
        $id = I('post.id');
        $data = array(
            'name' => I('post.name'),
            'billing' => I('post.billing'),
            'measure_style' => I('post.measure'),
            'price' => I('post.price'),
            'charging_cycle' => implode(',',I('post.charging_cycle')),
            'modify_time' => date('Y-m-d H:i:s'),
            'remark' => I('post.remark'),
        );
        $result = D('Charge')->saveItems($id,$data);
        $result ? retMessage(true,$result) : retMessage(false,null,'','',4001);
    }

    //房间的收费管理页面
    public function roomItems(){
        $compid = I('get.compid');
        $hmid = I('get.hmid');
        $bindItems = D('Housecharges')->getBindItems($hmid);
        $items =  D('Charge')->getChargesList(array(
            'cm_id' => $compid,
            'page' => 1,
            'limit' => 32,
        ))['data'];
        foreach ($items as $key => $item) {
            $c_items[$item['id']] = $item;
            $c_items[$item['id']]['bind_status'] = -1; 
        }
        foreach ($bindItems as $binditem) {
            $c_items[$binditem['ch_id']]['bind_status'] = 1;
        }
        foreach ($c_items as $key => $value) {
            if($value['billing']){
                $billing = intval($value['billing']);
                $ms = intval($value['measure_style']);
                $c_items[$key]['billing'] = C("CHARGES.$billing");
                $c_items[$key]['measure_style'] = ($value['billing'] == 4) ? '定额' : C("CHARGES.$ms");    
            }
        }
        $belong = D('property')->getPropertyBelog($hmid, 'House');
        $this->assign('cc_id', $belong['cc_id']);
        $this->assign('bm_id', $belong['bm_id']);
        $this->assign('compid', $compid);
        $this->assign('hmid', $hmid);
        $this->assign('items', $c_items);
        $this->display();
    }

    //保存房间编辑的收费信息
    public function saveRoomItems(){
        $items = I('post.items');
        $hmid = I('post.hmid');
        $compid = I('post.compid');
        $bm_id = I('post.bm_id');
        $cc_id = I('post.cc_id');
        $hChargeModel = D('Housecharges');
        //获取修改前的费项以作对比
        $bindItems = $hChargeModel->getBindItems($hmid);
        $banItems = $hChargeModel->getBanItems($hmid);
        foreach ($items as $key => $item) {
            foreach ($bindItems as $k => $binditem) {
                if ($item == $binditem['ch_id']){
                    unset($items[$key],$bindItems[$k]);
                }
            }
            foreach ($banItems as $k => $banitem) {
                if ($banitem['ch_id'] == $item){
                    $saveItems[] = $item;
                    unset($items[$key]);
                }
            }
        }
        foreach ($items as $key => $item) {
            $adds[] = array(
                'ch_id' => $item,
                'hm_id' => $hmid,
                'cm_id' => $compid,
                'update_time' => date('Y-m-d H:i:s')
            );
        }
        $result = $hChargeModel->saveItems($adds, $bindItems, $saveItems, $hmid);
        $result ? retMessage(true, $result) : retMessage(false, $result);
    }

    //绑定记录页面
    public function bindRecord(){
        $where = $this->getWhere();
        $chargeModel = D('Charge');
        $flag = I('get.flag','');
        $data = $chargeModel->getBindRecord($where);
        for ($i=1; $i <= 32; $i++) { 
            $number[] = $i;
        }
        $community = D('property')->getCommunityByCompid($where['cm_id']);
        $this->assign('number', $number);
        $this->assign('community', $community);
        $this->assign('data', $data);
        $this->assign('compid', $where['cm_id']);
        $this->assign('where', $where);
        if ($flag) {
            retMessage(true,$data);
            exit;
        }
        $this->display();
    }

    /**
     * 房产收费绑定页面
     */
    public function bind()
    {
        $isAjax=I('isAjax','');
        $compid=I('request.compid','');
        $chId=I('request.chid','');
        $page=I('post.page',1);
        //查询该企业下的楼盘列表
        $this->propertyModel=D('property');
        $ccLists=$this->propertyModel->getCommunityByCompid($this->companyID);
        //查询房间收费绑定记录
        $chargeModel=D('charge');
        $recordResult=$chargeModel->getBindRecord(['cm_id'=>$compid,'ch_id'=>$chId,'page'=>$page,'limit'=>10]);
        if($isAjax){
            if(!$compid || !$chId){
                retMessage(false,null,'参数错误，请检查参数','参数错误，请检查参数',4001);
                exit;
            }
            retMessage(true,$recordResult);
            exit;
        }

        $this->assign('ccLists',$ccLists);
        $this->assign('uid',$this->userID);
        $this->assign('recordResult',$recordResult);
        $this->display();
    }

    /**
     * 执行绑定
     */
    public function dobind()
    {
        //接收数据
        $compid=I('post.compid','');
        $id=I('post.id','');
        $type=I('post.type','');
        $bindType=(I('post.bindType','bind')=='bind')?1:-1;
        $chId=I('post.chId','');
        $uid=I('post.uid','');
        if(!$compid || !$id || !$type || !$bindType || !$chId || !$uid){
            retMessage(false,null,'参数错误，请检查参数','参数错误，请检查参数',4001);
            exit;
        }
        $propertyCommon=A('propertycommon');
        $hmIds=$propertyCommon->getPropertyIds($id,$type);
        if(!$hmIds){
            retMessage(false,null,'获取房间失败！','获取房间失败！',4002);
            exit;
        }
        //绑定/解绑
        $this->houseChargesModel=D('housecharges');
        $bindResult=$this->houseChargesModel->doHouseCharges($compid,$hmIds,$chId,$bindType);
        if(!$bindResult){
            retMessage(false,null,'绑定失败！','绑定失败！',4003);
            exit;
        }
        //写入绑定记录
        $address='';
        $isCompany=true;
        if($type!='company') {
            $address=$propertyCommon->getPorpertyAddress($id,$type);
            $isCompany=false;
        }
        //获取费项名称并写入记录
        $name = D('charge')->getChargeItem($chId)['name'];
        $this->houseBindRecModel=D('housebindrec');
        $recordResult=$this->houseBindRecModel->recordHouseBind($compid,$chId,$uid,$bindType,$address,$isCompany,$id, $name);
        retMessage(true,null);
        exit;
    }
}

