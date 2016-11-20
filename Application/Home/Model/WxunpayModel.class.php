<?php
/*************************************************
 * 文件名：WxunpayModel.class.php
 * 功能：     微信待缴费模型
 * 日期：     2015.10.14
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class WxunpayModel extends Model
{

    protected $tableName = 'accounts_charges';

    /**
     * 查询某个房间的缴费相关金额
     * @param integer $hmId 房间ID
     * @param string $section 区间 等于号为eq，不等于为neq，大于号为gt，小于号为lt，与status同为空时，则不根据账单类型查询
     * @param number $status 状态 -1-已生成，未出 1-录入优惠(优惠状态) 2-已出账单，未缴费 3-已缴费，与section同为空时，则不根据账单类型查询
     * @return \Think\mixed
     */
    public function getTotalCharges($hmId, $section = '', $status = '')
    {
        $field = ['preferential_money', 'penalty', 'money'];
        $where = [
            'hm_id' => intval($hmId),
            'status' => [$section, $status]
        ];
        $result = $this->field($field)->where($where)->select();
        return $result;
    }

    /*
    * 查询某个车位的缴费相关金额
    */
    public function getCarTotalCharges($carid,$section = '',$status = ''){
        $field = array(
            'preferential_money',
            'penalty',
            'money'
        );
        $where = array(
            'car_id' => intval($carid),
            'status' => array(
                $section,
                $status
            )
        );
        $result = $this->table('fx_carfee_charges')->field($field)
            ->where($where)
            ->select();
        return $result;
    }

    /**
     * 根据用户设置缴费的房产ID查询缴费列表
     * @param string $hmId 房间ID
     * @param string $session 区间 等于号为eq，不等于为neq，大于号为gt，小于号为lt，与status同为空时，则不根据账单类型查询
     * @param string $status 状态 -1-已生成，未发布 1-已发布，为缴费 2-已缴费，与section同为空时，则不根据账单类型查询
     * @param string $year 年份，默认为空
     * @param string $month 月份，默认为空
     * @return unknown
     */
    public function getPayList($hmId, $session = '', $status = '', $year = '', $month = '')
    {
        $field=['id','cm_id','formerly'=>'name','money','preferential_money','penalty','status','year','month'];
        $where = ['hm_id' => $hmId];
        // 账单状态
        if ($session && $status) $where['status'] = [$session, $status];
        // 账单年份
        if ($year) $where['year'] = $year;
        // 账单月份
        if ($month) $where['month'] = $month;
        // 先根据年份再根据月份进行降序排序
        $order = ['year,month' => 'desc'];
        $result = $this->field($field)->where($where)->order($order)->select();
        return $result;
    }

    /**
     * 根据用户设置缴费的车辆ID查询缴费列表
     * @param string $session 区间 等于号为eq，不等于为neq，大于号为gt，小于号为lt，与status同为空时，则不根据账单类型查询
     * @param string $status 状态 -1-已生成，未发布 1-已发布，为缴费 2-已缴费，与section同为空时，则不根据账单类型查询
     * @param string $year 年份，默认为空
     * @param string $month 月份，默认为空
     * @return mixed
     */
    public function getCarPayList($carid, $session = '', $status = '', $year = '', $month = '')
    {
        $field = ['id', 'car_id', 'money', 'preferential_money', 'penalty', 'year', 'month', 'status'];
        $where = ['car_id' => $carid];
        if ($session && $status) $where['status'] = [$session, $status];
        if ($year) $where['year'] = $year;
        if ($month) $where['month'] = $month;
        $order = ['year,month' => 'desc'];
        if ($year && $month) {
            $where['year'] = $year;
            $where['month'] = $month;
        }
        $result = $this->table('fx_carfee_charges')->field($field)->where($where)->order($order)->select();
        return $result;
    }
}


