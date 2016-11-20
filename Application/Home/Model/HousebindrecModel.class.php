<?php
/*************************************************
 * 文件名：HousebindrecModel.class.php
 * 功能：     房产收费绑定模型
 * 日期：     2015.12.31
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class HousebindrecModel extends Model
{

    protected $tableName = 'house_bind_record';

    /**
     * 写入房产费项绑定记录
     * @param string $cmId                      企业ID
     * @param string $chId                       费项ID
     * @param string $uid                         操作者（用户）ID
     * @param string string $address       绑定房产的地址
     * @param bool|false $isCompany    是否为企业下所有房间
     * @param string $ccId                      楼盘ID
     * @return bool
     */
    public function recordHouseBind($cmId, $chId, $uid, $bindStatus, $address = '', $isCompany = false, $ccId = '', $name)
    {
        $data = [
            'cm_id' => $cmId,
            'ch_id' => $chId,
            'bind_status' => $bindStatus,
            'adress' => $address,
            'update_time' => date('Y-m-d H:i:s'),
            'uid' => $uid,
            'name' => $name,
        ];
        if (!$address) $data['adress'] = '所有楼盘';
        if (!$isCompany) $data['cc_id'] = $ccId;
        $result = $this->add($data);
        if (!$result) return false;
        return true;
    }
}