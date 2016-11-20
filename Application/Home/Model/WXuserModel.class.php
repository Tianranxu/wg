<?php
/*************************************************
 * 文件名：WXuserModel.class.php
 * 功能：    用户分组模型
 * 日期：     2015.11.20
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;
use Think\Model;
class WXuserModel extends WeixinModel
{
    protected $trueTableName = 'fx_weixin_user';
    
    
    //查询微信用户信息
    public function WXuserInfo($openid){
        $result = $this->where('openid="%s"',$openid)->find();
        return $result;
    }
    
    
    /**
    * 找出公司下的不同类型的微信用户
    * @param Int $compid 公司id
    * @param Int $user_type 用户类型
    */
    public function getUserByType($compid, $user_type, $propertyId=''){
        $table = [
            'fx_weixin_user' => 'wu',
            'fx_manager_wxuser_temp' => 'mwt'
        ];
        $where = [
            'mwt.cm_id' => $compid,
            'mwt.type' => $user_type,
            'mwt.wu_id = wu.id',
        ];
        if ($propertyId) $where['wu.cm_id'] = $propertyId;
        $field = [
            'mwt.cm_id', 'wu.openid', 
        ];
        return $this->table($table)->where($where)->field($field)->select();
    }
    
    /**
    * 找出微信用户所属的物业公司(物业号)
    * @param $compid 可为物业公司id，亦可为工作站id
    */
    public function getPropertyId($openid){
        return $this->where(['openid' => $openid])->find();
    }
    
    /**
    * 检查微信用户是否存在 
    * @param string $phone 用户手机号
    * @param $compid 公司id
    */
    public function checkuser($phone, $compid=''){
        return (!$compid) ? $this->where(['mobile' => $phone])->select() : $this->where(['mobile' => $phone, 'cm_id' => $compid])->select();
    }
    
    /**
    * 添加管理员（能收到模板消息） 
    * @param array $data 管理员和微信用户的信息
    */
    public function addManager($data){
        foreach ($data as $key => $value) {
            $check = M('manager_wxuser_temp')->where($value)->find();
            if ($check) return -1;
        }
        return (count($data) == 1) ? M('manager_wxuser_temp')->add($data[0]) : M('manager_wxuser_temp')->addAll($data);
    }
    
    /**
    * 获取管理员信息
    * @param int $compid 公司id
    */
    public function getManagerByCompid($compid){
        $table = [
            'fx_weixin_user' => 'wu',
            'fx_manager_wxuser_temp' => 'mwt',
        ];
        $where = [
            'mwt.cm_id' => $compid,
            'mwt.wu_id = wu.id',
        ];
        $field = [
            'mwt.type' => 'user_type',
            'wu.mobile' => 'phone',
            'nickname' => 'name'
        ];
        return $this->table($table)->where($where)->field($field)->select();
    }
    
    
    
    
    
    
    
    
}