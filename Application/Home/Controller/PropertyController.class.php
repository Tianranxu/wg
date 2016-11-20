<?php
/*************************************************
 * 文件名：ProtertyController.class.php
 * 功能：     房产管理控制器
 * 日期：     2015.8.4
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;

use Home\Controller\AccessController;

class PropertyController extends AccessController
{

    protected $_userModel;

    protected $_companyModel;

    protected $_cityModel;

    protected $_propertyModel;
    
    protected $deviceModel;
    
    protected $compdeviceModel;

    /**
     * 初始化
     */
    public function _initialize()
    {
        parent::_initialize();
        
        $this->_userModel = D('user');
        $this->_companyModel = D('company');
        $this->_cityModel = D('city');
        $this->_propertyModel = D('property');
    }

    /**
     * 物管首页
     */
    public function index()
    {
        // 接收企业ID
        $compId = I('get.compid', '');
        // 判断是否有企业ID
        if (! $compId) {
            $this->error('对不起，你无此权限进入！！', U('User/login'));
            exit();
        }
        
        // 判断是否有用户ID
        if ($this->userID) {
            // TODO 查询用户信息，以及头像url
            $userInfo = $this->_userModel->find_user_info($this->userID);
            $userPhotoUrl = $this->_userModel->find_user_photo($userInfo['photo'])['url_address'];
            // TODO 查询该企业的名称
            $companyInfo = $this->_companyModel->selectCompanyDetail($compId);

            $this->assign('compid', $compId);
            $this->assign('userInfo', $userInfo);
            $this->assign('photo', $userPhotoUrl);
            $this->assign('companyInfo', $companyInfo);
        } else {
            $this->error('对不起，你无此权限进入！！', U('User/login'));
            exit();
        }
        
        $this->display();
    }

    /**
     * 楼盘管理首页
     */
    public function property()
    {
        // 接收企业ID
        $compId = I('request.compid', '');
        if (! $compId) {
            $this->error('对不起，你无此权限进入！！', U('User/login'));
            exit();
        }
        
        $flag = I('post.flag', '');
        if (! $flag) {
            // TODO 未带搜索条件
            // 查询所有楼盘
            $propertyList = $this->_propertyModel->get_property_list($compId, 0,10);
            $propertyList = json_decode($propertyList);
            // 遍历所有楼盘，区分可用和禁用楼盘
            foreach ($propertyList[1]->list as $lk => $lv) {
                if ($lv->status == 1) {
                    // TODO 可用楼盘
                    $useList[] = $propertyList[1]->list[$lk];
                } elseif ($lv->status == - 1) {
                    // TODO 禁用楼盘
                    $disUseList[] = $propertyList[1]->list[$lk];
                }
            }
            $this->assign('useList', $useList);
            $this->assign('disUseList', $disUseList);
            $this->assign('total', $propertyList[1]->total);
            $this->assign('compid', $compId);
            $this->display();
        } else {
            // TODO 附带搜索条件
            // 接收数据
            $propertyName = I('post.propertyName', '');
            $status = I('post.status', '');
            
            if ($status) {
                $propertyList = $this->_propertyModel->get_property_list($compId, 0, 10, $propertyName, $status);
                $propertyList = json_decode($propertyList);
                // 遍历所有楼盘，区分可用和禁用楼盘
                foreach ($propertyList[1]->list as $lk => $lv) {
                    if ($lv->status == 1) {
                        // TODO 可用楼盘
                        $useList[] = $propertyList[1]->list[$lk];
                    } elseif ($lv->status == - 1) {
                        // TODO 禁用楼盘
                        $disUseList[] = $propertyList[1]->list[$lk];
                    }
                }
                if (! $useList) {
                    $useList = '';
                }
                if (! $disUseList) {
                    $disUseList = '';
                }
                $data = array(
                    'use' => $useList,
                    'dis_use' => $disUseList
                );
                retMessage(true, $data);
                exit();
            } else {
                retMessage(false, null, '未接收到数据', '为接收到数据', 4001);
                exit();
            }
        }
    }

    /**
     * 查找维修公司
     */
    public function getRepairCompanies()
    {
        // 接收数据
        $name = I('post.name', '');
        $number = I('post.number', '');
        $page = I('post.page',1);
        
        $result = $this->_companyModel->getRepairCompanies($name, $number, ceil(($page-1)*5), 5);
        if (! $result['list']) {
            retMessage(false, null, '查询不到维修公司', '查询不到维修公司', 4002);
            exit();
        }
        $result['total']=ceil($result['total']/5);
        retMessage(true, $result);
        exit();
    }
    
    /**
     * 新增楼盘页面
     */
    public function add_property()
    {
        // 接收企业ID
        $compId = I('get.compid', '');
        if (! $compId) {
            $this->error('对不起，你无此权限进入！！', U('User/login'));
            exit();
        }
        
        // 查询所有省
        $provinceList = $this->_cityModel->select_province_list();
        //查询所有故障设备
        $this->deviceModel=D('device');
        $deviceList=$this->deviceModel->getDeviceList(1);
        $repairCompanies=$this->_companyModel->getRepairCompanies('','',0,5);
        
        $this->assign('compid', $compId);
        $this->assign('provinceList', $provinceList);
        $this->assign('deviceList',$deviceList);
        $this->assign('repairCompanies',$repairCompanies['list']);
        $this->assign('totalPages',ceil($repairCompanies['total']/5));
        $this->display();
    }

    /**
     * 编辑楼盘页面
     */
    public function edit_property()
    {
        // 接收数据
        $compId = I('get.compid', '');
        $propertyId = I('get.propertyid', '');
        if (! $compId && ! $propertyId) {
            $this->error('对不起，你无此权限进入！！', U('User/login'));
            exit();
        }
        
        // 查询楼盘信息
        $propertyInfo = $this->_propertyModel->find_property_info($compId, $propertyId);
        if ($propertyInfo[0] != 2) {
            $this->error('查询不到该楼盘信息！！', U('property', array(
                'compid' => $compId
            )));
            exit();
        }
        
        // 查询所有省
        $provinceList = $this->_cityModel->select_province_list();
        // 查询楼盘所在省的所有城市
        $cityList = $this->_cityModel->find_city_list($propertyInfo[1]['province']['id']);
        // 查询楼盘所在城市的所有区
        $areaList = $this->_cityModel->find_area_list($propertyInfo[1]['city']['id']);
        //查询所有故障设备
        $this->deviceModel=D('device');
        $deviceList=$this->deviceModel->getDeviceList(1);
        //查询该楼盘下各个设备绑定的维修公司
        $this->compdeviceModel=D('compdevice');
        $deviceBindList=$this->compdeviceModel->getBindList($propertyId);
        foreach ($deviceList as $d=>$device){
            foreach ($deviceBindList as $bind){
                if ($device['id']==$bind['device_id']){
                    $deviceList[$d]['status']=$bind['status'];
                    $deviceList[$d]['compid']=$bind['compid'];
                    $deviceList[$d]['comp_name']=$bind['comp_name'];
                    if ($bind['status']==-1){
                        $deviceList[$d]['compid']='';
                        $deviceList[$d]['comp_name']='';
                    }
                    continue;
                }
            }
        }
        $repairCompanies=$this->_companyModel->getRepairCompanies();
        
        $this->assign('compid', $compId);
        $this->assign('propertyInfo', $propertyInfo[1]);
        $this->assign('provinceList', $provinceList);
        $this->assign('cityList', $cityList);
        $this->assign('areaList', $areaList);
        $this->assign('deviceList',$deviceList);
        $this->assign('repairCompanies',$repairCompanies['list']);
        $this->assign('totalPages',ceil($repairCompanies['total']/5));
        $this->display();
    }

    /**
     * 房产管理首页
     */
    public function house()
    {
        // 接收数据
        $compId = I('request.compid', '');
        $proid = I('request.proid', '');
        $buildid = I('request.buildid', '');
        if (! ($compId && $buildid)) {
            $this->error('对不起，你无此权限进入！！', U('User/login'));
            exit();
        }
        
        $flag = I('post.flag', '');
        if (! $flag) {
            // TODO 未带搜索条件
            // 查询该楼宇下的所有房产
            $houseList = $this->_propertyModel->get_house_list($buildid,10);

            // 遍历所有房产，区分可用和禁用房产
            foreach ($houseList['list'] as $hk => $hv) {
                if ($hv['status'] == - 1) {
                    // TODO 禁用房产
                    $disUseList[] = $houseList['list'][$hk];
                } else {
                    // TODO 可用房产
                    $useList[] = $houseList['list'][$hk];
                }
            }
            
            $this->assign('compid', $compId);
            $this->assign('proid', $proid);
            $this->assign('buildid', $buildid);
            $this->assign('useList', $useList);
            $this->assign('disUseList', $disUseList);
            $this->assign('total', $houseList['total']);
            $this->display();
        } else {
            // TODO 附带搜索条件
            // 接收数据
            $number = I('post.number', '');
            $hm_number = I('post.hm_number', '');
            $status = I('post.status', '');
            $total = I('post.search_total', '');
            
            if (!$status) retMessage(false, null, '未接收到数据', '为接收到数据', 4001);
            $houseList = $this->_propertyModel->get_house_list($buildid, 10, 0, $number, $hm_number, $status);
            // 遍历所有楼盘，区分可用和禁用楼盘
            foreach ($houseList['list'] as $lk => $lv) {
                if ($lv['status'] == 1 || $lv['status'] == 2) {
                    // TODO 可用楼盘
                    $useList[] = $houseList['list'][$lk];
                } elseif ($lv['status'] == - 1) {
                    // TODO 禁用楼盘
                    $disUseList[] = $houseList['list'][$lk];
                }
            }
            if (! $useList) {
                $useList = '';
            }
            if (! $disUseList) {
                $disUseList = '';
            }
            $data = array(
                'use' => $useList,
                'dis_use' => $disUseList,
                'total' => $houseList['total']
            );
            retMessage(true, $data);
        }
    }

    /**
     * 新增房产页面
     */
    public function add_house()
    {
        // 接收数据
        $compId = I('get.compid', '');
        $proid = I('get.proid', '');
        $buildid = I('get.buildid', '');
        if (! ($compId && $buildid)) {
            $this->error('对不起，你无此权限进入！！', U('User/login'));
            exit();
        }
        
        // 查询该楼宇信息
        $buildInfo = $this->_propertyModel->find_build_info($buildid);
        if (! $buildInfo) {
            $this->error('对不起，查询不到该楼宇的信息！', U('house', array(
                'compid' => $compId,
                'buildid' => $buildid
            )));
            exit();
        }
        
        $this->assign('compid', $compId);
        $this->assign('proid', $proid);
        $this->assign('buildid', $buildid);
        $this->assign('buildInfo', $buildInfo);
        $this->display();
    }

    /**
     * 编辑房产页面
     */
    public function edit_house()
    {
        // 接收数据
        $compId = I('get.compid', '');
        $proid = I('get.proid', '');
        $buildid = I('get.buildid', '');
        $houseid = I('get.houseid', '');
        if (! ($compId && $buildid && $houseid)) {
            $this->error('对不起，你无此权限进入！！', U('User/login'));
            exit();
        }
        
        // 查询该楼宇信息
        $buildInfo = $this->_propertyModel->find_build_info($buildid);
        if (! $buildInfo) {
            $this->error('对不起，查询不到该楼宇的信息！', U('house', array(
                'compid' => $compId,
                'buildid' => $buildid
            )));
            exit();
        }
        
        // 查询该房产信息
        $houseInfo = $this->_propertyModel->find_house_info($houseid);
        if (! $houseInfo) {
            $this->error('对不起，查询不到该房产信息！！', U('house', array(
                'compid' => $compId,
                'buildid' => $buildid
            )));
            exit();
        }
        
        $this->assign('compid', $compId);
        $this->assign('proid', $proid);
        $this->assign('buildid', $buildid);
        $this->assign('houseid', $houseInfo['id']);
        $this->assign('buildInfo', $buildInfo);
        $this->assign('houseInfo', $houseInfo);
        $this->display();
    }

    /**
     * 根据省ID查找城市列表
     */
    public function find_city_list()
    {
        // 接收数据
        $province_id = I('post.province_id', '');
        
        if ($province_id) {
            $cityList = $this->_cityModel->find_city_list($province_id);
            if (! $cityList) {
                retMessage(false, null, '查询不到记录', '查询不到记录', 4002);
                exit();
            }
            retMessage(true, $cityList);
        } else {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
    }

    /**
     * 根据城市ID查找区列表
     */
    public function find_area_list()
    {
        // 接收数据
        $city_id = I('post.city_id', '');
        
        if ($city_id) {
            $areaList = $this->_cityModel->find_area_list($city_id);
            if (! $areaList) {
                retMessage(false, null, '查询不到记录', '查询不到记录', 4002);
                exit();
            }
            retMessage(true, $areaList);
        } else {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
    }

    /**
     * 楼盘-加载更多
     */
    public function propertyLoadMore()
    {
        // 接收数据
        $compId = I('post.compid', '');
        $flag = I('post.flag', '');
        $page = I('post.page', '');
        
        if ($compId && $flag && $page) {
            if ($flag == 1) {
                // TODO 未带搜索条件
                $propertyList = $this->_propertyModel->get_property_list($compId, $page, 10, '', '');
                $propertyList = json_decode($propertyList);
                if (($page / 10) >= $propertyList[1]->total) {
                    retMessage(false, null, '已加载完毕', '已加载完毕', 4003);
                    exit();
                }
                if ($propertyList[0] != 2) {
                    retMessage(false, null, '查找不到记录', '查找不到记录', 4002);
                    exit();
                }
                // 遍历所有楼盘，区分可用和禁用楼盘
                foreach ($propertyList[1]->list as $lk => $lv) {
                    if ($lv->status == 1) {
                        // TODO 可用楼盘
                        $useList[] = $propertyList[1]->list[$lk];
                    } elseif ($lv->status == - 1) {
                        // TODO 禁用楼盘
                        $disUseList[] = $propertyList[1]->list[$lk];
                    }
                }
                if (! $useList) {
                    $useList = '';
                }
                if (! $disUseList) {
                    $disUseList = '';
                }
                $data = array(
                    'use' => $useList,
                    'dis_use' => $disUseList
                );
                retMessage(true, $data);
                exit();
            } elseif ($flag == 2) {
                // TODO 附带搜索条件
                // 接收数据
                $propertyName = I('post.propertyName', '');
                $status = I('post.status', '');
                
                if ($status) {
                    $propertyList = $this->_propertyModel->get_property_list($compId, $page, 10, $propertyName, $status);
                    $propertyList = json_decode($propertyList);
                    if (($page / 10) >= $propertyList[1]->total) {
                        retMessage(false, null, '已加载完毕', '已加载完毕', 4003);
                        exit();
                    }
                    if ($propertyList[0] != 2) {
                        retMessage(false, null, '查找不到记录', '查找不到记录', 4002);
                        exit();
                    }
                    // 遍历所有楼盘，区分可用和禁用楼盘
                    foreach ($propertyList[1]->list as $lk => $lv) {
                        if ($lv->status == 1) {
                            // TODO 可用楼盘
                            $useList[] = $propertyList[1]->list[$lk];
                        } elseif ($lv->status == - 1) {
                            // TODO 禁用楼盘
                            $disUseList[] = $propertyList[1]->list[$lk];
                        }
                    }
                    if (! $useList) {
                        $useList = '';
                    }
                    if (! $disUseList) {
                        $disUseList = '';
                    }
                    $data = array(
                        'use' => $useList,
                        'dis_use' => $disUseList
                    );
                    retMessage(true, $data);
                    exit();
                } else {
                    retMessage(false, null, '未接收到数据', '为接收到数据', 4001);
                    exit();
                }
            }
        } else {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
    }

    /**
     * 新增楼盘
     */
    public function do_add_property()
    {
        // 接收数据
        $compid = I('post.compid', '');
        $property_name = I('post.property_name', '');
        $area = I('post.area', '');
        $remark = I('post.remark', '');
        $deviceArr=I('post.deviceArr','');
        $repairCompArr=I('post.repairCompArr','');
        
        if (!$compid || !$property_name || !$area || !$deviceArr || !$repairCompArr){
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
        
        //组装维修设置
        foreach ($deviceArr as $d=>$device){
            foreach ($repairCompArr as $r=>$repair){
                if (!$repair){
                    unset($repairCompArr[$r]);
                    continue;
                }
                if ($d==$r){
                    $repairSetting[$d]['did']=intval($device);
                    $repairSetting[$d]['rc_id']=intval($repair);
                    $repairSetting[$d]['update_time']=date('Y-m-d H:i:s');
                }
            }
        }
        $repairSetting=array_values($repairSetting);
        
        $result = $this->_propertyModel->add_property($compid, $property_name, $area, $remark, $repairSetting);
        if (!$result) {
            retMessage(false, null, '添加失败', '添加失败', 4002);
            exit();
        }
        retMessage(true, null);
        exit();
    }

    /**
     * 解绑维修公司
     */
    public function unbindRepairComp()
    {
        // 接收数据
        $ccId = I('post.ccId', '');
        $dId = I('post.dId', '');
        $rcId = I('post.rcId', '');
        if (! $ccId || ! $dId || ! $rcId) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
        
        $this->compdeviceModel = D('compdevice');
        $result = $this->compdeviceModel->unbindRepairComp($ccId, $dId, $rcId);
        if (! $result) {
            retMessage(false, null, '解绑维修公司失败', '解绑维修公司失败', 4002);
            exit();
        }
        retMessage(true, null);
        exit();
    }

    /**
     * 编辑楼盘
     */
    public function do_edit_property()
    {
        // 接收数据
        $compid = I('post.compid', '');
        $id = I('post.id', '');
        $property_name = I('post.property_name', '');
        $area = I('post.area', '');
        $remark = I('post.remark', '');
        $status = I('post.status', '');
        $originStatus=I('post.originStatus','');
        $deviceArr=I('post.deviceArr','');
        $repairCompArr=I('post.repairCompArr','');
        
        if (!$compid || !$id || !$property_name || !$area || !$status || !$originStatus || !$deviceArr || !$repairCompArr) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
        // 组装更新数组
        $data = array(
            'cm_id' => $compid,
            'id' => $id,
            'name' => $property_name,
            'address_id' => $area,
            'remark' => $remark,
            'status' => intval($status),
        );
        
        //组装维修设置
        foreach ($deviceArr as $d=>$device){
            foreach ($repairCompArr as $r=>$repair){
                if (!$repair){
                    unset($repairCompArr[$r]);
                    continue;
                }
                if ($d==$r){
                    $repairSetting[$d]['did']=intval($device);
                    $repairSetting[$d]['rc_id']=intval($repair);
                    $repairSetting[$d]['update_time']=date('Y-m-d H:i:s');
                }
            }
        }
        $repairSetting=array_values($repairSetting);
        
        $result = $this->_propertyModel->edit_property($id, $data, $originStatus, $repairSetting);
        if (!$result) {
            retMessage(false, null, '更新失败', '更新失败', 4002);
            exit();
        }
        retMessage(true, null);
        exit();
    }

    /**
     * 房产-加载更多
     */
    public function houseLoadMore()
    {
        // 接收数据
        $compId = I('post.compid', '');
        $buildid = I('post.buildid', '');
        $flag = I('post.flag', '');
        $page = I('post.page', '');
        $total = I('post.total', '');
        if (!$compId || !$buildid || !$flag || !$page) retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
        if ($flag == 1) {
            // TODO 未带搜索条件
            if ($total) {
                if (($page / 10) >= $total) retMessage(false, null, '已加载完毕', '已加载完毕', 4003);
            }
            $houseList = $this->_propertyModel->get_house_list($buildid, 10, $page);
            if (!$houseList) retMessage(false, null, '查找不到记录', '查找不到记录', 4002);
            // 遍历所有楼盘，区分可用和禁用楼盘
            foreach ($houseList['list'] as $lk => $lv) {
                if ($lv['status'] >= 1) {
                    // TODO 可用楼盘
                    $useList[] = $houseList['list'][$lk];
                } elseif ($lv['status'] == - 1) {
                    // TODO 禁用楼盘
                    $disUseList[] = $houseList['list'][$lk];
                }
            }
            if (! $useList) $useList = '';
            if (! $disUseList) $disUseList = '';
            $data = ['use' => $useList,'dis_use' => $disUseList,'total' => $houseList[1]->total];
            retMessage(true, $data);
        } elseif ($flag == 2) {
            // TODO 附带搜索条件
            // 接收数据
            $compId = I('post.compid', '');
            $buildid = I('post.buildid', '');
            $number = I('post.number', '');
            $hm_number = I('post.hm_number', '');
            $status = I('post.status', '');
            $total = I('post.search_total', '');
            if (!$status) retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            if ($total && ($page / 10) >= $total) retMessage(false, null, '已加载完毕', '已加载完毕', 4003);
            $houseList = $this->_propertyModel->get_house_list($buildid, 10, $page, $number, $hm_number, $status);
            if (!$houseList) retMessage(false, null, '查找不到记录', '查找不到记录', 4002);
            // 遍历所有楼盘，区分可用和禁用楼盘
            foreach ($houseList['list'] as $lk => $lv) {
                if ($lv['status'] >= 1) {
                    // TODO 可用楼盘
                    $useList[] = $houseList['list'][$lk];
                } elseif ($lv['status'] == - 1) {
                    // TODO 禁用楼盘
                    $disUseList[] = $houseList['list'][$lk];
                }
            }
            if (! $useList) $useList = '';
            if (! $disUseList) $disUseList = '';
            $data = ['use' => $useList,'dis_use' => $disUseList,'total' => $houseList[1]->total];
            retMessage(true, $data);
        }
    }

    /**
     * 判断该楼宇下是否有重复房号
     */
    public function check_house_number()
    {
        // 接收数据
        $buildid = I('post.buildid', '');
        $number = I('post.number', '');
        
        if ($buildid && $number) {
            $result = $this->_propertyModel->check_house_number($buildid, $number);
            if ($result) {
                retMessage(false, null, '该楼宇下有该房号', '该楼宇下有该房号', 4002);
                exit();
            }
            retMessage(true, null);
            exit();
        } else {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
    }

    /**
     * 添加房产
     */
    public function do_add_house()
    {
        // 接收数据
        $buildid = I('post.buildid', '');
        $number = I('post.number', '');
        $name = I('post.name', '');
        $mobile_number = I('post.mobile_number', '');
        $floor = I('post.floor', '');
        $building_area = I('post.building_area', '');
        $inside_area = I('post.inside_area', '');
        $flat_area = I('post.flat_area', '');
        $description = I('post.description', '');
        $status = I('post.status', '');
        
        if ($buildid && $number && $name && $mobile_number && $floor && $status) {
            // 组装添加数据
            $data = array(
                'bm_id' => $buildid,
                'number' => $number,
                'name' => $name,
                'mobile_number' => $mobile_number,
                'floor' => $floor,
                'building_area' => $building_area,
                'inside_area' => $inside_area,
                'flat_area' => $flat_area,
                'description' => $description,
                'status' => $status,
                'create_time' => date('Y-m-d H:i:s', time()),
                'modify_time' => date('Y-m-d H:i:s', time())
            );
            
            $result = $this->_propertyModel->do_add_house($buildid, $data);
            if ($result != 2) {
                retMessage(false, null, '添加房产失败', '添加房产失败', 4002);
                exit();
            }
            retMessage(true, null);
            exit();
        } else {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
    }

    /**
     * 编辑房产
     */
    public function do_edit_house()
    {
        // 接收数据
        $buildid = I('post.buildid', '');
        $houseid = I('post.houseid', '');
        $name = I('post.name', '');
        $mobile_number = I('post.mobile_number', '');
        $floor = I('post.floor', '');
        $building_area = I('post.building_area', '');
        $inside_area = I('post.inside_area', '');
        $flat_area = I('post.flat_area', '');
        $description = I('post.description', '');
        $status = I('post.status', '');
        
        if ($houseid && $name && $mobile_number && $floor && $status) {
            // 组装更新数据
            $data = array(
                'id' => $houseid,
                'name' => $name,
                'mobile_number' => $mobile_number,
                'floor' => $floor,
                'building_area' => $building_area,
                'inside_area' => $inside_area,
                'flat_area' => $flat_area,
                'description' => $description,
                'status' => $status,
                'bm_id' => $buildid,
                'modify_time' => date('Y-m-d H:i:s', time())
            );
            
            $result = $this->_propertyModel->do_edit_house($houseid, $data);
            if ($result != 2) {
                retMessage(false, null, '编辑房产失败', '编辑房产失败', 4002);
                exit();
            }
            retMessage(true, null);
            exit();
        } else {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
    }
    /**
     * 判断是否有公众号和公司绑定,若有绑定，
     */
    public function checkPublicno(){
        //接收ajax传过来的数据0
        $compid = I('post.compid','');
        //初始化公众号模型
        $publicnoModel = M('publicno');
        $check = $publicnoModel->where("cm_id={$compid} AND isCancel=-1")->find();
        if($check){
            retMessage(true,"","",2000);
        }else{
            retMessage(false,"","",4001);
        }
    }
}


