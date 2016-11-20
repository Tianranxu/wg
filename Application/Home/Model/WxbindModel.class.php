<?php
/*************************************************
 * 文件名：WxbindModel.class.php
 * 功能：     微信缴费房产/车辆模型
 * 日期：     2015.10.14
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class WxbindModel extends Model
{

    protected $tableName = 'bind_temp';

    /**
     * 获取用户绑定的房产/车辆列表
     *
     * @param string $openid
     *            用户的标识，对当前公众号唯一
     * @param number $type
     *            绑定类型 1-房产绑定 2-车辆绑定，默认1
     * @return unknown
     */
    public function getHouseBindList($openid, $type = 1)
    {
        $field = ['id','openid','type','hm_id','car_id','is_pay'];
        $where = ['openid' => $openid,'type' => $type];
        // 查询用户房产绑定列表
        $bindList = $this->field($field)->where($where)->select();
        // 房产绑定额外信息
        if ($type == 1) {
            // 取出所有房间ID
            $getHmIds = function ($bindValue){return $bindValue['hm_id'];};
            $hmIds = array_map($getHmIds, $bindList);
            // 查询绑定的房产所属信息
            $propertyModel = D('property');
            $houseBelong = array_values($propertyModel->get_house_belong($hmIds));
            // 重组数组
            foreach ($bindList as $bk => $bv) {
                foreach ($houseBelong as $hv) {
                    if ($bv['hm_id'] == $hv['id']) {
                        $bindList[$bk]['houseInfo'] = $hv;
                    }
                }
            }
            return $bindList;
        }
        // 车辆绑定额外信息
        if ($type == 2) {
            // 取出所有车辆ID
            $getCarIds = function ($bindValue){return $bindValue['car_id'];};
            $carIds = array_map($getCarIds, $bindList);
            // 查询车辆所属的信息
            $carModel = D('car');
            $carBelong = $carModel->getCarBelong($carIds);
            // 重组数组
            foreach ($bindList as $bk => $bv) {
                foreach ($carBelong as $cv) {
                    if ($bv['car_id'] == $cv['id']) {
                        $bindList[$bk]['carInfo'] = $cv;
                    }
                }
            }
            return $bindList;
        }
    }

    /**
     * 获取用户当前用户激活的绑定房产/车辆
     * @param string $openid 用户的标识，对当前公众号唯一
     * @param number $type 绑定类型 1-房产绑定 2-车辆绑定，默认1
     * @return unknown
     */
    public function getCurrentActiveItem($openid, $type = 1)
    {
        if ($type == 1) {
            $field = ['bt.hm_id', 'hm.number', 'bm.name' => 'build_name', 'cc.name' => 'community_name'];
            $tables = ['fx_bind_temp' => 'bt', 'fx_house_manage' => 'hm', 'fx_building_manage' => 'bm', 'fx_community_comp' => 'cc'];
            $condi = 'bt.hm_id = hm.id AND hm.bm_id=bm.id AND bm.cc_id=cc.id';
        } else {
            $field = ['cm.id', 'cm.car_number', 'cm.card_number', 'cc.name' => 'cname'];
            $tables = ['fx_bind_temp' => 'bt', 'fx_car_manage' => 'cm', 'fx_community_comp' => 'cc'];
            $condi = 'bt.car_id = cm.id AND cm.cc_id = cc.id';
        }
        $where = [
            'openid' => $openid,
            'is_pay' => 1,
            'type' => $type
        ];
        // 查询用户当前激活的绑定房产
        $binding = $this->table($tables)->field($field)->where($where)->where($condi)->find();
        return $binding;
    }

    /**
     * 绑定房产/车辆
     *
     * @param string $openid
     *            用户的标识，对当前公众号唯一
     * @param string $type
     *            绑定类型 1-房产绑定 2-车辆绑定，默认1
     * @param string $id
     *            房间ID/车辆管理主键ID
     * @param string $isPay
     *            是否为首次绑定
     * @return boolean
     */
    public function bindOn($openid, $type, $id, $isPay = '-1')
    {
        // 组装添加数据
        $data = ['openid' => $openid,'type' => $type,'create_time' => date('Y-m-d H:i:s')];
        if ($type == 1) $data['hm_id'] = $id;
        if ($type == 2) $data['car_id'] = $id;
        // 是否为首次绑定
        if ($isPay == 1) $data['is_pay'] = $isPay;
        $this->startTrans();
        $result = $this->add($data);
        if (! $result) {
            $this->rollback();
            return false;
        }
        // 将缴费财产存入session
        if ($isPay == 1)
            session($openid . 'payhouse', $id);
        $this->commit();
        return true;
    }

    /**
     * 解除绑定房产/车辆
     *
     * @param string $openid
     *            用户的标识，对当前公众号唯一
     * @param string $type
     *            绑定类型 1-房产绑定 2-车辆绑定，默认1
     * @param string $id
     *            房间ID/车辆管理主键ID
     * @return boolean
     */
    public function unBindOn($openid, $type, $id)
    {
        $where = array(
            'openid' => $openid,
            'type' => $type
        );
        if ($type == 1) {
            $where['hm_id'] = $id;
        }
        if ($type == 2) {
            $where['car_id'] = $id;
        }
        
        $this->startTrans();
        $result = $this->where($where)->delete();
        if (! $result) {
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    /**
     * 切换设置缴费房产/车辆
     *
     * @param string $openid
     *            用户的标识，对当前公众号唯一
     * @param string $type
     *            绑定类型 1-房产绑定 2-车辆绑定，默认1
     * @param string $unbindId
     *            未设置为缴费房产/车辆的ID
     * @param string $bindId
     *            已设置为缴费房产/车辆的ID
     * @return boolean
     */
    public function changePay($openid, $type, $unbindId, $bindId)
    {
        // 已设置为缴费的参数
        $setWhere = array(
            'openid' => $openid,
            'type' => $type
        );
        $setData = array(
            'is_pay' => - 1
        );
        // 未设置为缴费的参数
        $unSetWhere = array(
            'openid' => $openid,
            'type' => $type
        );
        $unSetData = array(
            'is_pay' => 1
        );
        if ($type == 1) {
            $setWhere['hm_id'] = $bindId;
            $unSetWhere['hm_id'] = $unbindId;
        }
        if ($type == 2) {
            $setWhere['car_id'] = $bindId;
            $unSetWhere['car_id'] = $unbindId;
        }
        
        // 解除已设置为缴费的房产/车辆
        $this->startTrans();
        $setResult = $this->where($setWhere)->save($setData);
        $unSetResult = $this->where($unSetWhere)->save($unSetData);
        if (! $setResult || ! $unSetResult) {
            $this->rollback();
            return false;
        }
        // 将新绑定的房产写入session
        if ($type == 1)
            session($openid . 'payhouse', $unbindId);
        if ($type == 2)
            session($openid . 'paycar', $unbindId);
        $this->commit();
        return true;
    }

    /**
     * 查询已设置为缴费房产/车辆的信息
     *
     * @param string $hmId
     *            设置缴费的房产ID
     * @param string $type
     *            绑定类型 1-房产绑定 2-车辆绑定，默认1
     * @return unknown
     */
    public function getSetPayInfo($hmId, $type)
    {
        // 房产绑定额外信息
        if ($type == 1) {
            // 查询该房产所属的信息
            $propertyModel = D('property');
            $houseBelong = array_values($propertyModel->get_house_belong(array(
                intval($hmId)
            )));
            
            // 重组数组
            $info = $houseBelong[0];
            return $info;
        }
        
        // 车辆绑定额外信息
        if ($type == 2) {
            //查询车辆信息
            $table = array(
                'fx_car_manage' => 'cm',
                'fx_community_comp' => 'cc',
            );
            $field = array(
                'cm.id' => 'cid',
                'cm.card_number' => 'card_number',
                'cm.car_number' => 'car_number',
                'cc.id' => 'cc_id',
                'cc.name' => 'cname',
            );
            $where = array(
                'cm.id' => $hmId,
                'cm.cc_id = cc.id',
            );
            $info = $this->table($table)->field($field)->where($where)->find();
            return $info;
        }
    }
}


