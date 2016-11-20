<?php
/*************************************************
 * 文件名：NoticeModel.class.php
 * 功能：     PC端消息通知模型
 * 日期：     2016.02.23
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class NoticeModel extends Model
{

    protected $tableName = 'sys_notice';

    /**
     * 获取消息通知列表
     * @param int $userId 用户ID
     * @param string $cmId 企业ID
     * @param int $status 状态  -1-未阅读或未处理，1-已阅读或已处理
     * @return array
     */
    public function getNoticeLists($userId, $cmId = '', $status = -1)
    {
        $field = ['n.id', 'n.content', 'n.type', 'cm.id' => 'cm_id', 'cm.name' => 'cm_name', 'cm.cm_type' => 'cm_type', 'n.url', 'n.user_id', 'n.status'];
        $table = ['fx_sys_notice' => 'n', 'fx_comp_manage' => 'cm'];
        $where = ['n.user_id' => $userId, 'n.status' => $status, 'n.cm_id=cm.id'];
        if ($cmId) $where['cm.id'] = $cmId;
        $noticeLists = $this->handleNoticeLists($this->table($table)->field($field)->where($where)->order('n.type')->select());
        return $noticeLists;
    }

    /**
     * 组装消息通知列表数据
     * @param array $lists 消息通知数据
     * @return mixed
     */
    public function handleNoticeLists(array $lists)
    {
        $noticeLists = ['lists' => [], 'total' => 0];
        foreach ($lists as $li => $list) {
            $noticeLists['lists'][$list['cm_id']]['total'] = $noticeLists['lists'][$list['cm_id']]['total'] ? $noticeLists['lists'][$list['cm_id']]['total'] : 0;
            $noticeLists['lists'][$list['cm_id']]['cm_id'] = $list['cm_id'];
            $noticeLists['lists'][$list['cm_id']]['cm_name'] = $list['cm_name'];
            $noticeLists['lists'][$list['cm_id']]['cm_type'] = $list['cm_type'];
            foreach (C('NOTICE_TYPE') as $type) {
                $noticeLists['lists'][$list['cm_id']][$type['name']]['list'] = $noticeLists['lists'][$list['cm_id']][$type['name']]['list'] ? $noticeLists['lists'][$list['cm_id']][$type['name']]['list'] : [];
                $noticeLists['lists'][$list['cm_id']][$type['name']]['total'] = $noticeLists['lists'][$list['cm_id']][$type['name']]['total'] ? $noticeLists['lists'][$list['cm_id']][$type['name']]['total'] : 0;
                $noticeLists['lists'][$list['cm_id']][$type['name']]['url'] = $type['url'] . $list['cm_id'];
                if ($type['type'] == $list['type']) {
                    $noticeLists['lists'][$list['cm_id']]['total']++;
                    $noticeLists['lists'][$list['cm_id']][$type['name']]['list'][] = $list;
                    $noticeLists['lists'][$list['cm_id']][$type['name']]['total'] = count($noticeLists['lists'][$list['cm_id']][$type['name']]['list']);
                    $noticeLists['total']++;
                }
            }
        }
        $noticeLists['lists'] = array_values($noticeLists['lists']);
        return $noticeLists;
    }

    /**
     * 添加消息通知记录
     * @param array $datas 添加的数据
     * @return bool
     */
    public function addNotices(array $datas)
    {
        $result = $this->addAll($datas);
        return $result ? true : false;
    }

    /**
     * 更新消息通知状态
     * @param int $cmId 企业ID
     * @param int $type 消息类型 1-超时未接故障，2-意见反馈，3-待审核表单，4-微信用户缴费
     * @param int $status 状态 -1未阅读或未处理，1-已阅读或已处理
     * @param int $otherId 其他ID
     * @return bool
     */
    public function updateNoticeStatus($cmId = '', $type, $status, $otherId = '')
    {
        $data = ['status' => $status, 'update_time' => date('Y-m-d H:i:s')];
        $where = ['type' => $type];
        if ($cmId) $where['cm_id'] = $cmId;
        if ($otherId) $where['other_id'] = $otherId;
        $result = $this->where($where)->save($data);
        return $result ? true : false;
    }
}