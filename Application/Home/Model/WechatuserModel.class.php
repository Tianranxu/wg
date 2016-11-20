<?php
/*************************************************
 * 文件名：WechatuserModel.class.php
 * 功能：    用户分组模型
 * 日期：     2015.7.23
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;
use Think\Model;
class WechatuserModel extends WeixinModel{    
    /**
     * 读redis
     * @param  $groupid     组ID
     * @param  $compid      企业id
     * @param  $star      开始数据流水号
     * @param  $end       一次拉取多少个数据记录
     */
    public function readRedis($compid, $star=0, $groupid='*', $end=9)
    {
        $redis=$this->connectRedis();
        $condition = $star+$end;
        $value = array();
        for($i=$star;$i<=$condition;$i++){

            if($groupid=='*'||$groupid==''){
                $keys = $redis->keys("wechatuser:{$compid}:{$i}:*:*:*");
                $key = $keys[0];
            }else{
                $keys = $redis->keys("wechatuser:{$compid}:*:{$groupid}:{$i}:*");
                $key = $keys[0];
            }
            $val = json_decode($redis->get($key));
            if(empty($val)){return $value;}
            $value[] = $val;
        }
        $this->disConnectRedis();
        return $value;
    }
    /**
     * 移动用户到某个分组(redis端操作)
     * @param  $compid      公司 ID
     * @param  $groupid     当前分组id
     * @param  $tar_group   目标分组ID
     * @param  $openid      用户openID
     */
    public function moveRedis($compid, $groupid, $tar_group, $openid)
    {
        $redis = $this->connectRedis();
        //当前key
        $curr_key = $redis->keys("wechatuser:{$compid}:*:{$groupid}:*:{$openid}");
        //拆分当前key
        $field = explode(':',$curr_key[0]);
        //目标key
        $keys = $redis->keys("wechatuser:{$compid}:*:{$tar_group}:*:*");
        $count = count($keys);
        $target = "wechatuser:{$compid}:{$field[2]}:{$tar_group}:{$count}:{$openid}" ;
        //移动(重命名)
        $c_move = $redis->renamenx($curr_key[0], $target);
        $this->upDataRedis($redis, $target, 'groupid', $tar_group);
        //查询当前分组最后一个key的分组流水号
        $curr_count = count($redis->keys("wechatuser:{$compid}:*:{$groupid}:*:*"));
        $curr_last_gl_number = $redis->keys("wechatuser:{$compid}:*:{$groupid}:{$curr_count}:*");
     //把当前分组最后一个key重命名为当前key
        //把当前组最后一个key拆成数组
        $keyArr = explode(':',$curr_last_gl_number[0]);
        //组成要替换的键
        $curr_key = "wechatuser:{$compid}:{$keyArr[2]}:{$groupid}:{$field[4]}:{$keyArr[5]}";
        //重命名
        $t_move = $redis->renamenx($curr_last_gl_number[0], $curr_key);
        //关闭redis
        $this->disConnectRedis();
        $result = $c_move==1&&$t_move==1?true:false;
        return $result;
    }
    /**
     *修改redis某个值
     * @param $redis redis数据库对象
     * @param string $key 要修改的键名
     * @param string $obj 要修改值的名称
     * @param string $val 要修改的值
     */
    public function upDataRedis($redis, $key, $obj, $val){
        $target = json_decode($redis->get($key));
        $target->$obj = $val;
        $redisValue = json_encode($target);
        $result = $redis->set($key, $redisValue);
        return $result;
    }

    /**
     * 移动用户到某个分组(微信端操作)
     * @param string $accessToken     公众号第三方的全局唯一票据
     * @param string $openid          用户openid
     * @param string $gid             分组id
     */
    public function moveToGroup($accessToken, $compid, $openid, $gid)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token={$accessToken}";
        $parameter = '{"openid":"'.$openid.'","to_groupid":'.$gid.'}';
        $result = $this->http_post($url,$parameter);
        $res = json_decode($result);
        return $res;
    }
    /**
     * 设置备注(微信端)
     * @param string $accessToken     公众号第三方的全局唯一票据
     * @param string $openid          用户openid
     * @param string $remark          备注内容
     */
    public function setRemark($accessToken, $openid, $remark)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/user/info/updateremark?access_token={$accessToken}";
        $parameter = '{"openid":"'.$openid.'","remark":"'.$remark.'"}';
        $result = $this->http_post($url,$parameter);
        return $result;
    }
    /**
     * 设置备注(redis)
     * @param string $compid          公司ID
     * @param string $remark          备注内容
     *  @param string $gid          分组ID
     * @param  $openid           用户openID
     */
    public function setRemarkRedis($compid, $remark, $gid, $openid)
    {
        $redis = $this->connectRedis();
        //要设置的键名
        $key = $redis->keys("wechatuser:{$compid}:*:{$gid}:*:{$openid}");
        $val = json_decode($redis->get($key[0]));
        $val->remark = $remark;
        $val = json_encode($val);
        $result = $redis->set($key[0],$val);
        $this->disConnectRedis();
        return $result;
    }
    /**
     * 删除某个分组(微信端)
     * @param string $accessToken     公众号第三方的全局唯一票据
     * @param string $gid             分组id
     */
    public function deleteGroup($accessToken, $gid)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/groups/delete?access_token={$accessToken}";
        $parameter = '{"group":{"id":'.$gid.'}}';
        $result = $this->http_post($url,$parameter);
        return $result;
    }
    /**
     * 删除某个分组(redis)
     * @param array $gid      分组ID
     * @param int  $compid     企业ID
     */
    public function delRedisGroup($compid,$gid)
    {
        $redis=$this->connectRedis();
        //读出此分组下所有键
        $keys = $redis->keys("wechatuser:{$compid}:*:{$gid}:*:*");
        //查出默认分组最后一个流水号
        $def_group_number = count($redis->keys("wechatuser:{$compid}:*:0:*:*"));
        //删除记数
        $count = true;
        //把此分组下的用户移入默认分组下
        //开启事务
        $redis->multi();
        foreach($keys as $val){
            $field = explode(':',$val);
            $target = "wechatuser:{$compid}:{$field[2]}:0:{$def_group_number}:{$field[5]}";
            $del = $redis->renamenx($val, $target);
            //更新值（groupid）
            $this->upDataRedis($redis, $target, 'groupid', 0);
            $def_group_number++;
            if($del!=1){
                $count = false;
                break;
            }
        }
        if($count){
            $redis->exec();
            return true;
        }else{
            $redis->discard();
            return false;
        }

    }

    /**
     * 查询微信公众平台所有用户分组列表
     * @param string $accessToken     是公众号第三方的全局唯一票据
     */
    public function getGroupList($accessToken)
    {
         $url = "https://api.weixin.qq.com/cgi-bin/groups/get?access_token={$accessToken}";
         $result = $this->http_get($url);
         return $result;
    }
    /**
     * 创建用户分组
     * @param string $accessToken     公众号第三方的全局唯一票据
     * @param string $groupName       分组名
     */
    public function createGroup($accessToken, $groupName)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/groups/create?access_token={$accessToken}";
        $parameter = '{"group":{"name":"'.$groupName.'"}}';
        $result = $this->http_post($url,$parameter);
        return $result;
    }
    /**
     * 查询所有用户分组
     * @param string $accessToken     公众号第三方的全局唯一票据
     */
    public function selectAllGroup($accessToken)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/groups/get?access_token={$accessToken}";
        $result = $this->http_get($url);
        return $result;
    }
    /**
     * 查询用户所在分组
     * @param string $accessToken     公众号第三方的全局唯一票据
     * @param string $openid          用户OPENID
     */
    public function selectInGroup($accessToken, $openid)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/groups/getid?access_token={$accessToken}";
        $parameter = '{"openid":"'.$openid.'"}';
        $result = $this->http_post($url,$parameter);
        return $result;
    }
    /**
     * 修改分组名
     * @param string $accessToken     公众号第三方的全局唯一票据
     * @param string $gid             修改的分组id
     * @param string $gName           新的分组名
     */
    public function modifyGroupName($accessToken, $gid, $gName)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/groups/update?access_token={$accessToken}";
        $parameter = '{"group":{"id":'.$gid.',"name":"'.$gName.'"}}';
        $result = $this->http_post($url,$parameter);
        return $result;
    }

    /**
     * 用户排序
     * @param string $allUsers         所有用户
     */
    public function usersSort($allUsers)
    {
        $time_stamp = array();
        foreach ($allUsers as $temp_) {
            $time_stamp[] = $temp_['subscribe_time'];
        }
        array_multisort($time_stamp, SORT_DESC, $allUsers); //先按关注时间排序
        usort($allUsers, function($a, $b) {                    //再按自定义排序
            $sort_a = $a['groupid'];
            $sort_b = $b['groupid'];
            if($sort_a<$sort_b){
                return -1;
            }elseif($sort_a>$sort_b){
                return 1;
            }else{
                return 0;
            }
        });
        return $allUsers;
    }
    /**
     * 获取微信服务器推送component_verify_ticket
     * @param string $ticket_name        存入到redis名字
     */
    public function get_ticket($ticket_name='weixin:ticket'){
        $redis = $this->connectRedis();
        $result=  $redis->get($ticket_name);
        return $result;
    }


    //获取access_token(测试)
    public function refresh_token(){
        $component_appid = 'wx717ca718b86bf895';
        $component_appsecret = '0c79e1fa963cd80cc0be99b20a18faeb';
        $component_access_token = $this->get_component_access_token($component_appid,$component_appsecret)->component_access_token;
        $appid = 'wxc82ddd239a1fde04';
        $refresh_token = 'refreshtoken@@@OculWlFqha69_eSu1Ldg44uShiDCt0Yk8FOiZe2UAuw';
        // dump($component_access_token);exit;
        $token = $this->refresh_access_token($appid,$refresh_token,$component_appid,$component_access_token);
        dump($token);exit;
    }
    
    //保存微信用户信息
    public function saveWxuser($data){
        return $this->table('fx_weixin_user')->save($data);
    }
    
    //获取微信用户信息
    public function getUser($session_id,$compid){
        return $this->table('fx_weixin_user')->where(array('session_id' => $session_id , 'cm_id' => $compid))->find();
    }
    //用openid获取用户信息
    public function getUserByOpenid($openid){
        return $this->table('fx_weixin_user')->where(array('openid' => $openid))->find();
    }
    //添加用户
    public function addUser($data){
        return $this->table('fx_weixin_user')->add($data);
    }

}