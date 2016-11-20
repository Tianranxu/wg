<?php
/*
* 文件名：FaultModel.class.php
* 功能：故障模型
* 日期：2015-11-11
* 作者：XU
* 版权：Copyright @ 2015 风馨科技 All Rights Reserved
*/

namespace Home\Model;
use Think\Model;

class FaultModel extends Model{

     protected $tableName = 'fault_details';
    /**
    * 根据传过来的条件找出相应的故障列表
    * @param $where 查询条件
    * @param $where['page'] 页码
    */
    public function getFaultList($where,$order='create_time desc'){
        $table = array('fx_fault_details' => 'fd');
        $field = array(
            'fd.id','fd.fault_number','fd.status','fd.contacts','fd.ct_mobile','fd.location','fd.origin_fd_id','fd.shift_reson','fd.submitter','fd.type',
            'fd.remark','fd.description','fd.create_time','fd.catch_time','fd.restore_time','fd.shift_time','fd.evaluate_time','fd.finish_time',
            'fd.fr_id','fd.sr_id','fd.cid','fd.cm_id','fd.cc_id','fd.bm_id','fd.did','fd.fp_id','fd.rc_id','fd.sr_id','fd.repairman_openid',
            'sr.name' => 'sr_name',
            'sr.phone' => 'sr_phone',
        );
        //组装查询条件
        foreach ($where as $key => $value) {
            if ($key == 'page' || $key == 'start_time' || $key == 'end_time' || $key == 'flag' || $key == 'limit') {
            }elseif ($key == 'fault_number' || $key == 'location' || $key == 'create_time'){
                $query['fd.'.$key] = array('like','%'.$value.'%');
            }elseif ($key == 'ids') {
                $query['fd.'.'id'] = array('in' , $value);
            }elseif ($key == 'timeout_status'){
                if ($value == 1) {
                    $query['fd.'.'timeout_status']=[['exp','IS NULL'],['gt',2],'or'];
                }elseif ($value == 2){
                    $query['fd.'.'timeout_status'] = array('lt', 3);
                }elseif ($value == 3) {
                    $query['fd.'.$key] = $value;
                }
            }else{
                $query['fd.'.$key] = $value;
            }
        };
        if ($where['start_time'] && $where['end_time']) {
            $query['fd.'.'create_time'] = array(
                'between',array(date('Y-m-d H:i:s',strtotime($where['start_time'])),date('Y-m-d H:i:s',strtotime($where['end_time'])))
            );
        }elseif($where['fd.'.'start_time'] || $where['end_time']){
            $time = $where['fd.'.'start_time'] ? $where['start_time'] : $where['end_time'] ;
            $query['fd.'.'create_time'] = array('like','%'.$time.'%');
        }
        $count = $this->table($table)->where($query)->count();
        if ($where['limit']) {
            $data = $this->table($table)->where($query)
                ->join('`fx_sys_repairer` AS `sr` ON fd.sr_id = sr.id', 'LEFT')->field($field)
                ->limit(($where['page']-1)*$where['limit'],$where['limit'])->order('fd.'.$order)->select();
            $total = ceil($count/$where['limit']);
        }else{
            $data = $this->table($table)->where($query)->order('fd.'.$order)->select();
        }
        return array('result' => $data, 'count' => $count, 'total' => $total);
    }

    //根据故障id获取故障的详情(单个)
    public function getFaultById($id){
        $table = array(
            'fx_fault_details' => 'fd',
            'fx_device' =>'d',
            'fx_fault_phenomenon' => 'fp',
            'fx_comp_manage' => 'cm',
        );
        $where = array(
            'fd.id' => $id,
            'fd.did = d.id',
            'fd.fp_id = fp.id',
            'fd.rc_id = cm.id',
        ); 
        $field = array(
            'fd.id','fd.fault_number','fd.status','fd.timeout_status','fd.contacts','fd.ct_mobile','fd.location','fd.origin_fd_id','fd.shift_reson','fd.submitter','fd.type',
            'fd.remark','fd.description','fd.create_time','fd.catch_time','fd.restore_time','fd.shift_time','fd.evaluate_time','fd.finish_time',
            'fd.fr_id','fd.sr_id','fd.cid','fd.cm_id','fd.cc_id','fd.bm_id','fd.did','fd.fp_id','fd.rc_id','fd.sr_id','fd.repairman_openid',
            'fp.name' => 'p_name',
            'd.name' => 'd_name',
            'cm.name' => 'comp_name',
        );
        return $this->table($table)->where($where)->field($field)->find();
    }
    
