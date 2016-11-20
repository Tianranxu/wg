<?php
/*************************************************
 * 文件名：ReasonModel.class.php
 * 功能：     故障原因模型
 * 日期：     2015.11.13
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class ReasonModel extends Model
{

    protected $tableName = 'fault_reason';

    /**
     * 根据设备ID获取故障原因列表
     *
     * @param array|string $deviceIds
     *            故障设备ID
     * @return unknown
     */
    public function getReasonList($deviceIds)
    {
        $field = array(
            'id',
            'name',
            'status',
            'did'
        );
        $where = array(
            'did' => array(
                'in',
                $deviceIds
            )
        );
        $order = array(
            'create_time' => 'desc'
        );
        $result = $this->field($field)
            ->where($where)
            ->order($order)
            ->select();
        return $result;
    }

    /**
     * 添加故障原因
     *
     * @param string $name
     *            原因名称
     * @param string $status
     *            状态
     * @param string $dId
     *            所属的故障设备ID
     * @return boolean
     */
    public function addReason($name, $status, $dId)
    {
        $data = array(
            'name' => $name,
            'status' => $status,
            'did' => intval($dId),
            'create_time' => date('Y-m-d H:i:s')
        );
        $result = $this->add($data);
        if (! $result)
            return false;
        return $result;
    }

    /**
     * 编辑故障原因
     *
     * @param string $id
     *            原因ID
     * @param string $name
     *            原因名称
     * @param number $status
     *            原因状态，默认为1 -1-禁用 1-正常
     * @return boolean
     */
    public function editReason($id, $name, $status = 1)
    {
        $data = array(
            'name' => $name,
            'status' => $status,
            'create_time' => date('Y-m-d H:i:s')
        );
        $where = array(
            'id' => $id
        );
        $result = $this->where($where)->save($data);
        if (! $result)
            return false;
        return true;
    }

    /**
     * 禁用/启用设备所属的所有故障原因
     *
     * @param string $dId
     *            设备ID
     * @param integer $status
     *            原因状态
     * @return boolean
     */
    public function changeReasonsStatus($dId, $status)
    {
        $data = array(
            'status' => $status,
            'create_time' => date('Y-m-d H:i:s')
        );
        $where = array(
            'did' => array(
                'in',
                $dId
            )
        );
        // 查询该设备下是否有故障原因
        $list = $this->where($where)->select();
        if (! $list)
            return true;
        $this->startTrans();
        $result = $this->where($where)->save($data);
        if (! $result) {
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    public function causeById($id){
        return $this->where('id=%d',$id)->find();
    }
}


