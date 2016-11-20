<?php
/*************************************************
 * 文件名：WxunpayModel.class.php
 * 功能：     微信待缴费模型
 * 日期：     2015.10.14
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class InviteCodeModel extends Model
{

    protected $tableName = 'invite_code';

    public function selectNumberOfCode($code) {
        $where = array(
            'code'=>$code,
            'expire_time' => array('gt', date('Ymd H:i:s'))
        );
        return $this->where($where)->count();
    }

    public function logo($domain) {
        $where = array(
            'domain' => $domain
        );
        return $this->field('logo')->where($where)->find()['logo'];
    }
}


