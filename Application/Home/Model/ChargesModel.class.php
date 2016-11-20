<?php
/*************************************************
 * 文件名：ChargesModel.class.php
 * 功能：     收费管理模型
 * 日期：     2015.7.23
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Org\Util\RabbitMQ;

class ChargesModel extends BaseModel
{

    protected $trueTableName = 'fx_charges_setting';

    protected $queue = 'community_bill_queue';

    /**
     * 查询出所费用设置记录
     * 
     * @param string $companyid
     *            企业id
     * @param string $star
     *            开始id
     * @param string $end
     *            结束id
     */
    public function selectCharges($companyid, $star, $end)
    {
        $result = $this->where("cm_id=%d AND status=1",$companyid)
            ->order('modify_time desc')
            ->limit($star, $end)
            ->select();
        return $result;
    }

    /**
     * 查询出楼盘信息
     * 
     * @param string $proid
     *            楼盘id
     */
    public function selectPro($proid)
    {
        $result = $this->table(array(
            'fx_community_comp' => 'p'
        ))
            ->field('p.id,p.name,p.number')
            ->where("p.id=%d AND p.status=1",$proid)
            ->find();
        return $result;
    }

    /**
     * 查询出房间信息
     * @param string $hid
     *            房间id
     */
    public function selectHouse($hid)
    { // hm_number房间编号，number房号 inside_area套内面积 charging_area计费面积 building_area建筑面积flat_area公摊面积
        $field = 'h.id,h.hm_number,h.number,h.mobile_number as phone,h.building_area as area,h.inside_area as in_area,h.flat_area as f_area,h.charging_area as c_area';
        $result = $this->table(array(
            'fx_house_manage' => 'h'
        ))
            ->field($field)
            ->where("h.id=%d AND h.status<>-1",$hid)
            ->find();
        return $result;
    }

    /**
     * 根据楼盘Ids查询出未禁用楼宇ids
     * 
     * @param array $cc_ids
     *            楼盘Ids
     */
    public function selectValidBuildingByCommunityIds($cc_ids)
    {
        $field = 'id';
        $map = array(
            'b.cc_id' => array(
                'in',
                $cc_ids
            ),
            'b.status' => array(
                'neq',
                - 1
            )
        );
        
        $result = $this->table(array(
            'fx_building_manage' => 'b'
        ))
            ->field($field)
            ->where($map)
            ->select();
        return $result;
    }

    /**
     * 根据楼宇Ids查询出未禁用房间ids
     * 
     * @param array $bm_ids
     *            楼宇Ids
     */
    public function selectValidHouseByBuildingIds($bm_ids)
    {
        $field = 'id';
        $map = array(
            'h.bm_id' => array(
                'in',
                $bm_ids
            ),
            'h.status' => array(
                'neq',
                - 1
            )
        );
        
        $result = $this->table(array(
            'fx_house_manage' => 'h'
        ))
            ->field($field)
            ->where($map)
            ->select();
        return $result;
    }

    /**
     * 查询收费项目信息
     * 
     * @param string $charid
     *            收费项目id
     */
    public function selectChargesItem($charid)
    {
        $field = 'ch.id,ch.name,ch.number,ch.price,ch.charging_cycle as cycle,ch.measure_style as style,ch.category,ch.billing';
        $result = $this->table(array(
            'fx_charges_manage' => 'ch'
        ))
            ->field($field)
            ->where("ch.id=%d AND ch.status=1",$charid)
            ->find();
        return $result;
    }

    /**
     * 查询出企业下所有楼盘
     * 
     * @param string $compid
     *            企业 id
     */
    public function selectALLProp($compid)
    {
        $field = 'c.id,c.name,c.number';
        $result = $this->table(array(
            'fx_community_comp' => 'c'
        ))
            ->field($field)
            ->where("c.cm_id=%d AND c.status=1",$compid)
            ->select();
        return $result;
    }

    /**
     * 查询楼宇下所有房间
     * 
     * @param string $bid
     *            楼宇id
     */
    public function selectHouseForBuild($bid)
    { // hm_number房间编号，number房号 inside_area套内面积 charging_area计费面积 building_area建筑面积flat_area公摊面积
        $field = 'h.id,h.hm_number,h.number,h.mobile_number as phone,h.building_area as area,h.inside_area as in_area,h.flat_area as f_area,h.charging_area as c_area';
        $result = $this->table(array(
            'fx_house_manage' => 'h'
        ))
            ->field($field)
            ->where("h.bm_id=%d AND h.status!=-1",$bid)
            ->select();
        return $result;
    }

    /**
     * 查询出企业下全局所有收费设置
     * 
     * @param string $compid
     *            企业 id
     */
    public function selectGlobalChargersSet($compid)
    {
        $field = 'cg.id,cg.name';
        $result = $this->table(array(
            'fx_charges_manage' => 'cg'
        ))
            ->field($field)
            ->where("cg.cm_id=%d AND cg.status=1",$compid)
            ->select();
        return $result;
    }

    /**
     * 查询出企业下单个全局所有收费设置
     * 
     * @param string $compId
     *            企业 id
     * @param string $chargeId
     *            收费项目 id
     */
    public function selectGlobalChargerSet($compId, $chargeId)
    {
        $field = 'cg.id,cg.name,cg.price,cg.remark,cg.create_time as time,cg.charging_cycle as cycle,cg.measure_style as measure,cg.category,cg.billing,cg.cm_id';
        $result = $this->table(array(
            'fx_charges_manage' => 'cg'
        ))
            ->field($field)
            ->where("cg.id=%d AND cg.cm_id=%d AND cg.status=1",$chargeId,$compId)
            ->find();
        return $result;
    }

    /**
     * 根据id查询费用设置记录
     * 
     * @param string $rid
     *            设置记录id
     */
    public function selectChargesForId($rid)
    {
        $result = $this->where("id=%d AND status=1",$rid)->find();
        return $result;
    }

    /**
     * 收费预览列表
     * 
     * @param string $cm_id
     *            企业ID
     * @param string $cc_id
     *            楼盘ID
     * @param string $bm_id
     *            楼宇ID
     * @param string $hm_number
     *            房号
     * @param string $category
     *            项目类别
     * @param string $charging_cycle
     *            计费周期
     * @return array 状态码 NO_DATA-未接收到数据 OPERATION_FAIL-查询失败 OPERATION_SUCCESS-查询成功，返回数据
     */
    public function get_charges_setting_list($cm_id, $cc_id = '', $bm_id = '', $hm_number = '', $category = '', $charging_cycle = '')
    {
        $flag = array(
            C('NO_DATA'),
            null
        );
        if (empty($cm_id)) {
            return $flag;
        }
        
        // 连接redis
        $redis = $this->connectRedis();
        
        if (! $redis) {
            // TODO 连接失败
            $flag = array(
                C('OPERATION_FAIL'),
                null
            );
            return $flag;
        }
        
        // TODO 连接成功
        $key = 'charges:*:' . $cm_id;
        $key .= ':' . $cc_id ? $cc_id : '*';
        $key .= ':' . $bm_id ? $bm_id : '*';
        $key .= ':*';
        // 模糊匹配redis的key
        $keys = $redis->keys($key);
        
        $id_func = function ($x)
        {
            return $x['id'];
        };
        $validCommunityIds = array_map($id_func, $this->selectALLProp($cm_id));
        $validBuildingIds = array_map($id_func, $this->selectValidBuildingByCommunityIds($validCommunityIds));
        $validHouseIds = array_map($id_func, $this->selectValidHouseByBuildingIds($validBuildingIds));
        
        foreach ($keys as $key) {
            $keySplit = explode(':', $key);
            if (in_array($keySplit[3], $validCommunityIds) && in_array($keySplit[4], $validBuildingIds) && in_array($keySplit[5], $validHouseIds)) {
                $validKeys[] = $key;
            }
        }
        
        $list = array_map(json_decode, $redis->mget($validKeys));
        
        // 断开连接Redis
        $this->disConnectRedis();
        
        if (empty($list)) {
            // TODO 查询不到redis数据
            $flag = array(
                C('OPERATION_FAIL'),
                null
            );
            return $flag;
        }
        // TODO 查询到redis数据
        // 判断搜索条件
        $chargeConfig = C('CHARGES');
        foreach ($list as $lk => $lv) {
            // 没有搜索条件
            if (! $hm_number && ! $category && ! $charging_cycle) {
                // 重组计费周期
                $list[$lk]->charging_cycle = explode(',', $lv->charging_cycle);
                $result[$lk] = $list[$lk];
            } else {
                // 房号
                if ($hm_number && ($lv->hm_number == $hm_number)) {
                    $result[$lk] = $list[$lk];
                }
                // 项目类别
                if ($category && ($lv->category == $category)) {
                    $result[$lk] = $list[$lk];
                }
                // 计费周期
                $list[$lk]->charging_cycle = explode(',', $lv->charging_cycle);
                if ($charging_cycle && in_array($charging_cycle, $lv->charging_cycle)) {
                    $result[$lk] = $list[$lk];
                }
            }
        }
        
        // 重组数组
        $result = array_values($result);
        foreach ($result as $rk => $rv) {
            if ($rv->category) {
                $result[$rk]->category = $chargeConfig[$rv->category];
            }
            if ($rv->billing) {
                $result[$rk]->billing = $chargeConfig[$rv->billing];
            }
            if ($rv->measure_style) {
                $result[$rk]->measure_style = $chargeConfig[$rv->measure_style];
            }
        }
        
        // 将最终结果返回
        $flag = array(
            C('OPERATION_SUCCESS'),
            $result
        );
        return $flag;
    }

    /**
     * 未出账单列表
     *
     * @param array $cm_id
     *            企业ID
     * @param array $hm_list
     *            房间信息数组
     * @param number $offset
     *            页码，默认为0
     * @param string $length
     *            分页数，默认为空
     * @param string $year
     *            年份，默认为空
     * @param string $month
     *            月份，默认为空
     * @param string $number
     *            房号，默认为空
     * @param string $charge_id
     *            费项ID，默认为空
     * @return array|boolean true-数据数组|false
     */
    public function get_unbill_list($cm_id, array $hm_list, $offset = 0, $length = '', $year = '', $month = '', $number = '', $charge_id = '')
    {
        if ($hm_list) {
            $hm_ids = implode(',', array_keys($hm_list));
            
            if ($number) {
                foreach ($hm_list as $lk => $lv) {
                    if ($lv['number'] == $number) {
                        $house_list[] = $hm_list[$lk];
                    }
                }
            }
            
            $hm_list = isset($house_list) ? array_values($house_list) : array_values($hm_list);
            
            // 组装where条件
            $where = array(
                'ac.hm_id' => array(
                    'in',
                    $hm_ids
                ),
                'ac.status' => array(
                    'lt',
                    2
                ),
                'c.cm_id' => $cm_id,
                'ac.cm_id=c.id'
            );
            if ($charge_id) {
                $where['ac.cm_id'] = $charge_id;
            }
            if ($year) {
                $where['ac.year'] = $year;
                if ($month) {
                    $where['ac.month'] = $month;
                }
            }
            // 查询未出账单
            $table = array(
                'fx_accounts_charges' => 'ac',
                'fx_charges_manage' => 'c'
            );
            $accountList = $this->table($table)
                ->field('ac.id,ac.hm_id,ac.cm_id,ac.money,ac.number,ac.bill_time,ac.preferential_money,ac.penalty,ac.description,ac.modify_time,ac.year,ac.month,c.id as charge_id,c.name as charge_name')
                ->where($where)
                ->order('modify_time desc')
                ->select();
            foreach ($accountList as $k => $v) {
                // 将房间房号、所属楼宇和楼盘名称组装进数组
                foreach ($hm_list as $hk => $hv) {
                    if ($v['hm_id'] == $hv['id']) {
                        $accList[$k]['id'] = $v['id'];
                        $accList[$k]['hm_id'] = $v['hm_id'];
                        $accList[$k]['cm_id'] = $v['cm_id'];
                        $accList[$k]['money'] = $v['money'];
                        $accList[$k]['number'] = $v['number'];
                        $accList[$k]['bill_time'] = $v['bill_time'];
                        $accList[$k]['year'] = $v['year'];
                        $accList[$k]['month'] = $v['month'];
                        $accList[$k]['preferential_money'] = $v['preferential_money'];
                        $accList[$k]['penalty'] = $v['penalty'];
                        $accList[$k]['description'] = $v['description'];
                        $accList[$k]['modify_time'] = $v['modify_time'];
                        $accList[$k]['hm_number'] = $hv['number'];
                        $accList[$k]['bm_name'] = $hv['build_name'];
                        $accList[$k]['cc_name'] = $hv['community_name'];
                        $accList[$k]['charge_id'] = $v['charge_id'];
                        $accList[$k]['charge_name'] = $v['charge_name'];
                        
                        if (! $v['money']) {
                            $accList[$k]['money'] = 0;
                        }
                        if (! $v['preferential_money']) {
                            $accList[$k]['preferential_money'] = 0;
                        }
                        if (! $v['penalty']) {
                            $accList[$k]['penalty'] = 0;
                        }
                        if (! $v['description']) {
                            $accList[$k]['description'] = '无';
                        }
                    }
                }
            }
            $accList = array_values($accList);
            // 计算总页数
            $count = count($accList);
            $total = ceil($count / $length);
            $accList = array_slice($accList, $offset, $length);
            $accountList = array(
                'list' => $accList,
                'total' => $total
            );
            return $accountList;
        } else {
            return false;
        }
    }

    /**
     * 未出账单录入优惠
     *
     * @param string $id
     *            未出账单ID
     * @param array $data
     *            更新数据数组，必须为账单表字段
     * @return boolean
     */
    public function add_discount($id, array $data)
    {
        if ($id && $data) {
            // 实例化
            $accountModel = M('accounts_charges');
            // 开启事务
            $this->startTrans();
            $result = $accountModel->save($data);
            if ($result) {
                // TODO 更新成功，提交事务
                $this->commit();
                return true;
            } else {
                // TODO 更新失败，回滚事务
                $this->rollback();
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 未出账单-删除优惠
     *
     * @param string $id
     *            未出账单ID
     * @param array $data
     *            更新数据数组
     * @return boolean
     */
    public function del_discount($id, array $data)
    {
        if ($id && $data) {
            // 实例化
            $accountModel = M('accounts_charges');
            // 开启事务
            $this->startTrans();
            $result = $accountModel->save($data);
            if ($result) {
                // TODO 更新成功，提交事务
                $this->commit();
                return true;
            } else {
                // TODO 更新失败，回滚事务
                $this->rollback();
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 未出账单-生成账单
     *
     * @param string $cm_id
     *            企业ID
     * @param array $data
     *            数据数组，详见wgs的wiki
     * @param string $hm_number
     *            房号，默认为空
     * @return boolean
     */
    public function generate_bills($cm_id, array $data, $hm_number = '')
    {
        if ($cm_id && $data) {
            // 判断ID的类型
            $id = $data['data']['id'];
            $type = $data['data']['type'];
            // 存在楼宇ID
            if ($type == 'building') {
                // 判断是否有房号
                if ($hm_number) {
                    // 查询房号对应的房间ID
                    $where = array(
                        'bm_id' => $data['data']['id'],
                        'number' => $hm_number
                    );
                    $hm_id = $this->table('fx_house_manage')
                        ->where($where)
                        ->getField('id', ',');
                    if ($hm_id) {
                        // 重组$data['data']['id']和$data['data']['type']
                        $data['data']['id'] = $hm_id;
                        $data['data']['type'] = 'house';
                    }
                }
            }
            // 将数据推送到队列中
            RabbitMQ::publish($this->queue, json_encode($data));
            return true;
        } else {
            return false;
        }
    }

    /**
     * 根据三表联查所有房间
     *
     * @param string $pid
     *            楼盘id
     */
    public function selectAllRoom($pid)
    {
        $field = 'p.id as pid,p.name as pname,b.id as bid,b.name as bname,h.id as hid,h.number';
        $where = array(
            'p.id' => $pid,
            'p.status' => 1,
            'b.status' => 1,
            'h.status' => array(
                'neq',
                1
            ),
            'b.cc_id=p.id',
            'h.bm_id=b.id'
        );
        $table = array(
            'fx_community_comp' => 'p',
            'fx_building_manage' => 'b',
            'fx_house_manage' => 'h'
        );
        $result = $this->table($table)
            ->field($field)
            ->where($where)
            ->select();
        return $result;
    }

    /**
     * 根据ID查询记录
     *
     * @param string $pid
     *            楼盘id
     * @param string $bid
     *            楼宇id
     * @param string $hid
     *            房间id
     * @param string $chmid
     *            所属收费项目id
     * @param string $chmid
     *            企业id
     */
    public function selectRepeatSet($pid, $bid, $hid, $chmid, $cm_id)
    {
        $field = 'id,price,charging_cycle as cycle';
        
        if ($hid) {
            $where=array(
                'hm_id'=>$hid,
                'chm_id'=>$chmid,
                'cm_id'=>$cm_id,
                'status'=>1
            );
            $result = $this->field($field)
                ->where($where)
                ->find();
            return $result;
        } elseif ($bid) {
            $where=array(
                'hm_id IS NULL',
                'bm_id'=>$bid,
                'chm_id'=>$chmid,
                'cm_id'=>$cm_id,
                'status'=>1
            );
            $result = $this->field($field)
                ->where($where)
                ->find();
            return $result;
        } else {
            $where=array(
                'hm_id IS NULL',
                'bm_id IS NULL',
                'cc_id'=>$pid,
                'chm_id'=>$chmid,
                'cm_id'=>$cm_id,
                'status'=>1
            );
            $result = $this->field($field)
                ->where($where)
                ->find();
            return $result;
        }
    }
}