    //测试用
    public function getFaultDetails($id){
        $field=array(
            'f.id'=>'id','f.fault_number','f.status','f.contacts','f.ct_mobile','f.submitter','f.type','f.cid','f.cm_id','f.cc_id','f.bm_id','f.location','f.did',
            'd.name'=>'d_name',
            'f.fp_id',
            'p.name'=>'p_name',
            'f.fr_id',
            'r.name'=>'r_name',
            'f.origin_fd_id','f.shift_reson','f.remark','f.rc_id',
            'rp.name'=>'repairer',
            'c.name'=>'comp_name',
            'f.sr_id',
            'rp.name'=>'sr_name',
            'rp.phone',
            'f.repairman_openid','f.description','f.create_time','f.catch_time','f.restore_time','f.shift_time',
            'e.id'=>'evaluate_id',
            'e.work_evaluation','e.service_evaluation','e.eva_content','f.evaluate_time','f.finish_time',
        );
        $where=array(
            'f.id'=>$id
        );
        $table=array(
            'fx_fault_details'=>'f'
        );
        $result=$this->table($table)->field($field)->where($where)
        ->join('`fx_comp_manage` AS `c` ON f.cm_id=c.id','LEFT')
        ->join('`fx_sys_repairer` AS `rp` ON f.sr_id=rp.id','LEFT')
        ->join('`fx_device` AS `d` ON f.did=d.id','LEFT')
        ->join('`fx_fault_phenomenon` AS `p` ON f.fp_id=p.id','LEFT')
        ->join('`fx_fault_reason` AS `r` ON f.fr_id=r.id','LEFT')
        ->join('`fx_evaluation` AS `e` ON f.id=e.fd_id','RIGHT')
        ->find();
        return $result;
    }
    
    //根据id获取故障的附件信息
    public function getAttachment($id){
        return $this->table('fx_fault_picture')->where(array('fd_id' => $id))->select();
    }

    /*
    * 获取评论
    */
    public function getEvaluation($id){
        return $this->table('fx_evaluation')->where(array('fd_id' => $id))->find();
    }

    /*
    * 添加评论
    */
   public function addEvaluation($data){
        $evaluationModel = M('evaluation');
        $this->startTrans();    //开启事务
        $e_result = $evaluationModel->add($data);
        $f_result = $this->where(array('id' => $data['fd_id']))->save(array('status' => C('FAULT_STATUS.EVALUATED') , 'evaluate_time' => date('Y-m-d H:i:s')));
        $result = ($e_result && $f_result) ? true : false;
        $result ? $this->commit() : $this->rollback() ;     //成功则提交，否则回滚
        return $result;
   }

   /*
    * 修改故障单的状态
    */
   public function changeStatus($id,$status){
        if ($status == C('FAULT_STATUS')['FINISH']) {
            $time = 'finish_time';
        }elseif ($status == C('FAULT_STATUS')['CATCHED']) {
            $time = 'catch_time';
        }elseif ($status == C('FAULT_STATUS')['REPAIRED']) {
            $time = 'restore_time';
        }elseif ($status == C('FAULT_STATUS')['SHIFTED']) {
            $time = 'shift_time';
        }elseif($status == C('FAULT_STATUS')['HANGED']){
            return $this->where(array('id' => $id))->save(array('status' => C('FAULT_STATUS')['NOT_YET']));
        }
        $result = $this->where(array('id' => $id))->save(array('status' => $status,$time => date('Y-m-d H:i:s')));
        return $result;
   }

