<?php
/*************************************************
 * 文件名：LeaseModel.class.php
 * 功能：    租赁账单模型
 * 日期：     2015.1.18
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

class LeaseModel extends WeixinModel
{
    protected $trueTableName = 'fx_lease_bill';

    /**查询企业下租赁账单
     * @param int $compid         企业id
     * @param int $type           账单类型
     * @param int $start          开始记录
     * @param int $end            一次多少条
     * @param array $condition    查询条件
     * @param $switch             查询开关 1查询详情 2查询总数
     */
    public function selectLeaseBill($compid, $condition='', $switch=1, $type=1, $start=0, $end=10){
        //状态条件
        if($type == 1){
            $exp = ['neq', 2];
        }else{
            $exp = ['eq', $type];
        };
        if(empty($condition)){
            $where = array(
                'cm_id' => $compid,
                'status' => $exp,
            );
        }else{
            if($condition['warning'] == 1) {
                $exp = ['eq', -1];
            }
            $where = array(
                'cm_id' => $compid,
                'status' => $exp,
            );
            $date = array();
            $ccid = array();
            $bmid = array();
            $hmid = array();
            $payer = array();
            $contract = array();
            if(!empty($condition['start_date'])){
                $date = array(
                    'payment' => array('between', "{$condition['start_date']}, {$condition['end_date']}"),
                );
            }
            if(!empty($condition['property'])){
                $ccid = array(
                    'cc_id' => $condition['property'],
                );
            }
            if(!empty($condition['build'])){
                $bmid = array(
                    'bm_id' =>  $condition['build'],
                );
            }
            if(!empty($condition['house'])){
                $hmid = array(
                    'hm_id' => $condition['house']
                );
            }
            if(!empty($condition['payer'])){
                $payer = array(
                    'payer' => array('like', '%'.$condition['payer'].'%'),
                );
            }
            if(!empty($condition['contract'])){
                $contract = array(
                    'contract_id' => $condition['contract']
                );
            }

            $where = array_merge($where, $date, $ccid, $bmid, $hmid, $payer, $contract);
        }
        if($switch == 1){
           return $this->where($where)
                ->where('status<>%d', -2)
                ->limit($start, $end)
                ->select();
            //echo $this->getLastSql();exit;
        }elseif($switch == 2){
            return $this->where($where)
                ->where('status<>%d', -2)
                ->count();
            //echo $this->getLastSql();exit;
        }
    }
    //获取账单
    public function getPayAccounts($cmId, $hm_id, $status=1)
    {
        //状态条件
        if($status == 1){
            $exp = ['neq', 2];
        }else{
            $exp = ['eq', $status];
        };

        $field = 'id,number,payment,property,payer,contact,cm_id,cc_id,bm_id,hm_id,contract_id,
                  discount as preferential_money,
                  delaying as penalty,
                  money,status,create_time,year,month';
        $where=[
            'cm_id' => $cmId,
            'hm_id' => $hm_id,
            'status' => $exp
        ];
        $order='payment';
        $result=$this->where($where)
            ->where('status<>-2')
            ->field($field)
            ->order($order)
            ->select();
        return $result;
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

    //中止合约后，将对应账单修改为中止状态
    public function banBill($contract_id){
        return $this->where(['contract_id' => $contract_id, 'status' => ['neq', 2]])->save(['status' => -2]);
    }

    public function getBills($ids){
        return $this->where(['id' => ['IN', $ids]])->select();
    }

    //获取租赁账单的详细信息
    public function getLeaseBills($ac_ids){
        $result = $this->where(['id' => ['IN', $ac_ids]])->select();
        $data = [];
        foreach($result as $r){
            $data[]= [
                'preferential_money' => $r['discount'],
                'penalty' => $r['delaying'],
                'id' => $r['id'],
                'year' => $r['year'],
                'month' => $r['month'],
                'number' => $r['number'],
                'money' =>$r['money'],
                'description'=> '',
                'ch_name' => '  ',
                'contract_id' => $r['contract_id'],
            ];
        }
        return $data;
    }













}