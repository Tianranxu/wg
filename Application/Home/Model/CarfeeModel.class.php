<?php
/*************************************************
 * 文件名：CarfeeModel.class.php
 * 功能：     车位账单模型
 * 日期：     2016.1.8
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class CarfeeModel extends Model{

    protected $tableName='carfee_charges';

    public function getPayAccounts($cmId, array $carIds, $status=1)
    {
        $field=['f.id','f.cm_id','f.cc_id','c.id'=>'car_id','c.card_number','c.car_number','f.number','f.money','f.preferential_money','f.penalty','f.status','f.year','f.month'];
        $where=[
            'f.cm_id'=>$cmId,
            'f.car_id'=>['in',$carIds],
            'f.status'=>$status,
            'c.id=f.car_id',
        ];
        $table=[
            'fx_carfee_charges'=>'f',
            'fx_car_manage'=>'c'
        ];
        $order='month,year desc';
        $result=$this->table($table)->field($field)->where($where)->order($order)->select();
        return $result;
    }
    /**
     * 根据公司查询账单
     * @param $compid    公司ID
     * @param $star      记录开始
     * @param $end       多少条
     * @param $status    账单状态（-1生成未发布，1发布未缴费）
     * @param array $data    查询条件数组
     */
    public function selectBill($compid, $status=-1, $star=0, $end=10, $data=array()){
        $table = array(
            'fx_carfee_charges' => 'cfee'
        );
        $field = array(
            'cfee.id',
            'cfee.car_id',
            'cfee.cm_id',
            'cfee.year',
            'cfee.month',
            'cfee.money',
            'cfee.preferential_money' => 'p_money',
            'cfee.penalty',
            'car.card_number' => 'number',
            'car.car_number' => 'plate',
            'cc.name' => 'cname'
        );
        $where = array(
            'cfee.cm_id' => $compid,
            'cfee.status' => $status
        );
        if(!empty($data)){
            $year = array();
            $month = array();
            $ccid = array();
            $card = array();
            if(!empty($data['property'])){
                $ccid = array(
                    'cfee.cc_id' => array('in', $data['property']),
                );
            }
            if(!empty($data['year'])){
                $year = array(
                    'cfee.year' => $data['year'],
                );
            }
            if(!empty($data['month'])){
                $month = array(
                    'cfee.month' => $data['month']
                );
            }
            if(!empty($data['card'])){
                $card = array(
                    'car_id' => $data['card'],
                );
            }
            $where = array_merge($where, $year, $month, $ccid, $card);
        }

        return $this->table($table)
            ->field($field)
            ->where($where)
            ->join('`fx_community_comp` AS `cc` ON cfee.cc_id=cc.id','LEFT')
            ->join('`fx_car_manage` AS `car` ON cfee.car_id=car.id','LEFT')
            ->limit($star, $end)
            ->select();
       // echo $this->getLastSql();exit;

    }
    /**
     * 根据公司查询账单总数
     * @param $compid    公司ID
     * @param $status    账单状态
     * @param array $data    查询条件数组
     */

    public function parkingCount($compid, $status=-1, $data=array()){
        $where = array(
            'cm_id' => $compid,
            'status' => $status
        );
        if(!empty($data)){
            $year = array();
            $month = array();
            $card = array();
            $ccid = array();
            if(!empty($data['year'])){
                $year = array(
                    'year' => $data['year'],
                );
            }
            if(!empty($data['property'])){
                $ccid = array(
                    'cc_id' => array('in', $data['property']),
                );
            }
            if(!empty($data['month'])){
                $month = array(
                    'month' => $data['month']
                );
            }
            if(!empty($data['card'])){
                $card = array(
                    'car_id' => $data['card'],
                );
            }
            $where = array_merge($where, $year, $month, $card, $ccid);
        }
        return $this->where($where)
            ->count();
        echo $this->getLastSql();exit;

    }
    /**
     * 发布账单
     * @param $compid    公司ID
     */
    public function publishBill($compid)
    {
        return $this->where('cm_id=%d and status=-1', $compid)
            ->setField('status', 1);
    }
    /**
     * 更新账单状态
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

    //获取车辆账单的详细信息
    public function getCarBills($ac_ids){
        return $this->table('fx_carfee_charges')->where(['id' => ['IN', $ac_ids]])->select();
    }
}