   /*
    * 计算故障数量，用于新建单号
   */
   public function getFaultCount(){
        return $this->max('id');
   }

    /**
     * 接单
     * 
     * @param string $id
     *            故障单ID
     * @param string $srId
     *            维修员ID
     * @param string $repairmanOpenid
     *            维修员OPENID
     * @return boolean
     */
    public function catchFault($id, $srId, $repairmanOpenid)
    {
        $data = array(
            'sr_id' => $srId,
            'repairman_openid' => $repairmanOpenid,
            'status' => C('FAULT_STATUS')['CATCHED'],
            'catch_time' => date('Y-m-d H:i:s')
        );
        $where = array(
            'id' => $id
        );
        $result = $this->where($where)->save($data);
        if (! $result)
            return false;
        return true;
    }
   
    //根据故障id获取故障的详情(回单用)
    public function getFaultInfo($id){
        $table = array(
            'fx_fault_details' => 'fd',
            'fx_device' =>'d',
            'fx_fault_phenomenon' => 'fp',
            'fx_comp_manage' => 'cm',
            'fx_fault_reason' => 'fr',
            'fx_sys_repairer' => 'sr'
        );
        $where = array(
            'fd.id' => $id,
            'fd.did = d.id',
            'fd.fp_id = fp.id',
            'fd.rc_id = cm.id',
            'fd.sr_id = sr.id',
            'fd.fr_id = fr.id',
        );
        $field = array(
            'fd.id','fd.fault_number','fd.status','fd.contacts','fd.ct_mobile','fd.location','fd.origin_fd_id','fd.shift_reson','fd.submitter','fd.type',
            'fd.remark','fd.description','fd.create_time','fd.catch_time','fd.restore_time','fd.shift_time','fd.evaluate_time','fd.finish_time',
            'fd.fr_id','fd.sr_id','fd.cid','fd.cm_id','fd.cc_id','fd.bm_id','fd.did','fd.fp_id','fd.rc_id','fd.sr_id','fd.repairman_openid',
            'fr.name' => 'r_name',
            'sr.name' => 'sr_name',
            'sr.phone' => 'phone',
            'fp.name' => 'p_name',
            'd.name' => 'd_name',
            'cm.name' => 'comp_name',
        );
        return $this->table($table)->where($where)->field($field)->find();
    }

    /*//一定时间内的维修单(物业公司)
    * @param int $compid         公司ID
     * @param string $starDate   开始日期
     * @param string $endDate    结束日期
     * @param int $ccId          楼盘ID
     * @param int $starlimit     开始的数据
    */
    function ord_quantity($compid,$starDate,$endDate='',$ccId=-1){
        $date = empty($endDate) ? date('Y-m-d H:i:s',time()) : $endDate;
        $field = 'fd.id,fd.fault_number,fd.status,fd.type,fd.cm_id,fd.cc_id,fd.bm_id,
                    fd.create_time,fd.catch_time,fd.restore_time,fd.shift_time,fd.evaluate_time,fd.finish_time,
                    e.work_evaluation as work,e.service_evaluation as serve,
                    fd.timeout_status as outType';
        if($ccId == -1){
            $where = array(
                'fd.create_time' => array('between',"{$starDate},{$date}"),
                'fd.cm_id' => $compid
            );
        }else{
            $where = array(
                'fd.create_time' => array('between',"{$starDate},{$date}"),
                'fd.cm_id' => $compid,
                'fd.cc_id' => $ccId
            );
        }

        $table = array('fx_fault_details'=>'fd');
        $result = $this->table($table)->field($field)->where($where)
            ->join(' `fx_evaluation` `e` ON fd.id=e.fd_id','LEFT')
            ->select();
        //echo $this->getLastSql();exit;
        return $result;
    }
   
