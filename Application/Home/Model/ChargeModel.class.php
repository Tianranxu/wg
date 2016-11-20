<?php
/*************************************************
 * 文件名：ChargeModel.class.php
 * 功能：     收费项目管理模型
 * 日期：     2015.8.17
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;
use Think\Model;
use Org\Util\RabbitMQ;

class ChargeModel extends Model{
    protected $tableName = 'charges_manage';
    protected $queue = 'cache_charge_queue';
    
    /**
     * 收费项目列表
     * @param string $cm_id                     企业ID
     * @param number $offset                  页数
     * @param string $category                 项目类别     C('CHARGES')[1]-周期性 C('CHARGES')[2]-抄表类
     * @param string $billing                     计费方式     C('CHARGES')[3]-单价*数量 C('CHARGES')[4]-定额
     * @param string $name                      费项名称
     * @param string $number                   费项编号
     * @param string $charging_cycle        计费周期    字符串，用','隔开
     * @return array                                   状态码  NO_DATA-未接收到数据 OPERATION_FAIL-查询失败 OPERATION_SUCCESS-查询成功，返回未带计费周期条件的$result或带计费周期条件的$finalResult
     */
    public function get_charge_list($cm_id,$offset=0,$length='',$category='',$billing='',$name='',$number='',$charging_cycle=''){
        $flag=array(C('NO_DATA'),null);
        
        if ($cm_id){
            $field='id,name,number,category,billing,measure_style,price,charging_cycle,cm_id,remark';
            $where=array(
                'cm_id'=>$cm_id,
            );
            $order=array('modify_time'=>'desc','create_time'=>'desc');
            //添加搜索条件
            if ($category){
                $where['category']=$category;
            }
            if ($billing){
                $where['billing']=$billing;
            }
            if ($name){
                $where['name']=array('like',"%{$name}%");
            }
            if ($number){
                $where['name']=$number;
            }
            $result=$this->field($field)->where($where)->order($order)->limit($offset,$length)->select();
            $count=$this->where($where)->count('id');
            $total=ceil($count/10);
            if ($result){
                $charges=C('CHARGES');
                foreach ($result as $k=>$v){
                    if ($v['category']){
                        $result[$k]['category']=$charges[$v['category']];
                    }
                    if ($v['billing']){
                        $result[$k]['billing']=$charges[$v['billing']];
                    }
                    if ($v['measure_style']){
                        $result[$k]['measure_style']=$charges[$v['measure_style']];
                    }
                    $result[$k]['charging_cycle']=explode(',', $result[$k]['charging_cycle']);
                    //判断是否有计费周期的搜索条件
                    if ($charging_cycle){
                        if (in_array($charging_cycle, $result[$k]['charging_cycle'])){
                            //重组数组
                            $finalResult[]=$result[$k];
                        }
                    }
                }
                if ($finalResult){
                    $count=count($finalResult);
                    $total=ceil($count/10);
                    $finalResult=array('list'=>$finalResult,'total'=>$total);
                    $flag=array(C('OPERATION_SUCCESS'),$finalResult);
                    return $flag;
                }
                $result=array('list'=>$result,'total'=>$total);
                $flag=array(C('OPERATION_SUCCESS'),$result);
                return $flag;
            }else {
                $flag=array(C('OPERATION_FAIL'),null);
                return $flag;
            }
        }else {
            $flag=array(C('NO_DATA'),null);
            return $flag;
        }
    }
    
    /**
     * 新建收费项目
     * @param string $cm_id     企业ID
     * @param array $data        添加的数据，必须为收费项目表里的字段
     * @return number              状态码 NO_DATA-未接收到数据 OPERATION_FAIL-添加失败 OPERATION_SUCCESS-添加成功
     */
    public function add_charge($cm_id,array $data){
        $flag=C('NO_DATA');
        
        if ($cm_id && $data){
            //统计该企业下费项数
            $where=array(
                'cm_id'=>$cm_id
            );
            $count=$this->where($where)->count('id');
            if ($count==0){
                $data['number']='SF00001';
            }elseif ($count){
                //不足5位补足零
                $data['number']=str_pad(($count+1), 5,0,STR_PAD_LEFT);
                $data['number']='SF'.$data['number'];
            }
            
            //添加收费项目，开启事务
            $this->startTrans();
            $result=$this->add($data);
            if ($result){
                //TODO 添加成功，提交事务
                $this->commit();
                $msg = array('charge_id' => $result, 'data'=> $data);
                RabbitMQ::publish($this->queue, json_encode($msg));
                $flag=C('OPERATION_SUCCESS');
                return $flag;
            }else {
                //TODO 添加失败，回滚事务
                $this->rollback();
                $flag=C('OPERATION_FAIL');
                return $flag;
            }
        }else {
            $flag=C('NO_DATA');
            return $flag;
        }
    }
    
    /**
     * 根据收费项目ID查询收费项目信息
     * @param string $id    收费项目ID
     * @return array           状态码 NO_DATA-未接收到数据 OPERATION_FAIL-查询失败 OPERATION_SUCCESS-查询成功，返回查询结果
     */
    public function find_charge_info($id){
        $flag=array(C('NO_DATA'),null);
        
        if ($id){
            $field='id,name,number,category,billing,measure_style,price,charging_cycle,cm_id,remark';
            $where="id={$id}";
            $result=$this->field($field)->where($where)->find();
            if ($result){
                //TODO 查询成功，组装数组
                $charges=C('CHARGES');
                if ($result['category']){
                    $result['category']=$charges[$result['category']];
                }
                if ($result['billing']){
                    $result['billing']=$charges[$result['billing']];
                }
                if ($result['measure_style']){
                    $result['measure_style']=$charges[$result['measure_style']];
                }
                $result['charging_cycle']=explode(',', $result['charging_cycle']);
                $flag=array(C('OPERATION_SUCCESS'),$result);
                return $flag;
            }else {
                //TODO 查询失败
                $flag=array(C('OPERATION_FAIL'),null);
                return $field;
            }
        }else {
            $flag=array(C('NO_DATA'),null);
            return $flag;
        }
    }
    
    /**
     * 编辑收费项目
     * @param string $cm_id     企业ID
     * @param array $data        更新的数据，必须为收费项目表的字段
     * @return array                  状态码 NO_DATA-未接收到数据 OPERATION_FAIL-更新失败 OPERATION_SUCCESS-更新成功
     */
    public function edit_charge($cm_id,array $data){
        $flag=C('NO_DATA');
        
        if ($cm_id && $data){
            //查询该收费项目信息
            $where=array(
                'id'=>$data['id']
            );
            $chargeInfo=$this->where($where)->getField('id,name,category,billing,measure_style,number');
            if ($chargeInfo){
                //TODO 查询成功
                $returnData['cm_id']=$cm_id;
                $returnData['id']=$data['id'];
                $returnData['name']=$chargeInfo[$data['id']]['name'];
                $returnData['price']=$data['price'];
                $returnData['category']=$chargeInfo[$data['id']]['category'];
                $returnData['billing']=$chargeInfo[$data['id']]['billing'];
                $returnData['measure_style']=$chargeInfo[$data['id']]['measure_style'];
                $returnData['charging_cycle']=$data['charging_cycle'];
                $returnData['remark']=$data['remark'];
                $returnData['number']=$chargeInfo[$data['id']]['number'];
            }else {
                //TODO 查询失败
                $flag=C('OPERATION_FAIL');
                return $flag;
            }
            //更新收费项目，开启事务
            $this->startTrans();
            $result=$this->save($data);
            if ($result){
                //TODO 更新成功，提交事务
                $this->commit();
                $msg = array('charge_id' => $data['id'], 'data'=> $returnData);
                RabbitMQ::publish($this->queue, json_encode($msg));
                $flag=C('OPERATION_SUCCESS');
                return $flag;
            }else {
                //TODO 更新失败，回滚事务
                $this->rollback();
                $flag=C('OPERATION_FAIL');
                return $flag;
            }
        }else {
            $flag=array(C('NO_DATA'),null);
            return $flag;
        }
    }

    //批量添加收费项目
    public function addCharges($data){
        return $this->addAll($data);
    }

    //根据查询条件获取公司下的收费项目
    public function getChargesList($where){
        if($where['charging_cycle']){
            $where['charging_cycle'] = ($where['charging_cycle'] == 1) ? array('like', $where['charging_cycle'] . ',%') : array('like','%' . $where['charging_cycle'] . ',%');
        }
        $limit = $where['limit'];
        $page = $where['page'];
        unset($where['limit'], $where['page']);
        $field = array(
            'id','name','price','remark','status','charging_cycle','measure_style','category','billing','cm_id','number',
        );
        $result['count'] = $this->where($where)->count();
        $result['total'] = ceil($result['count']/$limit);
        $result['data'] = $this->where($where)->field($field)->limit(($page-1)*$limit, $limit)->select();
        return $result;
    }

    //获取费项名称
    public function getNameByCompid($compid){
        return $this->where(array('cm_id' => $compid,'name' => array('exp','IS NOT NULL')))->getField('name',true);
    }

    //清除费项
    public function clearItem($id, $hm_ids){
        $data = array(
            'name' => NULL,
            'price' => NULL,
            'charging_cycle' => NULL,
            'measure_style' => NULL,
            'billing' => NULL,
        );
        // 开启事务
        $this->startTrans();
        $nullResult = $this->where(array('id' => $id))->save($data);
        $clearResult = D('Housecharges')->clearCharge($id, $hm_ids);
        $result = true;
        foreach ($clearResult as $value) {
            if (empty($value) && $value != 0)
                $result = false;
        }
        if ($nullResult && $result && $clearResult) {
            $this->commit();
            return true;
        }else{
            $this->rollback();
            return false;
        }
    }

    //获取单个费项信息
    public function getChargeItem($id){
        return $this->where(array('id' => $id))->find();
    }

    //保存编辑的费项信息
    public function saveItems($id, $data){
        return $this->where(array('id' => $id))->save($data);
    }

    //根据条件查询公司下的绑定记录
    public function getBindRecord($where){
        $table = array(
            'fx_house_bind_record' => 'hbr',
            'fx_sys_user' => 'su',
            'fx_charges_manage' => 'cm',
        );
        $query = array(
            'hbr.uid = su.id',
            'hbr.ch_id = cm.id',
            'hbr.cm_id' => $where['cm_id'],
        );
        //判断搜索条件
        if ($where['number']) {
            $query['cm.number'] = $where['number'];
        }
        if ($where['cc_id']) {
            $query['hbr.cc_id'] = array(
                array('exp' , 'IS NULL'),
                $where['cc_id'],
                'or'
            );
        }
        if($where['start_date'] && $where['end_date']) {
            $query['hbr.update_time'] = array(
                'between',array(date('Y-m-d H:i:s',strtotime($where['start_date'])),date('Y-m-d H:i:s',strtotime($where['end_date'])+86400)));
        }
        if ($where['name']) {
            $query['su.name'] = array('like', '%' . $where['name'] . '%');
        }
        if ($where['ch_id']) {
            $query['hbr.ch_id'] = $where['ch_id'];
        }
        $field = array(
            'hbr.id' => 'id',
            'hbr.ch_id' => 'ch_id',
            'hbr.update_time' => 'update_time',
            'hbr.bind_status' => 'status',
            'hbr.adress' => 'adress',
            'su.code' => 'code',
            'su.name' => 'u_name',
            'cm.number' => 'number',
            'hbr.name' => 'name',
        );
        $result['count'] = $this->table($table)->where($query)->count();
        $result['total'] = ceil($result['count']/$where['limit']);
        $result['data'] = $this->table($table)->where($query)->field($field)->limit(($where['page']-1)*$where['limit'], $where['limit'])->order('update_time desc')->select();
        return $result;
    }

    /**
     * 根据企业ID查询收费项目
     * @param string $cm_id     企业ID
     */
    public function getChargesToCmopid($cm_id){
        $field = array(
            'id',
            'name',
            'number',
            'price',
            'charging_cycle' => 'cycle',
            'measure_style' => 'measure',
            'category',
            'billing'
        );
        $where = 'cm_id=%d';
        return $this->field($field)
            ->where($where, $cm_id)
            ->select();
    }

    //清除费项表并为费项上线前的公司添加32费项
    public function addItems(){
        $this->where('1')->delete();
        $compids = M('comp_manage')->where(array('cm_type' => 1))->getField('id',true);
        foreach ($compids as $key => $compid) {
            for ($i = 0; $i < 32; $i++) {
                $charges[$i] = array(
                    'number' => $i+1,
                    'cm_id' => $compid,
                    'create_time' => date('Y-m-d H:i:s'),
                ); 
            }
            $result[] = $this->addCharges($charges);
        }
        return $result;
    }
}


