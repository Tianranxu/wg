<?php
/*************************************************
 * 文件名：DeviceController.class.php
 * 功能：     故障设备控制器
 * 日期：     2015.11.11
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;

use Home\Controller\AccessController;

class DeviceController extends AccessController
{

    protected $deviceModel;

    protected $phenomenonModel;

    protected $reasonModel;

    /**
     * 初始化
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->deviceModel = D('device');
        $this->phenomenonModel = D('phenomenon');
        $this->reasonModel = D('reason');
    }

    /**
     * 故障设备管理页面
     */
    public function index()
    {
        // 获取故障设备列表
        $deviceList = $this->deviceModel->getDeviceList();
        $deviceIds = array_map(function ($device)
        {
            return $device['id'];
        }, $deviceList);
        // 获取故障设备所属故障现象
        $phenomenonList = $this->phenomenonModel->getPhenomenonList($deviceIds);
        // 获取故障设备所属故障原因
        $reasonList = $this->reasonModel->getReasonList($deviceIds);
        // 组装数据
        foreach ($deviceList as $d => $device) {
            // 组装故障现象
            foreach ($phenomenonList as $phenomenon) {
                if ($phenomenon['did'] == $device['id']) {
                    $deviceList[$d]['phenomenon'][] = $phenomenon;
                }
            }
            // 组装故障原因
            foreach ($reasonList as $reason) {
                if ($reason['did'] == $device['id']) {
                    $deviceList[$d]['reason'][] = $reason;
                }
            }
        }
        
        $this->assign('deviceList', $deviceList);
        $this->display();
    }

    /**
     * 添加设备/故障现象/故障原因
     */
    public function addDevice()
    {
        // 接收数据
        $name = I('post.name', '');
        $status = I('post.status', '');
        $type = I('post.type', '');
        $dId = I('post.dId', '');
        if (! $name || ! $status || ! $type) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
        
        $result = $this->deviceModel->doAdd($type, $name, $status, $dId);
        if (! $result) {
            retMessage(false, null, '添加失败', '添加失败', 4002);
            exit();
        }
        retMessage(true, $result);
        exit();
    }

    /**
     * 编辑设备/故障现象/故障原因
     */
    public function editDevice()
    {
        // 接收数据
        $id = I('post.id', '');
        $name = I('post.name', '');
        $status = I('post.status', '');
        $type = I('post.type', '');
        if (! $id || ! $name || ! $status || ! $type) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
        
        $result = $this->deviceModel->doEdit($type, $id, $name, $status);
        if (! $result) {
            retMessage(false, null, '编辑失败', '编辑失败', 4002);
            exit();
        }
        retMessage(true, null);
        exit();
    }

    /**
     * 启用设备
     */
    public function enabledDeivce()
    {
        // 接收数据
        $id = I('post.deviceId','');
        if (! $id) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
        
        $result = $this->deviceModel->changeDevicesStatus($id, 1);
        if (! $result) {
            retMessage(false, null, '启用设备失败', '启用设备失败', 4002);
            exit();
        }
        retMessage(true, null);
        exit();
    }
}