   //联合查询获取物业和维修公司评价详情
    public function getEvaluationData($where){
        $table = array(
            'fx_evaluation' => 'e',
            'fx_fault_details' => 'fd',
        );
        $query = array(
            'e.fd_id = fd.id',
        );
        $field =  array(
            'e.work_evaluation' => 'work_eva',
            'e.service_evaluation' => 'service_eva',
        );
        if ($where['type'] == 'repair') {
            //若为维修公司
            $table['fx_sys_repairer'] = 'sr' ;
            $query[] = 'fd.sr_id = sr.id' ;
            $query['fd.rc_id'] = $where['compid'];
            $field['sr.name'] = 'name';                 //维修人员名称
            $field['fd.sr_id'] = 'group_id';             //以维修人员分组
        }elseif ($where['type'] == 'property') {
            //若为物业公司
            $table['fx_comp_manage'] = 'cm'; 
            $query[] = 'fd.rc_id = cm.id';
            $query['fd.cm_id'] = $where['compid'];
            $field['cm.name'] = 'name' ;                //维修公司名称
            $field['fd.rc_id'] = 'group_id' ;             //以维修公司分组  
        }
        //组装查询时传过来的条件
        if ($where['cc_id'])
            $query['fd.cc_id'] = $where['cc_id'];
        if ($where['evaluate_time'])
            $query['fd.evaluate_time'] = array('like','%' . $where['evaluate_time'] . '%');
        if ($where['start_time'] && $where['end_time']) {
            $query['fd.evaluate_time'] = array(
                'between',array(date('Y-m-d H:i:s',strtotime($where['start_time'])),date('Y-m-d H:i:s',strtotime($where['end_time'])+86400)));
        }
        return  $this->table($table)->where($query)->field($field)->select();
    }
    /*//一定时间内的维修单(维修公司)
    * @param int $rc_id             维修公司ID
     * @param string $starDate   开始日期
     * @param string $endDate    结束日期
     * @param int $ccId          楼盘ID
     * @param int $starlimit     开始的数据
    */
    function repairQuantity($rc_id,$starDate,$endDate='',$ccId=-1){
        $date = empty($endDate) ? date('Y-m-d H:i:s',time()) : $endDate;
        $field = 'fd.id,fd.fault_number,fd.status,fd.type,fd.cm_id,fd.cc_id,fd.bm_id,
                    fd.create_time,fd.catch_time,fd.restore_time,fd.shift_time,fd.evaluate_time,fd.finish_time,
                    e.work_evaluation as work,e.service_evaluation as serve,
                    fd.timeout_status as outType';
        if($ccId == -1){
            $where = array(
                'fd.create_time' => array('between',"{$starDate},{$date}"),
                'fd.rc_id' => $rc_id
            );
        }else{
            $where = array(
                'fd.create_time' => array('between',"{$starDate},{$date}"),
                'fd.rc_id' => $rc_id,
                'fd.cc_id' => $ccId
            );
        }

        $table = array('fx_fault_details'=>'fd');
        $result = $this->table($table)->field($field)->where($where)
            ->join(' `fx_evaluation` `e` ON fd.id=e.fd_id','LEFT')
            ->select();
        return $result;
    }
    /*查询所有报障楼盘(维修公司)
    *
     * @param $rc_id  维修公司ID
     * @param $prop  楼盘ID
     *
    */
    function repairProperty($rc_id, $prop=''){
        $field = 'cc.id,cc.name';
        if(empty($prop)){
            $where = array(
                'fd.rc_id' => $rc_id,
                'fd.cc_id=cc.id'
            );
        }else{
            $where = array(
                'fd.rc_id' => $rc_id,
                'fd.cc_id' => $prop,
                'fd.cc_id=cc.id'
            );
        }

        $table = array(
            'fx_fault_details' => 'fd',
            'fx_community_comp' => 'cc'
        );
        $result = $this->table($table)
                        ->distinct(true)
                        ->field($field)
                        ->where($where)
                        ->select();
        return $result;

    }
}
