<?php
/*************************************************
 * 文件名：ProtertyModel.class.php
 * 功能：     房产管理模型
 * 日期：     2015.8.5
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Org\Util\RabbitMQ;

class PropertyModel extends BaseModel
{

    protected $tableName = 'community_comp';

    protected $queue = 'newhouse_charges_queue';
    
    protected $compdeviceModel;

    /**
     * 初始化
     */
    public function _initialize()
    {}

    /**
     * 根据企业ID查询楼盘列表
     *
     * @param string $cm_id
     *            企业ID
     * @param number $offset
     *            页数，默认为0，即第一页
     * @param string $name
     *            楼盘名称（可选），默认为空
     * @param string $status
     *            楼盘状态 -1-禁用 1-正常 2-所有，默认为空，即所有
     * @return array 数据数组 状态码-> -1-未接收到数据 1-查询不到楼盘 2-查询到楼盘，返回数据信息
     */
    public function get_property_list($cm_id, $offset = 0, $length = '', $name = '', $status = '')
    {
        
        // TODO 没有缓存
        if ($cm_id) {
            // 查询该企业所有楼盘
            $propertyField = array(
                'id',
                'number',
                'name',
                'address_id',
                'remark',
                'status',
                'create_time',
                'modify_time'
            );
            $propertyWhere = array(
                'cm_id' => $cm_id
            );
            $order = array(
                'modify_time' => 'desc'
            );
            if ($name) {
                $propertyWhere['name'] = array(
                    'like',
                    "%{$name}%"
                );
            }
            if ($status) {
                if ($status == 1) {
                    $propertyWhere['status'] = 1;
                } else 
                    if ($status == - 1) {
                        $propertyWhere['status'] = '-1';
                    }
            }
            $propertyResult = $this->field($propertyField)
                ->where($propertyWhere)
                ->order($order)
                ->limit($offset, $length)
                ->select();
            $count = $this->where($propertyWhere)->count('id');
            $total = ceil($count / 10);
            if ($propertyResult) {
                // 查询楼盘所属地
                $areaField = array(
                    'c.id',
                    'c.name',
                    'c.pid'
                );
                $cityTable = array(
                    'fx_city' => 'c'
                );
                
                $buildField = array(
                    'b.id'
                );
                $buildTable = array(
                    'fx_building_manage' => 'b'
                );
                
                $houseField = array(
                    'h.id'
                );
                $houseTable = array(
                    'fx_house_manage' => 'h'
                );
                
                $carField = array(
                    'ca.id'
                );
                $carTable = array(
                    'fx_car_manage' => 'ca'
                );
                foreach ($propertyResult as $pk => $pv) {
                    // 判断空备注
                    if (! $pv['remark']) {
                        $propertyResult[$pk]['remark'] = '暂无备注';
                    }
                    
                    // 判断空楼盘编号
                    if (! $pv['number']) {
                        $propertyResult[$pk]['number'] = '暂无楼盘编号';
                    }
                    
                    $areaWhere = array(
                        'c.id' => $pv['address_id']
                    );
                    $areaResult = $this->table($cityTable)
                        ->field($areaField)
                        ->where($areaWhere)
                        ->find();
                    if ($areaResult) {
                        // 获取区父级ID，查询城市
                        $cityWhere = array(
                            'c.id' => $areaResult['pid']
                        );
                        $cityResult = $this->table($cityTable)
                            ->field($areaField)
                            ->where($cityWhere)
                            ->find();
                        if ($cityResult) {
                            // 获取城市父级ID，查询省
                            $provinceWhere = array(
                                'c.id' => $cityResult['pid']
                            );
                            $provinceResult = $this->table($cityTable)
                                ->field($areaField)
                                ->where($provinceWhere)
                                ->find();
                            if ($provinceResult) {
                                // 组装省市区
                                $propertyResult[$pk]['address'] = $provinceResult['name'] . $cityResult['name'] . $areaResult['name'];
                            } else {
                                // TODO 查询不到省
                                $propertyResult[$pk]['address'] = null;
                            }
                        } else {
                            // TODO 查询不到城市
                            $propertyResult[$pk]['address'] = null;
                        }
                    } else {
                        // TODO 查询不到区
                        $propertyResult[$pk]['address'] = null;
                    }
                    
                    // 统计该楼盘下的楼宇数
                    $buildWhere = array(
                        'b.cc_id' => $pv['id']
                    );
                    $buildResult = $this->table($buildTable)
                        ->field($buildField)
                        ->where($buildWhere)
                        ->select();
                    // 组装楼宇
                    if ($buildResult) {
                        // TODO 查询到楼宇，则统计该楼宇下的房产数
                        $propertyResult[$pk]['build_num'] = count($buildResult);
                        
                        foreach ($buildResult as $bk => $bv) {
                            $houseWhere = array(
                                'h.bm_id' => $bv['id']
                            );
                            $houseResult = $this->table($houseTable)
                                ->field($houseField)
                                ->where($houseWhere)
                                ->select();
                            $propertyResult[$pk]['house_num'] += count($houseResult);
                        }
                    } else {
                        // TODO 查询不到楼宇，则该楼盘下楼宇数为0，房产数为0
                        $propertyResult[$pk]['build_num'] = 0;
                        $propertyResult[$pk]['house_num'] = 0;
                    }
                    
                    // 统计该楼盘下的车位数
                    $carWhere = array(
                        'ca.cc_id' => $pv['id']
                    );
                    $carResult = $this->table($carTable)
                        ->field($carField)
                        ->where($carWhere)
                        ->select();
                    if ($carResult) {
                        $propertyResult[$pk]['car_num'] = count($carResult);
                    } else {
                        // TODO 查询不到车位，则该楼盘下车位数为0
                        $propertyResult[$pk]['car_num'] = 0;
                    }
                }
                $result = array(
                    'list' => $propertyResult,
                    'total' => $total
                );
                
                $flag = json_encode(array(
                    2,
                    $result
                ));
                // TODO 如果没有搜索条件和分页，写入缓存
                if (! ($offset || $name || $status)) {
                    S($cacheName, $flag);
                }
                return $flag;
            } else {
                // TODO 查询不到楼盘
                $result = array(
                    'list' => $propertyResult,
                    'total' => $total
                );
                $flag = json_encode(array(
                    1,
                    $result
                ));
                return $flag;
            }
        } else {
            $flag = json_encode(array(
                - 1,
                null
            ));
            return $flag;
        }
    }

    /**
     * 根据企业ID和楼盘ID查找该楼盘信息
     *
     * @param string $cm_id
     *            企业ID
     * @param string $id
     *            楼盘ID
     * @return array 数据数组 状态码-> -1-为接收到数据 1-查询不到楼盘信息 2-查询成功，返回楼盘信息
     */
    public function find_property_info($cm_id, $id)
    {
        $flag = array(
            - 1,
            null
        );
        
        if ($cm_id && $id) {
            $field = array(
                'id',
                'number',
                'name',
                'address_id',
                'remark',
                'status'
            );
            $where = array(
                'cm_id' => $cm_id,
                'id' => $id
            );
            $result = $this->field($field)
                ->where($where)
                ->find();
            if ($result) {
                // TODO 查询该楼盘的省市区
                // 获取区ID，查询区
                $areaId = $result['address_id'];
                $areaField = array(
                    'id',
                    'name',
                    'pid'
                );
                $areaWhere = array(
                    'id' => $areaId
                );
                $areaTable = array(
                    'fx_city' => 'c'
                );
                $areaResult = $this->table($areaTable)
                    ->field($areaField)
                    ->where($areaWhere)
                    ->find();
                $result['area'] = $areaResult;
                
                // 获取区父级ID
                $cityId = $areaResult['pid'];
                $cityWhere = array(
                    'id' => $cityId
                );
                $cityResult = $this->table($areaTable)
                    ->field($areaField)
                    ->where($cityWhere)
                    ->find();
                $result['city'] = $cityResult;
                
                // 获取城市父级ID
                $provinceId = $cityResult['pid'];
                $provinceWhere = array(
                    'id' => $provinceId
                );
                $provinceResult = $this->table($areaTable)
                    ->field($areaField)
                    ->where($provinceWhere)
                    ->find();
                $result['province'] = $provinceResult;
                
                $flag = array(
                    2,
                    $result
                );
                return $flag;
            } else {
                // TODO 查询不到楼盘信息
                $flag = array(
                    1,
                    null
                );
                return $flag;
            }
        } else {
            $flag = array(
                - 1,
                null
            );
            return $flag;
        }
    }

    /**
     * 添加楼盘
     *
     * @param string $cm_id
     *            企业ID
     * @param string $name
     *            楼盘名称
     * @param string $address_id
     *            楼盘所属地ID（区ID）
     * @param string $remark
     *            备注
     * @return number 状态码 -1-没接收到数据 1-添加失败 2-添加成功
     */
    public function add_property($cm_id, $name, $address_id, $remark, $repairSetting)
    {
        // 查询该企业下楼盘数
        $map = array(
            'cm_id' => $cm_id
        );
        $count = $this->where($map)->count('id');
        
        // 组装添加的数据
        // 生成楼盘编号
        if ($count == 0) {
            $data['number'] = 'LP00001';
        } elseif ($count > 0) {
            // 不足5位补足零
            $data['number'] = str_pad(($count + 1), 5, 0, STR_PAD_LEFT);
            $data['number'] = 'LP' . $data['number'];
        }
        $data['name'] = $name;
        $data['cm_id'] = $cm_id;
        $data['address_id'] = $address_id;
        $data['create_time'] = date('Y-m-d H:i:s', time());
        $data['modify_time'] = date('Y-m-d H:i:s', time());
        if ($remark) {
            $data['remark'] = $remark;
        }
        // 添加楼盘，开启事务
        $this->startTrans();
        $result = $this->add($data);
        if (! $result) {
            $this->rollback();
            return false;
        }
        
        foreach ($repairSetting as $r=>$repair){
            $repairSetting[$r]['cc_id']=$result;
        }
        if ($repairSetting){
            //添加维修设置
            $this->compdeviceModel=D('compdevice');
            $repairSetResult=$this->compdeviceModel->bindRepairComp($repairSetting);
            if (!$repairSetResult){
                $this->rollback();
                return false;
            }
        }
        
        $this->commit();
        return true;
    }

    /**
     * 编辑楼盘
     *
     * @param string $id
     *            楼盘ID
     * @param array $data
     *            更新的数据，必须为楼盘表的字段
     * @param array $repairSetting
     *            维修设置数据
     */
    public function edit_property($id, array $data, $originStatus, array $repairSetting)
    {
        // 组装更新的数据，开启事务
        $data['modify_time'] = date('Y-m-d H:i:s', time());
        $this->startTrans();
        $result = $this->save($data);
        if (! $result) {
            $this->rollback();
            return false;
        }
        //如果楼盘原来的状态为正常，则不需要更新楼栋和房间状态
        if ($originStatus == -1) {
            // TODO 更新成功，禁用或恢复该楼盘下所有楼宇
            // 查询该楼盘下所有楼宇
            $builField = 'id';
            $buildWhere = array(
                'cc_id' => $id
            );
            $buildModel = M('building_manage');
            $buildResult = $buildModel->field($builField)
                ->where($buildWhere)
                ->getField('id', true);
            // TODO 查询到楼宇，禁用所有楼宇
            if($buildResult){
                $build_data['status'] = $data['status'];
                $build_data['modify_time'] = date('Y-m-d H:i:s', time());
                $buildSaveWhere = array(
                    'id' => array(
                        'in',
                        $buildResult
                    )
                );
                $buildSaveResult = $buildModel->where($buildWhere)->save($build_data);
                if ($buildSaveResult===false) {
                    $this->rollback();
                    return false;
                }
                // TODO 更新成功，查询楼宇下所有房产
                $houseField = 'id';
                $houseModel = M('house_manage');
                $houseWhere = array(
                    'bm_id' => array(
                        'in',
                        $buildResult
                    )
                );
                $houseResult = $houseModel->where($houseWhere)->getField('id', true);
            }
            if($houseResult){
                // TODO 查询到房产，禁用所有房产
                $house_data['status'] = $data['status'];
                $house_data['modify_time'] = date('Y-m-d H:i:s', time());
                $houseSaveWhere = array(
                    'id' => array(
                        'in',
                        $houseResult
                    )
                );
                $houseSaveResult = $houseModel->where($houseSaveWhere)->save($house_data);
                if ($houseSaveResult===false) {
                    // TODO 房产更新失败，回滚事务
                    $this->rollback();
                    return false;
                }
            }
        }

        // TODO 全部更新成功，更新维修设置
        foreach ($repairSetting as $r => $repair) {
            $repairSetting[$r]['cc_id'] = $id;
        }
        // 添加维修设置
        $this->compdeviceModel = D('compdevice');
        $repairSetResult = $this->compdeviceModel->bindRepairComp($repairSetting);
        if (! $repairSetResult) {
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    /**
     * 根据楼宇ID查询房产列表
     * @param string $id 楼宇ID
     * @param number $length 获取记录数，默认为空，即全部
     * @param number $offset 页数，默认为0，即第一页
     * @param string $number 房号（可选），默认为空
     * @param string $hm_number 房产编号，默认为空
     * @param string $status 房产状态 -1-禁用 1-正常（包括空置和入住） 2-所有
     * @return bool
     */
    public function get_house_list($bm_id, $length = '', $offset = 0, $number = '', $hm_number = '', $status = '')
    {
        if (!$bm_id) return false;
        // 查询该楼宇下所有房产
        $houseField = ['h.id', 'h.number', 'h.hm_number', 'h.mobile_number', 'h.name', 'h.building_area', 'h.inside_area', 'h.flat_area', 'h.description', 'h.status'];
        $houseWhere = ['h.bm_id' => $bm_id];
        if ($number) $houseWhere['h.number'] = ['like', "%{$number}%"];
        if ($hm_number) $houseWhere['h.hm_number'] = ['like', "%{$hm_number}%"];
        if ($status == 1) {
            $houseWhere['h.status'] = ['neq', -1];
        } elseif ($status == -1) {
            $houseWhere['h.status'] = -1;
        }
        $houseTable = ['fx_house_manage' => 'h'];
        $order = ['hm_number' => 'asc'];
        $houseResult = $this->table($houseTable)->field($houseField)->where($houseWhere)->order($order)->select();
        if ($length) $houseResult = $this->table($houseTable)->field($houseField)->where($houseWhere)->order($order)->limit($offset, $length)->select();
        $count = $this->table($houseTable)->where($houseWhere)->count('id');
        $total = ceil($count / 10);
        if (!$houseResult) return false;
        // 查询所属楼宇
        $buildField = ['b.name', 'b.cc_id'];
        $buildWhere = ['b.id' => $bm_id];
        $buildTable = ['fx_building_manage' => 'b'];
        $buildResult = $this->table($buildTable)->field($buildField)->where($buildWhere)->find();
        if (!$buildResult) return false;
        // 查询所属楼盘
        $propertyField = ['name'];
        $propertyWhere = ['id' => $buildResult['cc_id']];
        $propertyResult = $this->field($propertyField)->where($propertyWhere)->find();
        if (!$propertyResult) return false;
        // TODO 查询成功，组装数据
        foreach ($houseResult as $hk => $hv) {
            if (!$hv['hm_number']) $houseResult[$hk]['hm_number'] = '暂无房间编号';
            if (!$hv['mobile_number']) $houseResult[$hk]['mobile_number'] = '暂无手机号';
            if (!$hv['name']) $houseResult[$hk]['name'] = '暂无业主名称';
            if (!$hv['building_area']) $houseResult[$hk]['building_area'] = 0;
            if (!$hv['inside_area']) $houseResult[$hk]['inside_area'] = 0;
            if (!$hv['flat_area']) $houseResult[$hk]['flat_area'] = 0;
            if (!$hv['description']) $houseResult[$hk]['description'] = '暂无描述';
            $houseResult[$hk]['building'] = $buildResult['name'];
            $houseResult[$hk]['property'] = $propertyResult['name'];
        }
        $result = ['list' => $houseResult, 'total' => $total];
        return $result;
    }

    /**
     * 查询房间信息
     * @param $id           房间ID
     * @return mixed
     */
    public function getHouseInfo($id)
    {
        $where=[
            'h.id'=>$id
        ];
        $table=[
            'fx_house_manage'=>'h'
        ];
        $result=$this->table($table)->where($where)->find();
        return $result;
    }

    /**
     * 根据楼宇ID查询楼宇信息
     *
     * @param string $id
     *            楼宇ID
     * @return array 数据数组
     */
    public function find_build_info($id)
    {
        $field = array(
            'c.id' => 'property_id',
            'c.name' => 'property_name',
            'b.id' => 'build_id',
            'b.name' => 'build_name'
        );
        $where = array(
            'b.id' => $id,
            'b.cc_id=c.id'
        );
        $table = array(
            'fx_building_manage' => 'b',
            'fx_community_comp' => 'c'
        );
        $result = $this->table($table)
            ->field($field)
            ->where($where)
            ->find();
        return $result;
    }

    /**
     * 查询该楼宇下是否有重复房号
     *
     * @param string $bm_id
     *            楼宇ID
     * @param integer $number
     *            房号
     * @return array 数据数组
     */
    public function check_house_number($bm_id, $number)
    {
        $field = array(
            'h.id'
        );
        $where = array(
            'h.bm_id' => $bm_id,
            'h.number' => $number
        );
        $result = $this->table(array(
            'fx_house_manage' => 'h'
        ))
            ->field($field)
            ->where($where)
            ->find();
        return $result;
    }

    /**
     * 添加房产
     *
     * @param string $bm_id
     *            楼宇ID
     * @param array $data
     *            添加数据，必须为房产表的字段
     * @return number 状态码 -1-未接收到数据 1-添加失败 2-添加成功
     */
    public function do_add_house($bm_id, array $data)
    {
        $flag = - 1;
        
        if ($bm_id && $data) {
            $houseModel = M('house_manage');
            // 查询该楼宇下的房产数
            $where = array(
                'bm_id' => $bm_id
            );
            $count = $houseModel->where($where)->count('id');
            // 生成房间编号
            if ($count == 0) {
                $hm_number = 'FC00001';
            } elseif ($count > 0) {
                // 不足5位补足零
                $hm_number = str_pad(($count + 1), 5, 0, STR_PAD_LEFT);
                $hm_number = 'FC' . $hm_number;
            }
            // 将房间编号组装进添加数据
            $data['hm_number'] = $hm_number;
            
            // 添加房产，开始事务
            $this->startTrans();
            $result = $houseModel->add($data);
            if ($result) {
                // 提交事务
                $this->commit();
                // 发布队列
                $houseBelong = array_values($this->get_house_belong(array(
                    $result
                )))[0];
                RabbitMQ::publish($this->queue, json_encode($houseBelong));
                // 删除缓存
                S('get_house_list:' . $bm_id, null);
                $flag = 2;
                return $flag;
            } else {
                // TODO 添加失败，回滚事务
                $this->rollback();
                $flag = 1;
                return $flag;
            }
        } else {
            $flag = - 1;
            return $flag;
        }
    }

    /**
     * 根据楼宇ID查询
     *
     * @param string $bm_id            
     * @return array
     */
    public function find_house_info($id)
    {
        $field = ['h.id','h.number','h.hm_number','h.floor','h.mobile_number','h.name','h.building_area','h.inside_area','h.flat_area','h.description','h.status','h.bm_id'];
        $where = ['h.id'=>$id];
        $result = $this->table(['fx_house_manage' => 'h'])->field($field)->where($where)->find();
        return $result;
    }

    /**
     * 编辑房产
     *
     * @param string $id
     *            房产ID
     * @param array $data
     *            添加的数据，必须为房产表的字段
     * @return number 状态码 -1-未接收到数据 1-编辑失败 2-编辑成功
     */
    public function do_edit_house($id, array $data)
    {
        $flag = - 1;
        
        if ($id && $data) {
            // 编辑楼盘，开始事务
            $this->startTrans();
            $houseModel = M('house_manage');
            $where = array(
                'id' => $id
            );
            $result = $houseModel->where($where)->save($data);
            if ($result) {
                // TODO 更新成功，提交事务
                $this->commit();
                // 删除缓存
                S('get_house_list:' . $data['bm_id'], null);
                $flag = 2;
                return $flag;
            } else {
                // TODO 更新失败，回滚事务
                $this->rollback();
                $flag = 1;
                return $flag;
            }
        } else {
            $flag = - 1;
            return $flag;
        }
    }

    /**
     * 根据房产ID查房产名称
     *
     * @param string $pid
     *            房产ID
     */
    public function selectProName($pid)
    {
        $field = array(
            'id',
            'name',
        );
        $where = array(
            'id' => $pid
        );
        $result = $this->field($field)
            ->where($where)
            ->find();
        return $result;
    }

    /**
     * 添加楼宇
     *
     * @param string $cm_id
     *            企业ID
     * @param array $data
     *            添加的数据，必须为楼宇表里的字段
     * @return array 状态码 -1-未接收到数据 1-添加失败 2-添加成功，返回主键ID
     */
    public function do_add_building($cm_id, array $data)
    {
        $flag = array(
            - 1,
            null
        );
        
        if ($cm_id && $data) {
            $buildModel = M('building_manage');
            // 添加楼宇，开始事务
            $result = $buildModel->add($data);
            if ($result) {
                // TODO 添加成功，提交事务
                $this->commit();
                $flag = array(
                    2,
                    $result
                );
                return $flag;
            } else {
                // TODO 添加失败，回滚事务
                $this->rollback();
                $flag = array(
                    1,
                    null
                );
                return $flag;
            }
        } else {
            $flag = array(
                - 1,
                null
            );
            return $flag;
        }
    }

    /**
     * 删除楼宇
     *
     * @param string $id
     *            楼ID
     * @return number 状态码 -1-未接收到数据 1-删除失败 2-删除成功
     */
    public function do_del_building($id)
    {
        $flag = - 1;
        
        if ($id) {
            $buildModel = M('building_manage');
            // 删除楼宇，开启事务
            $where = array(
                'id' => $id
            );
            $result = $buildModel->where($where)->delete();
            if ($result) {
                // TODO 删除成功，提交事务
                $this->commit();
                $flag = 2;
                return $flag;
            } else {
                // TODO 删除失败，回滚事务
                $this->rollback();
                $flag = 1;
                return $flag;
            }
        } else {
            $flag = - 1;
            return $flag;
        }
    }

    /**
     * 根据日志ID查找日志
     *
     * @param string $id
     *            日志ID
     * @return array 数据数组
     */
    public function find_import_log($id)
    {
        $field = array(
            'id',
            'name',
            'code',
            'user_name',
            'import_time',
            'success',
            'failures',
            'error_no'
        );
        $where = array(
            'l.id' => $id
        );
        $table = array(
            'fx_import_log' => 'l'
        );
        $result = $this->table($table)
            ->field($field)
            ->where($where)
            ->find();
        return $result;
    }

    /**
     * 查询用户的导入日志列表
     *
     * @param string $code
     *            用户账号
     * @return array 数据数组
     */
    public function get_import_log_list($code,$cm_id)
    {
        $field = array(
            'id',
            'name',
            'user_name',
            'import_time',
            'success',
            'failures',
            'error_no'
        );
        $where = array(
            'l.code' => $code,
            'l.cm_id'=>$cm_id,
            'l.il_type' => 4
        );
        $table = array(
            'fx_import_log' => 'l'
        );
        $result = $this->table($table)
            ->field($field)
            ->where($where)
            ->select();
        return $result;
    }

    /**
     * 根据企业或楼盘或楼宇或房间ID，获取所有或单个房间ID
     *
     * @param string $id
     *            企业ID/楼盘ID/楼宇ID/房间ID
     * @param string $type
     *            'company'-企业 'community'-楼盘 'building'-楼宇 'house'-房间
     * @return array true-房间ID的集合|false
     */
    public function get_all_house_ids($id, $type)
    {
        if ($id && $type) {
            // 存在企业ID，查询所有楼盘ID
            if ($type == 'company') {
                $ccWhere = array(
                    'cm_id' => $id,
                    'status' => array(
                        'neq',
                        '-1'
                    )
                );
                $cc_Result = $this->table('fx_community_comp')
                    ->where($ccWhere)
                    ->getField('id,name', true);
                $cc_ids = implode(',', array_keys($cc_Result));
            }
            // 存在楼盘ID，直接获取楼盘ID
            if ($type == 'community') {
                // 楼盘ID
                $cc_ids = $id;
            }
            // 存在企业或者楼盘ID，查询楼宇ID
            if (in_array($type, [
                'company',
                'community'
            ])) {
                $bmWhere = array(
                    'cc_id' => array(
                        'in',
                        $cc_ids
                    ),
                    'status' => array(
                        'neq',
                        '-1'
                    )
                );
                $bm_ids = $this->table('fx_building_manage')
                    ->where($bmWhere)
                    ->getField('id', true);
                $bm_ids = implode(',', $bm_ids);
            }
            // 存在楼宇ID，直接获取楼宇ID
            if ($type == 'building') {
                $bm_ids = $id;
            }
            // 存在企业或者楼盘或者楼宇ID，查询房间ID
            if (in_array($type, [
                'company',
                'community',
                'building'
            ])) {
                $hmWhere = array(
                    'bm_id' => array(
                        'in',
                        $bm_ids
                    ),
                    'status' => array(
                        'neq',
                        '-1'
                    )
                );
                $hm_ids = $this->table('fx_house_manage')
                    ->where($hmWhere)
                    ->getField('id', true);
            } else {
                // 存在房间ID，直接获取房间ID
                $hm_ids = $id;
            }
            return $hm_ids;
        } else {
            return false;
        }
    }

    /**
     * 根据房间ID数组查询房间所属信息
     *
     * @param array $hm_ids
     *            房间ID数组
     * @return array|boolean true-房间ID、房号、所属楼宇和楼盘的ID及名称|false
     */
    public function get_house_belong(array $hm_ids)
    {
        if ($hm_ids) {
            $hm_ids = implode(',', $hm_ids);
            
            // 设置缓存名称
            $cacheName = 'house_belong:' . $hm_ids;
            // 判断是否存在缓存
            $cache = S($cacheName);
            if ($cache) {
                return $cache;
            } else {
                // 查询房间所属信息
                $where = array(
                    'h.id' => array(
                        'in',
                        $hm_ids
                    ),
                    'h.bm_id=b.id',
                    'b.cc_id=cc.id',
                    'cc.cm_id=cm.id'
                );
                $table = array(
                    'fx_house_manage' => 'h',
                    'fx_building_manage' => 'b',
                    'fx_community_comp' => 'cc',
                    'fx_comp_manage' => 'cm'
                );
                $houseList = $this->table($table)
                    ->where($where)
                    ->getField('h.id,h.number,h.hm_number,h.bm_id,b.name as build_name,b.cc_id,cc.name as community_name,cc.cm_id,cm.name', true);
                if ($houseList) {
                    // 写入缓存
                    S($cacheName, $houseList);
                }
                return $houseList;
            }
        } else {
            return false;
        }
    }

    /**
     * 根据公众号appid查询楼盘列表
     * 
     * @param string $appid            
     * @return unknown
     */
    public function getPropertyByAppid($appid)
    {
        $field = array(
            'cc.id',
            'cc.name'
        );
        $where = array(
            'p.appid' => $appid,
            'p.isCancel' => -1,
            'p.cm_id=cc.cm_id',
            'cc.status' => 1
        );
        $order = array(
            'cc.modify_time' => 'desc'
        );
        $table = array(
            'fx_publicno' => 'p',
            'fx_community_comp' => 'cc'
        );
        $result = $this->table($table)
            ->field($field)
            ->where($where)
            ->order($order)
            ->select();
        return $result;
    }

    /**
     * 查询楼盘信息
     * 
     * @param unknown $id            
     * @return \Think\mixed
     */
    public function getCommunityInfo($id)
    {
        $field=['cc.id','cc.number','cc.name','cc.cm_id','cc.address_id','ct.name'];
        $where = ['cc.id' => $id];
        $table=['fx_community_comp'=>'cc'];
        $result = $this->table($table)->join('`fx_city` AS `ct` ON cc.address_id=ct.id','LEFT')->field($field)->where($where)->find();
        $cityModel=D('city');
        $area=$cityModel->get_city_name($result['address_id']);
        $city=$cityModel->get_city_name($area['pid']);
        $province=$cityModel->get_city_name($city['pid']);
        $result['address']=$province['name'].$city['name'].$area['name'];
        return $result;
    }

    public function getCommunityByCompid($compid){
        return $this->table('fx_community_comp')->where(array('cm_id' => $compid,'status'=>1))->order('number asc')->select();
    }

    /**根据公司ID查询已经绑定维修公司所有楼盘
     * @param  $compid 公司ID
     *
     * @param  $cc_id  楼盘ID
     *
     */
    public function getCommunityToBindRepair($compid, $cc_id='')
    {
        $field = 'cc.id,cc.name';
        if(empty($cc_id)){
            $where = array(
                'cc.cm_id' => $compid,
                'cc.id=dt.cc_id'
            );
        }else{
            $where = array(
                'cc.id' => $cc_id
            );
        }
        $table = array(
            'fx_community_comp' => 'cc',
            'fx_comp_device_temp' => 'dt'
            );
        $result = $this->table($table)
                        ->distinct(true)
                        ->field($field)
                        ->where($where)
                        ->select();

        return $result;
    }

    /**
     * 根据楼栋ID（或者楼栋D集）获取楼栋列表
     * @param array $bmIds  楼栋ID或楼栋ID集
     * @return mixed
     */
    public function getHouseListsByBuildingIds(array $bmIds)
    {
        $where=[
            'bm_id'=>['in',$bmIds],
        ];
        $result=$this->table('fx_house_manage')->where($where)->select();
        return $result;
    }

    /**
     * 根据公司id获取楼盘列表
     * @param $compid  楼盘ID
     * @param $status  楼盘状态
     * @return mixed
     */
    public function getPropertys($compid, $status=1)
    {
        $field = 'id,name';
        $where = 'cm_id=%d and status=%d';

        return $result = $this->field($field)
            ->where($where, $compid, $status)
            ->select();
    }



    /**
     * 获取物业相关所属
     * @param $id
     * @param $type
     * @return mixed
     */
    public function getPropertyBelog($id, $type)
    {
        $function=new \ReflectionMethod(get_called_class(),'get'.ucwords($type).'Belong');
        $result=$function->invoke($this,$id);
        return $result;
    }

    /**
     * 获取楼盘所属
     * @param $id
     * @return mixed
     */
    public function getCommunityBelong($id)
    {
        $field=[
            'cm.id'=>'compid',
            'cm.name'=>'cm_name',
            'cc.id'=>'cc_id',
            'cc.name'=>'cc_name'
        ];
        $where=[
            'cc.id'=>$id,
            'cm.id=cc.cm_id',
        ];
        $table=[
            'fx_community_comp'=>'cc',
            'fx_comp_manage'=>'cm',
        ];
        $result=$this->table($table)->field($field)->where($where)->find();
        return $result;
    }

    /**
     * 获取楼栋所属
     * @param $id
     * @return mixed
     */
    public function getBuildBelong($id)
    {
        $field=[
            'cm.id'=>'compid',
            'cm.name'=>'cm_name',
            'cc.id'=>'cc_id',
            'cc.name'=>'cc_name',
            'bm.id'=>'bm_id',
            'bm.name'=>'bm_name'
        ];
        $where=[
            'bm.id'=>$id,
            'cc.id=bm.cc_id',
            'cm.id=cc.cm_id'
        ];
        $table=[
            'fx_community_comp'=>'cc',
            'fx_comp_manage'=>'cm',
            'fx_building_manage'=>'bm',
        ];
        $result=$this->table($table)->field($field)->where($where)->find();
        return $result;
    }

    /**
     * 获取房间所属
     * @param $id
     * @return mixed
     */
    public function getHouseBelong($id)
    {
        $field=[
            'cm.id'=>'compid',
            'cm.name'=>'cm_name',
            'cc.id'=>'cc_id',
            'cc.name'=>'cc_name',
            'bm.id'=>'bm_id',
            'bm.name'=>'bm_name',
            'hm.id'=>'hm_id',
            'hm.number'=>'hm_name'
        ];
        $where=[
            'hm.id'=>$id,
            'hm.bm_id=bm.id',
            'cc.id=bm.cc_id',
            'cm.id=cc.cm_id',
        ];
        $table=[
            'fx_community_comp'=>'cc',
            'fx_comp_manage'=>'cm',
            'fx_building_manage'=>'bm',
            'fx_house_manage'=>'hm'
        ];
        $result=$this->table($table)->field($field)->where($where)->find();
        return $result;
    }
}


