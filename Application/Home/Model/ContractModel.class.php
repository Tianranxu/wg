<?php
/*************************************************
 * 文件名：ContractModel.class.php
 * 功能：     合约管理模型
 * 日期：     2016.01.25
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;
use Think\Model;

class ContractModel extends Model{

    protected $tableName = 'contract_manage';
    public function getContractList($where){
        $table = [
            'fx_contract_manage' => 'cm',
            'fx_property_pc_user' => 'ppu',
            'fx_customer_source' => 'cs',
            'fx_room_source' => 'rs',
            'fx_house_manage' => 'hm',
            'fx_building_manage' => 'bm',
            'fx_community_comp' => 'cc',
        ];
        $query = [
            'cm.custom_id = cs.id',
            'cm.room_id = rs.id',
            'cs.customer_id = ppu.id',
            'rs.hm_id = hm.id',
            'hm.bm_id = bm.id',
            'bm.cc_id = cc.id',
        ];
        $field = [
            'cm.id', 'cm.name', 'cm.number', 'cm.start_date', 'cm.end_date', 'cm.rent', 'cm.status', 
            'cs.name' => 'cs_name', 'ppu.contact_number', 'hm.number' => 'h_name', 'bm.name' => 'b_name', 'cc.name' => 'cc_name',
        ];
        foreach ($where as $key => $value) {
            if ($key == 'number' || $key == 'name') {
                $query['cm.'.$key] = ['like', '%'. $value .'%'];
            }elseif ($key == 'customer') {
                $query['cs.name'] = ['like', '%'. $value .'%'];
            }elseif ($key == 'start_time' || $key == 'end_time' || $key ==  'date_type') {
                if ($where['date_type'] && $where['start_time'] && $where['end_time'])
                    $query['cm.'.$where['date_type']] = ['between', [date('Y-m-d H:i:s', strtotime($where['start_time'])), date('Y-m-d H:i:s', strtotime($where['end_time'])+86400)]];
            }else{
                $query['cm.'.$key] = $value;
            }
        }
        return $this->table($table)->where($query)->field($field)->order(['cm.sign_date'=>'desc', 'cm.id' => 'desc'])->select();
    }

    //获取单个合同的详情
    public function getContractById($id){
        $table = [
            'fx_contract_manage' => 'cm',
            'fx_customer_source' => 'cs',
            'fx_property_pc_user' => 'ppu',
            'fx_room_source' => 'rs',
            'fx_house_manage' => 'hm',
            'fx_building_manage' => 'bm',
            'fx_community_comp' => 'cc',
        ];
        $where = [
            'cm.id' => $id,
            'cm.custom_id = cs.id',
            'cs.customer_id = ppu.id',
            'cm.room_id = rs.id',
            'rs.hm_id = hm.id',
            'hm.bm_id = bm.id',
            'bm.cc_id = cc.id',
        ];
        $field = [
            'cm.id', 'cm.name', 'cm.start_date', 'cm.end_date', 'cm.cert_number', 'cm.out_accounts_date', 'cm.deposit', 'cm.month', 'cm.days', 'cm.rent', 'cm.cycle', 'cm.number', 'cm.sign_date',
            'cm.status', 'cm.is_increase', 'cm.increase_cycle', 'cm.increase_rent', 'cm.ins_rent_type', 'cm.marketer', 'cm.remark', 'cs.name' => 'customer', 'ppu.contact_number',
            'hm.number' => 'h_name', 'bm.name' => 'b_name', 'cc.name' => 'cc_name',
        ];
        return $this->table($table)->where($where)->field($field)->find();
    }

    public function getPictureById($contract_id){
        return $this->table('fx_contract_picture')->where(['contract_id' => $contract_id])->select();
    }

    //中止合约
    public function stopContract($contract_id){
        $this->startTrans();    //开启事务
        $contractResult = $this->where(['id' => $contract_id])->save(['status' => -2]);
        $billResult = D('Lease')->banBill($contract_id);
        if ($contractResult && $billResult !== false) {
            $this->commit();
            return true;    
        }else{
            $this->rollback();
            return false;
        }
    }

    /**
     * 检查合同编号是否重复
     * @param int $cmId 企业ID
     * @param int $number 合同编号
     * @return mixed
     */
    public function checkNumberExists($cmId, $number)
    {
        $where = ['cm_id' => $cmId, 'number' => $number];
        $result = $this->where($where)->find();
        return $result;
    }

    /**
     * 新建合约
     * @param array $datas 合约数据
     * @return bool
     */
    public function addContract(array $datas)
    {
        $pictures = $datas['pictures'];
        unset($datas['pictures']);
        $this->startTrans();
        $result = $this->add($datas);
        if (!$result) {
            $this->rollback();
            return false;
        }
        //添加附件
        if (!empty($pictures)) {
            $picDatas = [];
            foreach ($pictures as $p => $picture) {
                $picDatas[$p]['contract_id'] = $result;
                $picDatas[$p]['pic_url'] = $picture;
            }
            $contractpicModel = D('contractpic');
            $picResult = $contractpicModel->addPictures($picDatas);
            if (!$picResult) {
                $this->rollback();
                return false;
            }
        }
        //将房源和客源更新为已签约/已租状态
        $roomModel = D('room');
        $roomResult = $roomModel->saveRoom($datas['cm_id'], $datas['room_id'], ['status' => 2, 'update_time' => date('Y-m-d H:i:s')]);
        if (!$roomResult) {
            $this->rollback();
            return false;
        }
        $customerSourceModel = D('customersource');
        $customerSourceResult = $customerSourceModel->saveCustomer(['id' => $datas['custom_id'], 'status' => 2]);
        if (!$customerSourceResult) {
            $this->rollback();
            return false;
        }
        $this->commit();
        return $result;
    }
    //根据企业和是否过期查询所有合约
    public function getCntractForStatus($compid, $expire=-1){

        return $this->where('expire=%d and cm_id=%d and status<>-2 or status=%d', $expire, $compid, $expire)
            ->getField('id,start_date as start_time,end_date as end_time',true);
       // echo $this->getLastSql();exit;
    }
}