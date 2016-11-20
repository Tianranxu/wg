<?php
/*************************************************
 * 文件名：CustomerModel.class.php
 * 功能：     企业模型
 * 日期：     2015.7.23
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;
use Think\Model;
class CustomerModel extends Model{
    protected $trueTableName = 'fx_property_pc_user';
    
    /**
     * 查询出所有客户
     * @param int $cid       企业ID
     * @param int $sta       开始号
     * @param int $end       结束号
     */
    
    public function selectAllClient($cid,$star,$end){
        static $dis_star = 0;
        $field  = 'id,name,pu_type as type,contact_number as phone,remark,number,status,modify_time as mtime,status';
        $result = $this->field($field)->where("cm_id={$cid}")->order('status asc')->order('status desc ,modify_time desc')->limit("{$star},{$end}")->select();
        return $result;
    }
    
    /**
     * 查询出某个客户
     * @param string $id       客户id
     */
    
    public function selectClient($id){
        $field  = 'id,name,pu_type as type,contact_number as phone,remark,status,create_time as ctime';
        $result = $this->field($field)->where("id={$id}")->find();
        return $result;
    }
    /**
     * 查询客户表里最后一个客户编号
     * 
     */
    
    public function selectLastNumber($cid){
        $field  = 'id';
        $lastID = $this->field($field)->where("cm_id={$cid}")->order('id desc')->limit(1)->find();
        $result = $this->field('number')->where("id={$lastID['id']}")->find();       
        return $result;
    }
    
    /**
     * 模糊查询客户
     * @param string $compid       企业ID
     * @param string $name         楼宇名称
     * @param string $status       楼宇状态
     */
    
    public function searchCust($compid,$name,$status,$star,$end){
        $field  = 'id,name,pu_type as type,contact_number as phone,remark,number,status,create_time as ctime,status';
        $map['name']   = array('like',"%{$name}%");
        $map['cm_id']  = $compid;
        if($status==2){
            $result = $this->field($field)->where($map)->order('status desc ,modify_time desc')->limit($star,$end)->select();
        }else{
            $result = $this->field($field)->where($map)->where("status={$status}")->order('status desc ,modify_time desc')->limit($star,$end)->select();
        }
        return $result;
    }

    /**
     * 新建客户
     * @param array $datas 客户数据
     * @return bool
     */
    public function addCustomer(array $datas)
    {
        $result = $this->add($datas);
        if (!$result) return false;
        return $result;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
