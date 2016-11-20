<?php
/*************************************************
 * 文件名：CompdeviceModel.class.php
 * 功能：     楼盘绑定维修公司模型
 * 日期：     2015.11.16
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class CompdeviceModel extends Model
{

    protected $tableName = 'comp_device_temp';

    /**
     * 查询楼盘绑定维修公司列表
     *
     * @param string $ccId
     *            楼盘ID
     * @return \Think\mixed
     */
    public function getBindList($ccId, $status='')
    {
        $field = array(
            't.cc_id',
            'd.id' => 'device_id',
            'd.name' => 'device_name',
            'c.id' => 'compid',
            'c.name' => 'comp_name',
            't.status'
        );
        $join = array(
            'LEFT JOIN `fx_device` `d` ON t.did=d.id',
            'LEFT JOIN `fx_comp_manage` `c` ON t.rc_id=c.id'
        );
        if(empty($status)){
            $where = array(
                't.cc_id' => $ccId
            );
        }else{
            $where = array(
                't.cc_id' => $ccId,
                't.status' => $status
            );
        }

        $table = array(
            'fx_comp_device_temp' => 't'
        );
        $result = $this->table($table)
            ->field($field)
            ->join($join)
            ->where($where)
            ->distinct(true)
            ->select();
        return $result;
    }
    /**
     * 楼盘绑定维修公司
     *
     * @param array $repairSetting
     *            维修设置数据
     * @return boolean
     */
    public function bindRepairComp(array $repairSetting)
    {
        if (! $repairSetting)
            return true;
        foreach ($repairSetting as $repair) {
            $dIds[]=$repair['did'];
        }
        // 查询该楼盘下是否已经绑定过维修公司
        $where = array(
            'cc_id' => $repairSetting[0]['cc_id'],
            'did'=>['in',$dIds]
        );
        $lists = $this->where($where)->select();
        if (!$lists){
            $addResult = $this->addRepairComp($repairSetting);
            if (! $addResult)
                return false;
            return true;
        }

        foreach ($repairSetting as $r => $repair) {
            foreach ($lists as $l => $list) {
                //已存在的不同的维修公司
                if ($list['cc_id'] == $repair['cc_id'] && $list['did'] == $repair['did'] && $list['rc_id']!=$repair['rc_id']) {
                    $disabledData=[
                        'status' => -1,
                        'update_time' => date('Y-m-d H:i:s'),
                    ];
                    $disabledWhere=[
                        'cc_id' => $repair['cc_id'],
                        'did' => $repair['did'],
                    ];
                    $disabledResult = $this->where($disabledWhere)->save($disabledData);
                    $addData=[
                        'status'=>1,
                        'cc_id' => $repair['cc_id'],
                        'did' => $repair['did'],
                        'rc_id'=>$repair['rc_id'],
                        'update_time' => date('Y-m-d H:i:s'),
                    ];
                    $addResult = $this->add($addData);
                    unset($repairSetting[$r]);
                    continue;
                }
                // 已存在绑定的同一个维修公司
                if ($list['cc_id'] == $repair['cc_id'] && $list['did'] == $repair['did']) {
                    // 更新已绑定的维修公司状态
                    $saveData = [
                        'status' => 1,
                        'update_time' => date('Y-m-d H:i:s'),
                        'rc_id'=>$repair['rc_id']
                    ];
                    $saveWhere = [
                        'cc_id' => $repair['cc_id'],
                        'did' => $repair['did'],
                    ];
                    $saveResult = $this->where($saveWhere)->save($saveData);
                    unset($repairSetting[$r]);
                    continue;
                }
            }
        }
        if($repairSetting){
            $addResult = $this->addRepairComp(array_values($repairSetting));
        }
        return true;
    }
    
    protected function addRepairComp($repairSetting){
        $addResult = $this->addAll($repairSetting);
        if (! $addResult)
            return false;
        return true;
    }

    /**
     * 楼盘解绑维修公司
     * 
     * @param string $ccId
     *            楼盘ID
     * @param string $dId
     *            设备ID
     * @param string $rcId
     *            维修公司ID
     * @return boolean
     */
    public function unbindRepairComp($ccId, $dId, $rcId)
    {
        $data = array(
            'status' => - 1,
            'update_time' => date('Y-m-d H:i:s')
        );
        $where = array(
            'cc_id' => $ccId,
            'did' => $dId,
            'rc_id' => $rcId
        );
        $result = $this->where($where)->save($data);
        if (! $result)
            return false;
        return true;
    }

    //通过维修公司id获取绑定了该公司的楼盘信息
    public function getCommunityByRCid($rcid){
        $table = array(
            'fx_comp_device_temp' => 'cdt',
            'fx_community_comp' => 'cc',
        );
        $where = array(
            'cdt.rc_id' => $rcid,
            'cc.id = cdt.cc_id',
            'cdt.status' => 1,
        );
        $field = array(
            'cdt.cc_id' => 'id',
            'cc.name' => 'name',
        );
        $datas = $this->table($table)->where($where)->field($field)->select();
        //去重
        foreach ($datas as $data) {
            $result[$data['cc_id']] = $data;
        }
        return $result;
    }
    /**
     * 停止维修公司时禁用与这相关的物业公司
     * @param $rc_id        维修企业ID
     */
    public function setCompanyDeviceStatus($rc_id){

        return $this->where('rc_id=%d',$rc_id)
                    ->save(array('status' => -1));
    }
}


