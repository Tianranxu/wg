<?php
/*************************************************
 * 文件名：WxrepairModel.class.php
 * 功能：     维修员模型
 * 日期：     2015.10.14
 * 作者：     DA fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class WxrepairModel extends WeixinModel{
    
    protected $trueTableName = 'fx_sys_repairer';
    
    
    
    //根据openid查询用户信息
    public function repairer($openid){
        $field = 'id,name,openid,phone,cm_id,gid,exam_id as eid,last_log as log,head,status,access_token,refresh_token,expires';
        $where = 'openid="%s"';
        $result = $this->field($field)->where($where, $openid)->find();
        return $result;
    }
    //根据网页授权获取微信用户信息
    public function WXrepairInfo($access_token,$openid){
        //获取用户信息
        $info = $this->get_user_info($access_token,$openid);
        return $info;
    }
    //刷新access_token
    public function refreshToken($refresh_token){
        $appid = C('REPAIR_PUBLICNO.APPID');
        $url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid={$appid}&grant_type=refresh_token&refresh_token={$refresh_token}";
        //GET请求
        $result=$this->http_get($url);
        return $result;
    }
    
    //根据维修员id获取维修员信息
    public function getRepairmanById($id){
        return $this->table('fx_sys_repairer')
                    ->where(array('id' => $id,'status' => C('REPAIR_STATUS.PENDING')))
                    ->find();
    }
    
    //根据 企业ID查询所有维修员
    public function get_repairer($compid){
        $field = 'id,name,openid,phone,head,cm_id,gid,status,number,request';
        $where = 'cm_id=%d AND status<>-1';
        $result = $this->field($field)
                        ->where($where, $compid)
                        ->order('create_time')
                        ->select();
        return $result;
    }
    //查询维修员区域
    public function get_repair_area($rid,$cid){
        $field = 'c.id,c.name,c.pid';
        $where = 'rc.rid=%d and rc.cid=%d and rc.city_id=c.id';
        $table = array(
            'fx_city' => 'c',
            'fx_repairer_city' => 'rc'
        );
        $result = $this->table($table)
                        ->field($field)
                        ->where($where, $rid, $cid)
                        ->select();
        
        return $result;
        
    }
    //查询维修员维修设备
    public function get_repair_device($rid,$cid){
        $field = 'd.id,d.name';
        $where = 'rd.rid=%d and rd.cid=%d and rd.dev_id=d.id and d.status=1';
        $table = array(
            'fx_device' => 'd',
            'fx_repairer_device_temp' => 'rd'
        );
        $result = $this->table($table)
                        ->field($field)
                        ->where($where, $rid, $cid)
                        ->select();
        return $result;
        
        
    }
    //查询维修员总数
    public function select_repair_count(){
        $result = $this->where('status<>-1')->count();
        return $result;
    }
    //删除分组时把分组下面的成员移到默认分组下
    public function move_member_default($gid){
        //先查出此分组下所有成员ID
        $repair = $this->where('gid=%d', $gid)->select();
        foreach($repair as $re){
            $rid .= $re['id'].',';
        }
        $rid = rtrim($rid,',');
        //更新维修员组ID
        $where = array(
            'id' => array('in',$rid)
        );
        $result = $this->where($where)->setField('gid',null);
        return $result;
    }
    //审核维修员
    public function exmaRepairer($rid,$status,$user){
        $result = $this->where('id=%d', $rid)
                        ->setField(array(
                            'status' => $status,
                            'exam_id' => $user
                        ));
        return $result;
    }
    //保存维修注册信息
    public function keep_repairer($data,$openid){
        //先查询是否是存在的维修用户
        $is_repair = $this->where('openid="%s"',$openid)->find();
        if(empty($is_repair)){
            $data['openid'] = $openid;
            $repairID = $this->add($data);
            return $repairID;
        }else{
            $result = $this->where('id=%d',$is_repair['id'])->save($data);
            $result = $result?$is_repair['id']:'dev_temp';
            return $result;
        }
        
    }

    //根据sessionId获取维修员信息
    public function getRepairmanBySessionId($sessionId){
        return $this->table('fx_sys_repairer')->where(array('session_id' => $sessionId))->find();
    }

    public function addRepairer($data){
        return $this->table('fx_sys_repairer')->add($data);
    }

    public function saveRepairman($data){
        return $this->table('fx_sys_repairer')->save($data);
    }

    //通过openid检查维修员是否进行过授权
    public function checkExist($openid){
        return $this->table('fx_sys_repairer')->where(array('openid' => $openid))->find();
    }

    public function getRepairman($id){
        return $this->table('fx_sys_repairer')->where(array('id' => $id))->find();
    }

    /**
     * 根据维修公司和状态检查当前维修员
     * @param $id
     * @param $cmId
     * @param $status
     * @return mixed
     */
    public function checkRepairer($id,$cmId,$status){
        $where=[
            'id'=>$id,
            'cm_id'=>$cmId,
            'status'=>$status,
        ];
        $result=$this->where($where)->find();
        return $result;
    }

    /**
    * 根据公司id获取维修公司的维修管理员（客服）
    * @param int $compid
    */
    public function getCustomerServicer($compid){
        return $this->where(['cm_id' => $compid, 'user_type' => C('WXUSER_TYPE.REPAIR_MG')])->select();
    }

    /**
    * 根据手机号设置公司的管理员
    * @param string $phone 手机号
    * @param int $compid 公司id
    */
    public function setManager($data, $phone, $compid){
        return $this->where(['phone' => $phone, 'cm_id' => $compid])->save($data);
    }

    /**
    * 获取公司设置的管理员
    */
    public function getManagerByCompid($compid){
        return $this->where(['cm_id' => $compid, 'user_type' => ['neq', C('WXUSER_TYPE.USER')]])->select();
    }
}
