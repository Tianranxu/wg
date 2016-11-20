<?php
/*************************************************
 * 文件名：DeviceModel.class.php
 * 功能：     故障设备模型
 * 日期：     2015.11.11
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class DeviceModel extends Model
{

    protected $tableName = 'device';

    protected $phenomenonModel;

    protected $reasonModel;

    /**
     * 初始化
     */
    public function _initialize()
    {
        $this->phenomenonModel = D('phenomenon');
        $this->reasonModel = D('reason');
    }

    /**
     * 获取故障设备列表
     * 
     * @param number $isEnabled
     *            是否只显示正常设备
     * @return unknown
     */
    public function getDeviceList($isEnabled = 0)
    {
        if ($isEnabled)
            $where = array(
                'status' => 1
            );
        $order = array(
            'create_time' => 'desc'
        );
        $result = $this->where($where)
            ->order($order)
            ->select();
        return $result;
    }

    /**
     * 添加设备/故障现象/故障原因
     *
     * @param string $type
     *            添加类型
     * @param string $name
     *            名称
     * @param integer $status
     *            状态
     * @param string $deviceId
     *            设备ID，默认为空，仅用于添加现象或原因
     * @return mixed
     */
    public function doAdd($type, $name, $status, $deviceId = '')
    {
        $function = new \ReflectionMethod(get_called_class(), 'add' . ucwords($type));
        $result = $function->invoke($this, $name, $status, $deviceId);
        return $result;
    }

    /**
     * 添加设备
     *
     * @param string $name
     *            设备名称
     * @param integer $status
     *            设备状态
     * @return boolean
     */
    public function addDevice($name, $status = 1)
    {
        // 组装数据
        $data = array(
            'name' => $name,
            'status' => intval($status),
            'create_time' => date('Y-m-d H:i:s')
        );
        
        $result = $this->add($data);
        if (! $result)
            return false;
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
    public function addPhenomenon($name, $status = 1, $dId)
    {
        $result = $this->phenomenonModel->addPhenomenon($name, $status, $dId);
        if (! $result)
            return false;
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
    public function addReason($name, $status = 1, $dId)
    {
        $result = $this->reasonModel->addReason($name, $status, $dId);
        if (! $result)
            return false;
        return $result;
    }

    /**
     * 编辑设备/故障原因/故障现象
     *
     * @param string $type
     *            编辑类型
     * @param string $id
     *            ID
     * @param string $name
     *            名称
     * @param integer $status
     *            状态
     * @return mixed
     */
    public function doEdit($type, $id, $name, $status)
    {
        $function = new \ReflectionMethod(get_called_class(), 'edit' . ucwords($type));
        $result = $function->invoke($this, $id, $name, $status);
        return $result;
    }

    /**
     * 编辑设备
     *
     * @param string $id
     *            设备ID
     * @param string $name
     *            设备名称
     * @param number $status
     *            设备状态，默认为1 -1-禁用 1-正常
     * @return boolean
     */
    public function editDevice($id, $name, $status = 1)
    {
        $data = array(
            'name' => $name,
            'status' => $status,
            'create_time' => date('Y-m-d H:i:s')
        );
        $where = array(
            'id' => $id
        );
        $this->startTrans();
        $result = $this->where($where)->save($data);
        if (! $result) {
            $this->rollback();
            return false;
        }
        if ($status == - 1) {
            // 禁用该设备所属故障现象
            $phenomenonResult = $this->phenomenonModel->changePhenomenonsStatus($id, $status);
            if (! $phenomenonResult) {
                $this->rollback();
                return false;
            }
            // 禁用该设备所属故障原因
            $reasonResult = $this->reasonModel->changeReasonsStatus($id, $status);
            if (! $reasonResult) {
                $this->rollback();
                return false;
            }
        }
        $this->commit();
        return true;
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
        $result = $this->phenomenonModel->editPhenomenon($id, $name, $status);
        if (! $result)
            return false;
        return true;
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
    public function editReason($id, $name, $status)
    {
        $data = array(
            'name' => $name,
            'status' => $status,
            'create_time' => date('Y-m-d H:i:s')
        );
        $where = array(
            'id' => $id
        );
        $result = $this->reasonModel->editReason($id, $name, $status);
        if (! $result)
            return false;
        return true;
    }

    /**
     * 更改设备状态
     *
     * @param string $id
     *            设备ID
     * @param integer $status
     *            状态
     * @return boolean
     */
    public function changeDevicesStatus($id, $status)
    {
        $data = array(
            'status' => $status,
            'create_time' => date('Y-m-d H:i:s')
        );
        $where = array(
            'id' => $id
        );
        $this->startTrans();
        $result = $this->where($where)->save($data);
        if (! $result) {
            $this->rollback();
            return false;
        }
        
        // 更改该设备下所有故障现象和故障原因的状态
        $phenomenonResult = $this->phenomenonModel->changePhenomenonsStatus($id, $status);
        if (! $phenomenonResult)
            return false;
        $reasonResult = $this->reasonModel->changeReasonsStatus($id, $status);
        if (! $reasonResult)
            return false;
        
        $this->commit();
        return true;
    }

    /*
     * 根据设备id，现象id和原因id查出相应的信息
     */
    public function getInfomationOfDeviceAndPR($did, $fp_id, $fr_id)
    {
        $table = array(
            'fx_device' => 'd',
            'fx_fault_phenomenon' => 'fp'
        );
        $where = array(
            'd.id' => $did,
            'fp.id' => $fp_id
        );
        $field = array(
            'd.name' => 'device',
            'fp.name' => 'phenomenon'
        );
        return $this->table($table)
            ->where($where)
            ->field($field)
            ->find();
    }

    public function getDeviceById($id){
        return $this->where(array('id' => $id))->find();
    }
}


