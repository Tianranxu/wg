<?php
/*************************************************
 * 文件名：CompanyModel.class.php
 * 功能：     企业模型
 * 日期：     2015.7.23
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;
use Think\Model;
class CompanyModel extends Model{
    protected $trueTableName = 'fx_comp_manage';
    
    
    /**
     * 查询此用户下所有分组
     * @param int $useID         用户id
     */
    
  public function selectGroup($userID){
      $field = 'g.id,g.title,g.type';
      $where = "g.user_id=%d AND g.status=1";
      $table = array('fx_sys_group'=>'g');
      $result= $this->table($table)->field($field)->where($where,$userID)->select();
      return $result;
  }  
  /**AND type!=3
   * 查询此用户下所有分组(新建和编辑用)
   * @param int $useID         用户id
   */
  public function selectGroupForAdd($userID){
      $field = 'g.id,g.title,g.type';
      $where = "g.user_id=%d AND g.status=1 AND type!=3";
      $table = array('fx_sys_group'=>'g');
      $result= $this->table($table)->field($field)->where($where,$userID)->select();
      return $result;
  }
    
  /**
   * 查询此分组下所有企业
   * @param int $groupID         分组id
   * @param int $userID          用户id
   */
  public function selectCompany($groupID,$userID){
      
      $field  = 'cm.id,cm.name,cm.cm_type as category,cm.number,cm.status';
      $where  = "cg.group_id=%d AND cg.user_id=%d AND cg.cm_id=cm.id AND cm.is_delete=1";
      $table  = array('fx_comp_group_temp'=>'cg','fx_comp_manage'=>'cm');
      $result = $this->table($table)->field($field)->where($where,$groupID,$userID)->select();   
      return $result;
  }

  /**
   * 根据分组ids查询所有分组下的所有企业
   * @param int $groupIDs         分组id
   * @param int $userID          用户id
   */
  public function selectCompanyByGroups($groupIDs,$userID){
      $map = array(
          'cg.group_id' => array('in', $groupIDs),
          'cg.user_id' => $userID,
          'cm.is_delete' => 1
      );
      $field  = 'cm.id,cm.name,cm.cm_type as category,cm.number,cm.status,cg.group_id';
      $table  = array('fx_comp_group_temp'=>'cg','fx_comp_manage'=>'cm');
      $result = $this->table($table)->field($field)->where($map)->where('cg.cm_id = cm.id')->select(); 
      return $result;
  }

  /**
   * 根据企业名称查询企业ID
   * @param int $companyname         企业名字
   */
  public function selectCompanyid($companyname){
      $field = 'id,status';
      $where = "name='%s'";    
      $result= $this->field($field)->where($where,$companyname)->find();
      return $result;
  }

    /**
     * 根据企业id和用户id查询所有用户角色名称
     * @param int $companyID         企业id
     * @param int $userID            用户id
     */
    public function selectRoleName($companyID,$userID){
        $field = 'ar.id,ar.name';
        $where = "rt.user_id=%d AND rt.cm_id=%d AND rt.role_id=ar.id";
        $table = array('fx_user_role_temp'=>'rt','fx_sys_role'=>'ar');
        $result= $this->table($table)->field($field)->where($where,$userID,$companyID)->select();
        return $result;
        
    }

    /**
     * 查询数据库最后一条记录
     */
    
    public function selectLast(){
        $bastBig = $this->field('id')->order('id desc')->select();
        $lastNo  = $this->where('id='.$bastBig[0]['id'])->getField('number');
        return $lastNo;
    }
    
    /**
     * 根据企业id查询企业详情(不包括禁用企业)
     * @param int $companyID         企业id
     */
    public function selectCompanyDetail($companyID){
        $field  = 'id,name,description,contacts,office_phone,mobile_num,e_mail,remark,create_time,cm_type,number,associate,templet';
        $where  = "id=%d AND is_delete=1 AND status=1";
        $result = $this->field($field)->where($where,$companyID)->find();
        return $result;
    }
    /**
     * 根据企业id查询企业详情
     * @param int $companyID         企业id
     */
    public function selectCompanyAll($companyID){
        $where  = "id=%d AND is_delete=1";
        $result = $this->where($where,$companyID)->find();
        return $result;
    }
    /**
     * 根据用户id查询所有属企业ID
     * @param int $userID            用户id
     */
    public function selectAllCompany($userID){
        $field = 'cg.cm_id';
        $where = "cg.user_id=%d";
        $table = array('fx_comp_group_temp'=>'cg');
        $result= $this->table($table)->field($field)->where($where,$userID)->select();
        return $result;
    }
    /**
     * 根据角色类型查询所有角色
     * @param int $type           角色所属类型
     */
    public function selectAllRole($type){
        $result = $this->table(array('fx_sys_role'=>'r'))->field('r.id,r.name')->where("r.status=1 AND r.type=%d",$type)->select();
        return $result;
    }
    
    /**
     * 根据用户id和企业ID删除用所在中间表记录
     * @param int $uid            用户id
     * @param int $cid            企业id
     */
    public function deleteTempTable($uid, $cid){
        $sql = "delete rt,gt from fx_user_role_temp as rt,
                fx_comp_group_temp as gt where gt.user_id=rt.user_id and gt.cm_id=rt.cm_id and gt.user_id=%d and gt.cm_id=%d and rt.cm_id is not null";
        $result = $this->execute($sql,$uid,$cid);
        return $result;
    }
    /**
     * 根据用户id查询所有企业和角色
     * @param int $uid            用户id
     */
    public function selectCompanyRole($uid){
        $sql = "Select cm_id as cid,role_id as rid from fx_user_role_temp where cm_id in (select cm_id from fx_comp_group_temp where user_id=%d) and user_id={$uid};";
        $result = $this->query($sql,$uid);
        return $result;
    }
    /**
     * 根据企业ID查询企业信息
     * @param int $companyIds            企业id数组
     */
    public function selectCompanyInfo($companyIds){
        $field = 'id,name';
        $where = array('id'=>array('in',$companyIds),'is_delete'=>array('neq',-1),'status'=>array('eq',1));
        $result = $this->field($field)->where($where)->select();
        return $result;
    }
    /*查找所有指定类型公司
     * @param $type    公司类型
     * @param $number    企业编号
     * 
     */
    public function selectTypeComp($number,$type=1){
        $field = 'id,name,contacts,number';
        $map['name']=array('like',"%{$number}%");
        $where = "cm_type=%d AND status=1 AND is_delete=1";
        $result = $this->field($field)->where($map)->where($where,$type)->select();
        return $result;
    }

    /**
     * 获取维修公司列表
     * 
     * @param string $name
     *            维修公司名称，默认为空
     * @param string $number
     *            维修公司流水号，默认为空
     * @return \Think\mixed
     */
    public function getRepairCompanies($name = '', $number = '', $offset=0, $length='')
    {
        $field = array(
            'id',
            'name',
            'number'
        );
        $where = array(
            'status' => 1,
            'cm_type' => 2,
            'is_delete' => 1
        );
        if ($name)
            $where['name'] = array(
                'like',
                "%{$name}%"
            );
        if ($number)
            $where['number'] = array(
                'like',
                "%{$number}%"
            );
        $total=$this->where($where)->count();
        if($length){
            $result = $this->field($field)
                ->where($where)
                ->limit($offset,$length)
                ->select();
            return ['list'=>$result,'total'=>$total];
        }
        $result = $this->field($field)
            ->where($where)
            ->select();
        return ['list'=>$result,'total'=>$total];
    }
    
    //获取绑定了工作站的所有物业公司的id
    public function getPropertyCompanyIds($wsid){
        return $this->where(array('associate' => $wsid))->getField('id',true);
    }

    /**
     * 维修公司维修时限设置
     * @param $compid       企业ID
     * @param $type            时限类型
     * @param $timelimit    时限
     * @return bool
     */
    public function repairTimelimit($compid, $type, $timelimit)
    {
        if ($type == C('FAULT_LIMIT_TYPE.CATCH_LIMIT_TYPE')) {
            if (!$timelimit) $timelimit = C('FAULT_LIMIT_DEFAULT.CATCH_LIMIT_TIMEOUT');
            $data['catch_time_limit'] = $timelimit;
        }
        if ($type == C('FAULT_LIMIT_TYPE.REPAIR_LIMIT_TYPE')) {
            if (!$timelimit) $timelimit = C('FAULT_LIMIT_DEFAULT.REPAIR_LIMIT_TIMEOUT');
            $data['repair_time_limit'] = $timelimit;
        }
        $data['modify_time'] = date('Y-m-d H:i:s');
        $where = [
            'id' => $compid,
            'cm_type' => 2
        ];
        $result = $this->where($where)->save($data);
        if (!$result) return false;
        return true;
    }
    /**
     * 根据公司ID返回角色类型
     * @param $compid       公司ID
     */
    public function getRoleTypeToCompId($compid)
    {
        $field = 'cm_type';
        $type = $this->field($field)
            ->where('id=%d',$compid)
            ->find();
        switch($type['cm_type']){
            case C('COMPANY_TYPE.PROPERTY'):
                return C('ROLE_TYPE.PROPERTY');
            case C('COMPANY_TYPE.REPAIR'):
                return C('ROLE_TYPE.REPAIR');
            case C('COMPANY_TYPE.WORKSTATION'):
                return C('ROLE_TYPE.WORK');
            default :
                break;
        }
        return false;
    }
    /**
     * 根据公司ID类型返回管理员ID
     * @param $compid       公司ID
     */
    public function getAdminIdToCompId($compid)
    {
        $type = $this->field('cm_type')
                    ->where('id=%d',$compid)
                    ->find();
        switch($type['cm_type']){
            case C('COMPANY_TYPE.PROPERTY') :
                return COMPANY_MANAGE;
            case C('COMPANY_TYPE.REPAIR') :
                return REPAIR_MANAGE;
            case C('COMPANY_TYPE.WORKSTATION') :
                return WORK_MANAGE;
            default :
                break;
        }
        return false;
    }
    /**
     * 查出用户所有有管理员角色的企业
     * @param int $uid            用户id
     */
    public function HaveManageCompanys($uid){

        $field = 'cm.id,cm.name,cm.cm_type as type,rt.role_id as rid';
        $where = array(
            'rt.user_id' => $uid,
            'rt.cm_id=cm.id',
            'cm.status' => array('neq', -1),
            'cm.is_delete' => array('neq', -1),
            'rt.role_id' => array(
                array('eq',COMPANY_MANAGE),
                array('eq',REPAIR_MANAGE),
                array('eq',WORK_MANAGE),
                'or'
            )
        );
        $table = array(
            'fx_user_role_temp' => 'rt',
            'fx_comp_manage' => 'cm',
        );

        $result = $this->table($table)
            ->field($field)
            ->where($where)
            ->select();
        return $result;
    }

    /**
     * 保存通知单备注
     * @param int $id 企业ID
     * @param string $noticeRemark 通知单备注
     * @return bool
     */
    public function saveNoticeRemark($id, $noticeRemark)
    {
        $data = ['notice_remark' => $noticeRemark];
        $where = ['id' => $id];
        $result = $this->where($where)->save($data);
        if ($result===false) return false;
        return true;
    }
    
    
    
    
    
    
    
    
    
}