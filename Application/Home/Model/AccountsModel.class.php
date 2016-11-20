<?php
/*************************************************
 * 文件名：AccountsModel.class.php
 * 功能：     账单模型
 * 日期：     2016.1.5
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class AccountsModel extends Model{

    protected $tableName='accounts_charges';

    /**
     * 根据企业ID和房间ID集获取账单列表
     * @param int $cmId
     * @param array $hmIds
     * @param int $status
     * @return mixed
     */
    public function getPayAccounts($cmId, array $hmIds, $status = 1)
    {
        $field=['id','cm_id','hm_id','number','money','preferential_money','penalty','year','month','formerly'=>'ch_name'];
        $where = [
            'cm_id' => $cmId,
            'hm_id' => ['in', $hmIds],
            'status' => $status,
        ];
        $order = 'month,year desc';
        $result = $this->field($field)->where($where)->order($order)->select();
        return $result;
    }

    /**
     * 根据公司查询未发布账单
     * @param $compid    公司ID
     * @param $star      记录开始
     * @param $end       多少条
     * @param $status    账单状态（-1生成未发布，1发布未缴费）
     * @param array $data    查询条件数组
     */

    public function notReleasedBills($compid, $status=-1, $star=0, $end=10, $data=array()){

        $table = array(
            'fx_accounts_charges' => 'ac'
        );
        $field = array(
            'ac.id',
            'ac.hm_id',
            'ac.cm_id',
            'ac.year',
            'ac.month',
            'ac.ch_id',
            'ac.money',
            'ac.preferential_money' => 'p_money',
            'ac.penalty',
            'bm.name' => 'bname',
            'hm.number' => 'house',
            'cm.name' => 'charge',
            'cc.name' => 'cname',
            'ac.formerly'
        );
        $where = array(
            'ac.cm_id' => $compid,
            'ac.status' => $status
        );
        if(!empty($data)){
            $year = array();
            $month = array();
            $charge = array();
            $hmid = array();
            if(!empty($data['hmid'])){
                $hmid = array(
                    'ac.hm_id' => array('in', $data['hmid']),
                );
            }
            if(!empty($data['year'])){
                $year = array(
                    'ac.year' => $data['year'],
                );
            }
            if(!empty($data['month'])){
                $month = array(
                    'ac.month' => $data['month']
                );
            }
            if(!empty($data['charges'])){
                $charge = array(
                    'ac.ch_id' => $data['charges'],
                );
            }
            $where = array_merge($where, $year, $month, $charge, $hmid);

        }
        //dump($where);exit;
        return $this->table($table)
            ->field($field)
            ->where($where)
            ->join('`fx_house_manage` AS `hm` ON ac.hm_id=hm.id','LEFT')
            ->join('`fx_building_manage` AS `bm` ON hm.bm_id=bm.id','LEFT')
            ->join('`fx_charges_manage` AS `cm` ON ac.ch_id=cm.id','LEFT')
            ->join('`fx_community_comp` AS `cc` ON bm.cc_id=cc.id','LEFT')
            ->limit($star, $end)
            ->select();
         //echo $this->getLastSql();exit;
    }
    /**
     * 根据公司查询未发布账单总数
     * @param $compid    公司ID
     * @param $status    账单状态
     * @param array $data    查询条件数组
     */

    public function billsCount($compid, $status=-1, $data=array()){
        $where = array(
            'cm_id' => $compid,
            'status' => $status
        );
        if(!empty($data)){
            $year = array();
            $month = array();
            $charge = array();
            $hmid = array();
            if(!empty($data['hmid'])){
                $hmid = array(
                    'hm_id' => array('in', $data['hmid']),
                );
            }
            if(!empty($data['month'])){
                $month = array(
                    'month' => $data['month']
                );
            }
            if(!empty($data['year'])){
                $year = array(
                    'year' => $data['year'],
                );
            }
            if(!empty($data['charges'])){
                $charge = array(
                    'ch_id' => $data['charges'],
                );
            }
            $where = array_merge($where, $year, $month, $charge, $hmid);
        }
        return $this->where($where)
            ->count();
        //echo $this->getLastSql();exit;
    }
    /**
     * 发布账单
     * @param $compid    公司ID
     */
    public function publishBill($compid){
        return $this->where('cm_id=%d and status=-1', $compid)
            ->setField('status', 1);
    }

    //根据订单（order）中的ac_ids获取其账单的详细信息
    public function getBills($ac_ids){
        $where = [
            'id' => ['IN', $ac_ids],
        ];
        $field = [
            'formerly' => 'ch_name','id','year','month','money','preferential_money','penalty','description','number','status',
        ];
        return $this->table($table)->where($where)->field($field)->select();
    }
    
    /**
     * 批量更新账单状态
     * @param array $ids 账单ID集
     * @param int $status 账单状态
     * @return bool
     */
    public function updateAccounts(array $ids, $status)
    {
        $data = ['status' => $status];
        $where = ['id' => ['in', $ids]];
        $result = $this->where($where)->save($data);
        if (!$result) return false;
        return true;
    }
    //保存费项原名脚本
    public function copyChargesName(){
        $table = ['fx_charges_manage' => 'cm'];
        $charges = $this->table($table)->getField('id,name');
        $sql_1 = 'UPDATE fx_accounts_charges SET formerly = CASE ch_id';
        $sql_2 = '';
        $ids = '';
        $sql_3 = ' END WHERE id IN (';
        $accounts = $this->getField('id,ch_id');
        foreach($accounts as $key=>$ac){
            $sql_2 .= " WHEN {$ac} THEN '{$charges[$ac]}'";
            $ids .= $key.',';
        }
        $ids = rtrim($ids, ',');
        $sql = $sql_1.$sql_2.$sql_3.$ids.')';
        $result = $this->execute($sql);
        $result = $result===0?true:$result;
        return $result;
    }
}