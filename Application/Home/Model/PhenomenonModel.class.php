<?php
/*************************************************
 * 文件名：PhenomenonModel.class.php
 * 功能：     故障现象模型
 * 日期：     2015.11.12
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model\RelationModel;

class PhenomenonModel extends RelationModel
{

    protected $tableName = 'fault_phenomenon';

    /**
     * 根据设备ID获取故障现象列表
     *
     * @param array|string $deviceIds
     *            故障设备ID
     * @return unknown
     */
    public function getPhenomenonList($deviceIds)
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
     * 添加故障现象
     *
     * @param string $name
     *            现象名称
     * @param string $status
     *            状态
     * @param integer $dId
     *            所属故障设备ID
     * @return boolean
     */
    public function addPhenomenon($name, $status, $dId)
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
     * 编辑故障现象
     *
     * @param string $id
     *            现象ID
     * @param string $name
     *            现象名称
     * @param number $status
     *            现象状态，默认为1 -1-禁用 1-正常
     * @return boolean
     */
    public function editPhenomenon($id, $name, $status = 1)
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
     * 禁用/启用设备所属的所有故障现象
     *
     * @param string $dId
     *            设备ID
     * @param integer $status
     *            现象状态
     * @return boolean
     */
    public function changePhenomenonsStatus($dId, $status)
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
        // 查询该设备下是否有故障现象
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

    /*
    * 获取所有的故障现象
    */
   public function getAllPhenomenon(){
        $field = array('id','name','status','did');
        return $this->field($field)->select(); 
   } 

   public function getPhenomenonById($id){
        return $this->where(array('id' => $id))->find();
   }

   
}