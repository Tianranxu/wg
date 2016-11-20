<?php
/*************************************************
 * 文件名：PersonModel.class.php
 * 功能：     素材管理模型
 * 日期：     2015.9.28
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;
use Emoji\emoji;

class PersonModel extends WeixinModel
{

    protected $tableName = 'weixin_user';

    /**
     * 获取用户基本信息（包括UnionID机制）
     * 
     * @param string $access_token
     *            调用接口凭证
     * @param string $openid
     *            普通用户的标识，对当前公众号唯一
     * @param string $lang
     *            返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语
     * @return boolean|Ambigous <boolean, mixed>
     */
    public function info($access_token, $openid, $lang = 'zh_CN')
    {
        // 请求url
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $access_token . '&openid=' . $openid . '&lang=' . $lang;
        
        // 调用接口
        $result = $this->http_get($url);
        if ($result->errcode)
            return false;
        return $result;
    }

    /**
     * 获取微信用户信息（无调用接口）
     * 
     * @param string $openid
     *            普通用户的标识，对当前公众号唯一
     * @return \Think\mixed
     */
    public function getUserInfo($openid, $cm_id)
    {
        $map = array(
            'openid' => $openid,
            'cm_id' => $cm_id
        );
        $result = $this->where($map)->find();
        vendor('Emoji.emoji');
        $emoji = new emoji();
        $result['nickname'] = $emoji->emoji_html_to_unified($result['nickname']);
        return $result;
    }

    /**
     * 获取微信用户信息
     * 
     * @param string $access_token
     *            调用接口凭证
     * @param string $cm_id
     *            企业ID
     * @param string $openid
     *            普通用户的标识，对当前公众号唯一
     * @return \Think\mixed|boolean
     */
    public function getWeixinUserInfo($access_token, $openid, $cm_id = '')
    {
        $result = $this->getUserInfo($openid, $cm_id);
        if (! $result) {
            // TODO 查询不到微信用户表中用户的信息，调用接口
            $info = $this->info($access_token, $openid);
            if ($info) {
                // 将接口返回的用户信息写入微信用户表
                $data = array(
                    'openid' => $info->openid,
                    'nickname' => $info->nickname,
                    'sex' => $info->sex,
                    'language' => $info->language,
                    'city' => $info->city,
                    'province' => $info->province,
                    'country' => $info->country,
                    'headimgurl' => $info->headimgurl,
                    'subscribe_time' => $info->subscribe_time,
                    'unionid' => $info->unionid,
                    'remark' => $info->remark,
                    'groupid' => $info->groupid,
                    'cm_id' => $cm_id
                );
                $this->startTrans();
                $addResult = $this->add($data);
                if ($addResult) {
                    $this->commit();
                    // 重新查询该用户的信息
                    $result = $this->getUserInfo($openid,$cm_id);
                    return $result;
                } else {
                    $this->rollback();
                    return false;
                }
            }
        }
        // TODO 查询到用户的信息，直接返回
        return $result;
    }

    /**
     * 完善用户个人信息
     * 
     * @param string $cm_id
     *            企业ID
     * @param string $openid
     *            普通用户的标识，对当前公众号唯一
     * @param string $nickname
     *            用户真实姓名
     * @param string $mobile
     *            用户手机号码
     * @return boolean
     */
    public function saveUserInfo($cm_id, $openid, $nickname, $mobile)
    {
        vendor('Emoji.emoji');
        $emoji = new emoji();
        $data = array(
            'nickname' => $emoji->emoji_unified_to_html($nickname),
            'mobile' => $mobile,
            'modify_time' => date('Y-m-d H:i:s')
        );
        $map = array(
            'openid' => $openid,
            'cm_id' => $cm_id
        );
        // 保存信息
        $result = $this->where($map)->save($data);
        if (! $result) {
            return false;
        }
        return true;
    }
}


