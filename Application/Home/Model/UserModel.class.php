<?php
/*************************************************
 * 文件名：UserModel.class.php
 * 功能：     用户管理模型
 * 日期：     2015.7.23
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class UserModel extends Model
{

    protected $tableName = 'sys_user';

    /**
     * 检查登陆的手机号码和密码是否正确
     *
     * @param string $phone
     *            手机号码
     * @param string $password
     *            固定密码或临时密码
     * @return array 数据数组
     */
    public function check_login($phone, $password)
    {
        $field = array(
            'id'
        );
        $where = array(
            'fixed_password' => $password,
            'dyn_password' => $password,
            '_logic' => 'or'
        );
        $map = array(
            '_complex' => $where,
            'code' => $phone,
            'status' => 1
        );
        $result = $this->field($field)
            ->where($map)
            ->find();
        return $result;
    }

    /**
     * 写入用最后登陆的ip地址和登陆时间
     *
     * @param string $code
     *            手机号码
     * @param string $ip
     *            最后登录ip地址
     * @return integer 状态码 NO_DATA-未接收到数据 OPERATION_FAIL-更新失败 OPERATION_SUCCESS-更新成功
     */
    public function upLastLoginIp($code, $ip)
    {
        $flag = C('NO_DATA');
        
        if ($code && $ip) {
            // 组装更新数据
            $data = array(
                'last_login_ip' => $ip,
                'last_login_time' => date('Y-m-d H:i:s', time())
            );
            
            // 记录最后登录ip地址和登陆时间，开启事务
            $where = array(
                'code' => $code
            );
            $result = $this->where($where)->save($data);
            if ($result) {
                // TODO 更新成功，提交事务
                $this->commit();
                $flag = C('OPERATION_SUCCESS');
                return $flag;
            } else {
                // TODO 更新失败，回滚事务
                $this->rollback();
                $flag = C('OPERATION_FAIL');
                return $flag;
            }
        } else {
            $flag = C('NO_DATA');
            return $flag;
        }
    }

    /**
     * 写入用户最新的PHPSESSID
     *
     * @param string $id
     *            用户ID
     * @param string $session_id
     *            PHPSESSID
     * @return boolean
     */
    public function updateSession($id, $session_id)
    {
        if ($id && $session_id) {
            $data = array(
                'session_id' => $session_id,
                'modify_time' => date('Y-m-d H:i:s')
            );
            // 更新PHPSESSID
            $where = array(
                'id' => $id
            );
            $this->startTrans();
            $result = $this->where($where)->save($data);
            if (! $result) {
                $this->rollback();
                return false;
            }
            $this->commit();
            return true;
        }
        return false;
    }

    /**
     * 根据cookie中的PHPSESSID查找用户
     *
     * @param string $session_id
     *            cookie中的PHPSESSID
     * @return \Think\mixed
     */
    public function find_user_by_session_id($session_id)
    {
        $map = array(
            'session_id' => $session_id
        );
        $result = $this->where($map)->getField('id');
        return $result;
    }

    /**
     * 清除用户session_id
     * @param $id       用户ID
     * @return bool
     */
    public function cleanSessionId($id)
    {
        $data = [
            'session_id' => null
        ];
        $where = [
            'id' => $id
        ];
        $result = $this->where($where)->save($data);
        if (!$result) return false;
        return true;
    }

    /**
     * 检查手机号码是否重复
     *
     * @param string $phone
     *            手机号码
     * @return array 数据数组
     */
    public function check_mobile($phone)
    {
        $field = array(
            'id'
        );
        $where = array(
            'code' => $phone
        );
        $result = $this->field($field)
            ->where($where)
            ->find();
        return $result;
    }

    /**
     * 添加注册信息
     *
     * @param string $code
     *            手机号码
     * @param string $dyn_password
     *            临时密码
     * @param string $reg_ip
     *            用户IP
     * @return integer 状态码 -1-没执行到写入操作 1-写入失败 2-写入成功
     */
    public function addReg($code, $dyn_password, $reg_ip)
    {
        $flag = array(
            - 1,
            null
        );
        
        // 组装添加的数据
        $data['name'] = $code;
        $data['code'] = $code;
        $data['dyn_password'] = $dyn_password;
        $data['sex'] = 1;
        $data['create_time'] = date('Y-m-d H:i:s', time());
        $data['reg_ip'] = $reg_ip;
        $data['sms_times'] = 1;
        
        // 将注册信息添加到用户表，开启事务
        $this->startTrans();
        $result = $this->add($data);
        if ($result) {
            // TODO 添加成功，为用户添加普通用户角色
            $role_data['user_id'] = $result;
            $role_data['role_id'] = DEFAULT_USER;
            // 实例化角色和用户中间表
            $user_roleModel = M('user_role_temp');
            $role_result = $user_roleModel->add($role_data);
            if ($role_result) {
                // TODO 权限添加成功，为用户添加默认分组
                $group_data['user_id'] = $result;
                $group_data['type'] = 1;
                $group_data['title'] = '默认分组';
                $group_data['description'] = '系统自带分组';
                $group_data['status'] = 1;
                $group_data['create_time'] = date('Y-m-d H:i:s', time());
                $groupModel = M('sys_group');
                $group_result = $groupModel->add($group_data);
                if ($group_result) {
                    // TODO 添加默认分组成功，添加停止服务企业群组
                    $stop_group_data['user_id'] = $result;
                    $stop_group_data['type'] = 3;
                    $stop_group_data['title'] = '停止服务企业';
                    $stop_group_data['description'] = '系统自带分组';
                    $stop_group_data['status'] = 1;
                    $stop_group_data['create_time'] = date('Y-m-d H:i:s', time());
                    $stop_group_result = $groupModel->add($stop_group_data);
                    if ($stop_group_result) {
                        // TODO 添加停止服务企业群组成功，提交事务
                        $this->commit();
                        $flag = array(
                            5,
                            $result
                        );
                        return $flag;
                    } else {
                        // TODO 添加停止服务企业群组失败，回滚事务
                        $this->rollback();
                        $flag = array(
                            4,
                            null
                        );
                        return $flag;
                    }
                } else {
                    // TODO 添加默认分组失败，回滚事务
                    $this->rollback();
                    $flag = array(
                        3,
                        null
                    );
                    return $flag;
                }
            } else {
                // TODO 权限添加失败，回滚事务
                $this->rollback();
                $flag = array(
                    2,
                    null
                );
                return $flag;
            }
        } else {
            // TODO 添加失败，回滚事务
            $this->rollback();
            $flag = array(
                1,
                null
            );
            return $flag;
        }
    }

    /**
     * 查询用户的固定密码或临时密码是否正确
     *
     * @param string $id
     *            用户ID
     * @param string $password
     *            固定密码或临时密码
     * @return number 状态码 -1-未接收到数据，无操作 1-密码不正确 2-密码正确
     */
    public function check_now_pass($id, $password)
    {
        $flag = - 1;
        
        if ($id && $password) {
            $field = array(
                'id'
            );
            $where = array(
                'fixed_password' => md5($password),
                'dyn_password' => md5($password),
                '_logic' => 'or'
            );
            $map = array(
                '_complex' => $where,
                'id' => $id
            );
            $result = $this->field($field)
                ->where($map)
                ->find();
            if ($result) {
                // TODO 用户的固定密码或临时密码正确
                $flag = 2;
                return $flag;
            } else {
                // TODO 密码不正确
                $flag = 1;
                return $flag;
            }
        } else {
            $flag = - 1;
            return $flag;
        }
    }

    /**
     * 修改用户固定密码，并清空临时密码
     *
     * @param string $id
     *            用户ID
     * @param string $fixed_password
     *            用户固定密码
     * @return number 状态码 -1-未接收到数据，无操作 1-更新失败 2-更新成功
     */
    public function change_user_pass($id, $password)
    {
        $flag = - 1;
        
        if ($id && $password) {
            // 组装更新数据
            $save_data['id'] = $id;
            $save_data['fixed_password'] = md5($password);
            $save_data['dyn_password'] = null;
            
            // TODO 修改固定密码，并清空临时密码，开启事务
            $this->startTrans();
            $save_result = $this->save($save_data);
            if ($save_result) {
                // TODO 更新成功，提交事务
                $this->commit();
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
     * 查询所有用户头像
     *
     * @return array 数据数组
     */
    public function get_user_face_list()
    {
        $field = array(
            'i.id',
            'i.url_address'
        );
        $where = array(
            'i.type' => 3
        );
        $table = array(
            'fx_sys_icon' => 'i'
        );
        $result = $this->table($table)
            ->field($field)
            ->where($where)
            ->select();
        return $result;
    }

    /**
     * 更新用户资料
     *
     * @param string $id
     *            用户ID
     * @param array $data
     *            更新的数据，必须为用户表的字段
     * @return number 状态码 -1-缺少用户ID未执行操作 1-缺少资料数组 2-更新失败 3-更新成功
     */
    public function edit_user_info($id, array $data)
    {
        $flag = - 1;
        
        if ($id) {
            if ($data) {
                // 组装更新数据，开启事务
                $data['modify_time'] = date('Y-m-d H:i:s', time());
                $this->startTrans();
                $result = $this->save($data);
                if ($result) {
                    // TODO 更新成功，提交事务
                    $this->commit();
                    $flag = 3;
                    return $flag;
                } else {
                    // TODO 更新失败，回滚事务
                    $this->rollback();
                    $flag = 2;
                    return $flag;
                }
            } else {
                // 数据并非数组或者为空
                $flag = 1;
                return $flag;
            }
        } else {
            $flag = - 1;
            return $flag;
        }
    }

    /**
     * 根据用户ID查找用户信息
     *
     * @param string $id
     *            用户ID
     * @return array 数据数组
     */
    public function find_user_info($id)
    {
        $where = ['id' => $id];
        $result = $this->where($where)->find();
        return $result;
    }

    /**
     * 重置用户临时密码
     *
     * @param string $id
     *            用户ID
     * @param string $code
     *            用户手机号码
     * @param string $dyn_password
     *            临时密码
     * @return number 状态码 -1-没更新用户表 1-更新失败 2-更新成功
     */
    public function resetTempPass($id, $code, $dyn_password)
    {
        $flag = - 1;
        
        // 组装添加数据
        $data['id'] = $id;
        $data['code'] = $code;
        $data['dyn_password'] = $dyn_password;
        $data['modify_time'] = date('Y-m-d H:i:s', time());
        
        // 将新的临时密码更新到用户表，开启事务
        $this->startTrans();
        $result = $this->save($data);
        if ($result) {
            // TODO 更新成功，提交事务
            $this->commit();
            $flag = 2;
            return $flag;
        } else {
            // TODO 更新失败，回滚事务
            $this->rollback();
            $flag = 1;
            return $flag;
        }
    }

    /**
     * 根据用户ID对当天发送短信次数递增一
     *
     * @param string $id
     *            用户ID
     * @return number 状态码 -1-未进行任何操作 1-超过短信发送次数 2-短信次数递增失败 3-短信次数递增成功
     */
    public function increase_sms_times($id)
    {
        $flag = - 1;
        
        // 判断该用户当天发送短信次数是否超过限制
        $timesResult = $this->check_sms_times($id);
        if ($timesResult[0] == 3) {
            // TODO 发送短信次数
            // 组装更新数据
            $data['id'] = $id;
            $data['sms_times'] = (int) $timesResult[1] + 1;
            $data['modify_time'] = date('Y-m-d H:i:s', time());
            // 开启事务
            $this->startTrans();
            $saveResult = $this->save($data);
            if ($saveResult) {
                // 提交事务，更新成功
                $this->commit();
                $flag = 3;
                return $flag;
            } else {
                // 更新失败，回滚事务
                $this->rollback();
                $flag = 2;
                return $flag;
            }
        } else {
            // TODO 超过发送次数限制
            $flag = 1;
            return $flag;
        }
    }

    /**
     * 根据用户ID查询该用户当天发送短信次数
     *
     * @param string $id
     *            用户ID
     * @return array 数据数组
     */
    public function check_sms_times($id)
    {
        $flag = array(
            - 1,
            null
        );
        
        $field = array(
            'id',
            'create_time',
            'modify_time',
            'sms_times'
        );
        $where = array(
            'id' => $id
        );
        $result = $this->field($field)
            ->where($where)
            ->find();
        
        if ($result) {
            // TODO 查询到该用户
            $currentTime = date('Y-m-d', time());
            // 判断该用户是否有修改时间
            if (! $result['modify_time']) {
                // 没修改时间，取创建时间
                $lastSendTime = date('Y-m-d', strtotime($result['create_time']));
            } else {
                // 修改时间存在，去修改时间
                $lastSendTime = date('Y-m-d', strtotime($result['modify_time']));
            }
            
            // 判断创建时间或修改时间是否为当天
            if ($lastSendTime == $currentTime) {
                // TODO 判断发送短信次数是否超过10次
                if ($result['sms_times'] >= 10) {
                    // 超过次数
                    $flag = array(
                        1,
                        null
                    );
                    return $flag;
                } else {
                    // 未超过次数
                    $flag = array(
                        3,
                        $result['sms_times']
                    );
                    return $flag;
                }
            } else {
                // TODO 不是当天，重置发送短信次数为0
                // 判断短信发送次数是否为0
                if ($result['sms_times'] == 0) {
                    // 更新成功
                    $flag = array(
                        3,
                        0
                    );
                    return $flag;
                }
                $data['id'] = $id;
                $data['sms_times'] = 0;
                $data['modify_time'] = date('Y-m-d H:i:s', time());
                // 开启事务
                $this->startTrans();
                $saveResult = $this->save($data);
                if ($saveResult) {
                    // 更新成功，提交事务
                    $this->commit();
                    $flag = array(
                        3,
                        0
                    );
                    return $flag;
                } else {
                    // 更新失败，回滚事务
                    $this->rollback();
                    $flag = array(
                        2,
                        null
                    );
                    return $flag;
                }
            }
        } else {
            // TODO 查询不到该用户
            $flag = - 1;
            return $flag;
        }
    }

    /**
     * 查找用户头像
     *
     * @param string $id
     *            用户ID
     * @return array 数据数组
     */
    public function find_user_photo($id)
    {
        $field = array(
            'i.id',
            'i.url_address'
        );
        $where = array(
            'i.id' => $id
        );
        $table = array(
            'fx_sys_icon' => 'i'
        );
        $result = $this->table($table)
            ->field($field)
            ->where($where)
            ->find();
        return $result;
    }

    /**
     * 更新用户头像
     *
     * @param string $id
     *            用户ID
     * @param string $photo
     *            用户头像ID
     * @return number 状态码 -1-未接收到数据，无操作 1-更新失败 2-更新成功
     */
    public function change_user_face($id, $photo)
    {
        $flag = - 1;
        
        if ($id && $photo) {
            // 组装更新数组
            $data['id'] = $id;
            $data['photo'] = $photo;
            
            // 更新用户头像，开启事务
            $this->startTrans();
            $result = $this->save($data);
            if ($result) {
                // TODO 更新成功，提交事务
                $this->commit();
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
     * 查询被邀请的人员信息
     *
     * @param string $id
     *            邀请人用户ID
     */
    public function selectInvite($id)
    {
        $field = array(
            'u.id',
            'u.name',
            'u.code',
            'u.Contact_number' => 'phone',
            'u.create_time' => 'cTime',
            'u.last_login_ip' => 'ip',
            'u.last_login_time' => 'logTime',
            'cm.name' => 'cName',
            'cm.status',
            'r.name' => 'rName'
        );
        $map['u.id']  = array(
            'in',$id
        );
        $table = array(
            'fx_sys_user' => 'u'
        );
        $result = $this->table($table)
            ->field($field)
            ->where($map)
            ->join('`fx_user_role_temp` as rt ON u.id=rt.user_id', 'LEFT')
            ->join('`fx_comp_manage` as `cm` ON rt.cm_id=cm.id', 'LEFT')
            ->join('`fx_sys_role` as `r` ON rt.role_id=r.id', 'LEFT')
            ->order('u.create_time desc')
            ->select();
        return $result;
    }

    /**
     * 根据手机号查询人员信息
     *
     * @param string $number
     *            用户手机号
     */
    public function selectUserInfo($number)
    {
        $field = array(
            'id',
            'name',
            'code',
            'create_time' => 'ctime',
            'last_login_time' => 'lotime'
        );
        $where = array(
            'code' => $number
        );
        $result = $this->field($field)
            ->where($where)
            ->find();
        return $result;
    }
    /**
     * 根据手机号查询用户是否已存在公司中
     * @param $phone       用户手机号
     * @param $cm_id       公司ID
     */
    public function userInCompany($phone, $cm_id)
    {
        $where = array(
            'u.code' => $phone,
            'u.id=gt.user_id',
            'gt.cm_id' => $cm_id
        );
        $table = array(
            'fx_comp_group_temp' => 'gt',
            'fx_sys_user' => 'u'
        );
        $is_company = $this->table($table)
            ->field('u.id,u.name,u.code,gt.group_id')
            ->where($where)
            ->find();
        return $is_company;
    }
    /**
     * 更新邀请人字段
     * @param $inviter     邀请人ID
     * @param $uid         被邀请人ID
     */
    public function updateInviter($uid, $inviter)
    {
        //防止以前的数据后面没跟 ','号
        $invite_per_id = $this->field('invite_per_id')->where('id=%d',$uid)->find();
        $invite_per_id = trim($invite_per_id['invite_per_id'], ',');
        $BeInvited = explode(',', $invite_per_id);
        if(in_array($inviter, $BeInvited) ){
            return true;
        }
        $invite_per_id = empty($invite_per_id)?',':','.$invite_per_id.',';
        $sql = "UPDATE `fx_sys_user` SET invite_per_id = concat('{$invite_per_id}', '{$inviter}".','."') WHERE id = {$uid}";
        $result = $this->execute($sql);
        return $result;
    }
    /**
     * 更新邀请人字段
     * @param $uid       邀请人ID
     */
    public function belongToMe($uid)
    {
        $map['invite_per_id'] = array('like',"%,{$uid},%");
        $result = $this->field('id')->where($map)->select();
        return $result;
    }

    //通过用户id或者id的数组获取用户信息
    public function getUserById($ids){
        return $this->where(['id' => ['IN', $ids]])->select();
    }   

    public function getOneUser($id){
        return $this->where(['id' => $id])->find();
    }
}


