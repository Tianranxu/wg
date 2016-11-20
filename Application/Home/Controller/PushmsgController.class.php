<?php
/*************************************************
 * 文件名：PushmsgController.class.php
 * 功能：     消息推送管理控制器
 * 日期：     2016.02.22
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;

use Think\Controller;

class PushmsgController extends Controller
{

    protected $noticeModel;

    public function _initialize()
    {
        $this->noticeModel = D('notice');
    }

    /**
     * 检测用户是否登录
     * @return bool|mixed|string
     */
    public function checkUserLogin()
    {
        if (session('user_id')) return session('user_id');
        //查询用户是否存在
        $userModel = D('user');
        $userId = $userModel->find_user_by_session_id(cookie('PHPSESSID'));
        if ($userId) return $userId;
        return false;
    }

    /**
     * 消息推送入口
     */
    public function index()
    {
        header("Content-Type: text/event-stream\n\n");
        header("Cache-Control: no-cache\n\n");

        $lists = $this->getNoticeLists();
        echo "data: " . json_encode($lists) . "\n\n";
    }

    /**
     * 获取消息通知列表
     * @param string $compid 企业ID
     * @return array
     */
    public function getNoticeLists($compid = '')
    {
        $userId = $this->checkUserLogin();
        if (!$userId) exit;
        $lists = $this->noticeModel->getNoticeLists($userId, $compid);
        //dd($lists);
        return $lists;
    }

    /**
     * 更新微信用户缴费消息通知状态
     */
    public function changeWxPayNoticeStatus()
    {
        $compid = I('get.compid', '');
        $type = I('post.type', '');
        $status = I('post.status', 1);
        if (!$type) retMessage(false, null, '参数异常，请检查参数', '参数异常，请检查参数', 4001);
        $result = $this->noticeModel->updateNoticeStatus($compid, $type, $status);
        $result ? retMessage(true, null) : retMessage(false, null, '状态更新失败', '状态更新失败', 4002);
    }

    /**
     * 更新消息通知状态
     * @param int $cmId 企业ID
     * @param int $type 消息类型，参考C('NOTICE_TYPE')
     * @param int $otherId 其他ID 比如工单ID，表单ID，
     * @return bool
     */
    public function readNotices($cmId, $type, $otherId)
    {
        if ($type == C('NOTICE_TYPE')[1]['type'] && (M('feedback_response')->where(['fid' => $otherId])->count()) > 1) return true;
        $result = $this->noticeModel->updateNoticeStatus($cmId, $type, 1, $otherId);
        return $result ? true : false;
    }

    /**
     * 发送微信物业缴费消息到PC后端给相关人员
     * @param int $cmId 企业ID
     * @param string $type 缴费类型 1-物业费，2-车费
     * @param string $otherId 其他ID 默认为空
     * @return bool
     */
    public function sendNoticesToPcAdministors($cmId, $type, $otherId='')
    {
        //查找有权限进入收支明细的人员
        $roleModel = D('role');
        $roleLists = $roleModel->allRole();
        $roleIds = [];
        $ruleId = $this->getRuleByNoticeType($type);
        foreach ($roleLists as $roleList) {
            if (in_array($ruleId, explode(',', $roleList['rule_id']))) $roleIds[] = $roleList['id'];
        }
        $roleTempModel = D('roletemp');
        //根据权限获取需要发送消息的用户列表
        $userLists = $roleTempModel->getUsersByCompAndRole([$cmId], $roleIds);
        //添加消息通知记录
        $datas = $this->getSendDatas($userLists, $cmId, $type, $otherId);
        $noticeModel = D('notice');
        $result = $noticeModel->addNotices($datas);
        return $result ? true : false;
    }

    /**
     * 根据消息发送类型获取相应的权限ID
     * @param string $type 消息发送类型，参考C('NOTICE_SEND_TYPE')
     * @return string
     */
    public function getRuleByNoticeType($type)
    {
        $ruleId = '';
        foreach (C('NOTICE_SEND_TYPE') as $sendType) {
            if ($type == $sendType['name']) $ruleId = $sendType['value'];
        }
        return $ruleId;
    }

    /**
     * 获取添加消息通知的数据
     * @param array $userLists 用户列表
     * @param int $cmId 企业ID
     * @param string $type 发送类型，参考C('NOTICE_SEND_TYPE')
     * @param string $otherId 其他ID
     * @return array
     */
    public function getSendDatas(array $userLists, $cmId, $type, $otherId='')
    {
        $datas = [];
        foreach ($userLists as $user => $userList) {
            foreach (C('NOTICE_SEND_TYPE') as $sendType => $typeRule) {
                if ($type == $typeRule['name']) {
                    $datas[$user]['content'] = C('NOTICE_TYPE')[$sendType - 1]['content'];
                    $datas[$user]['type'] = C('NOTICE_TYPE')[$sendType - 1]['type'];
                    if($sendType == 3){
                        $getParam = explode(',', $otherId);
                        $data = $getParam[1];
                        $id = $getParam[0];
                        $datas[$user]['url'] = C('NOTICE_TYPE')[$sendType - 1]['url'] . $cmId.'&data='.$data.'&id='.$id;
                    }else{
                        $datas[$user]['url'] = C('NOTICE_TYPE')[$sendType - 1]['url'] . $cmId;
                    }

                    break;
                }
            }
            $datas[$user]['cm_id'] = $cmId;
            $datas[$user]['other_id'] = $otherId;
            $datas[$user]['user_id'] = $userList['user_id'];
            $datas[$user]['status'] = -1;
            $datas[$user]['update_time'] = date('Y-m-d H:i:s');
        }
        return $datas;
    }
}