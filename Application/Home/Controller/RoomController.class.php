<?php
/*************************************************
 * 文件名：RoomController.class.php
 * 功能：     房源管理控制器
 * 日期：     2016.01.18
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;

class RoomController extends AccessController
{

    protected $roomModel;

    /**
     * 初始化
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->roomModel = D('room');
    }

    /**
     * 房源信息页面
     */
    public function index()
    {
        $page = I('get.page', 1);
        $isAjax = I('get.isAjax', '');
        //获取房源列表
        $search = ['cc_id' => I('get.community', ''), 'number' => I('get.number', ''), 'type' => I('get.typeDemand', ''), 'room_type' => I('get.roomType', ''), 'furnish_type' => I('get.furnishType', ''), 'follow_type' => I('get.followType', ''), 'status' => I('get.status', ''), 'expire' => I('get.expire')];
        $roomLists = $this->roomModel->getRoomLists($this->companyID, $search);
        $arr = $this->arrangeRoomLists($roomLists, $page);
        if ($isAjax) {
            retMessage(true, ['lists' => $arr['lists'], 'statistics' => $arr['statistics'], 'totalPages' => $arr['totalPages']]);
            exit;
        }
        //获取楼盘列表
        $propertCommon = A('propertycommon');
        $ccLists = $propertCommon->getCommunityLists($this->companyID);
        //获取用户信息
        $userModel = D('user');
        $userInfo = $userModel->find_user_info($this->userID);
        //查询该企业房源预警天数
        $warningModel=D('warning');
        $warningDays=$warningModel->getWarning($this->companyID,3)['days'];
        $this->assign('page', $page);
        $this->assign('ccLists', $ccLists);
        $this->assign('roomLists', $arr['lists']);
        $this->assign('statistics', $arr['statistics']);
        $this->assign('totalPages', $arr['totalPages']);
        $this->assign('userInfo', $userInfo);
        $this->assign('warningDays',$warningDays);
        $this->display();
    }

    /**
     * 整理房间列表数据
     * @param array $roomLists 房源列表
     * @param int $page 第几页，默认为1
     * @return array
     */
    public function arrangeRoomLists(array $roomLists, $page = 1)
    {
        $statistics = ['totalCounts' => count($roomLists), 'hasRentCounts' => 0, 'unRentCounts' => 0, 'stopRentCounts' => 0, 'totalTrusteeFee' => number_format(0, 2), 'averageTrusteeFee' => number_format(0, 2)];
        foreach ($roomLists as $rm => $roomList) {
            $roomLists[$rm]['type'] = C('TYPE_DEMAND')[$roomList['type']];
            $roomLists[$rm]['room_type'] = C('ROOM_TYPE')[$roomList['room_type']];
            $roomLists[$rm]['furnish_type'] = C('FURNISH_TYPE')[$roomList['furnish_type']];
            $roomLists[$rm]['follow_type'] = C('FOLLOW_TYPE')[$roomList['follow_type']];
            $roomLists[$rm]['sign_time'] = date('Y-m-d',strtotime($roomList['sign_time']));
            $roomLists[$rm]['end_time'] = date('Y-m-d',strtotime($roomList['end_time']));
            $roomLists[$rm]['status_name'] = C('ROOM_STATUS')[$roomList['status']];
            if ($roomList['status'] == 1) $statistics['unRentCounts']++;
            if ($roomList['status'] == 2) $statistics['hasRentCounts']++;
            if ($roomList['status'] == 3) $statistics['stopRentCounts']++;
            $statistics['totalTrusteeFee'] = floatval($statistics['totalTrusteeFee']) + floatval($roomList['total_trustee_fee']);
        }
        $statistics['totalTrusteeFee'] = number_format($statistics['totalTrusteeFee'], 2, '.', '');
        $statistics['averageTrusteeFee'] = number_format($statistics['totalTrusteeFee'] / $statistics['totalCounts'], 2, '.', '');
        $roomLists = array_slice($roomLists, (($page - 1) * 10), 10);
        $totalPages = ceil($statistics['totalCounts'] / 10);
        return ['lists' => $roomLists, 'statistics' => $statistics, 'totalPages' => $totalPages];
    }

    /**
     * 登记房源页面
     */
    public function addroom()
    {
        //查询楼盘列表
        $propertyCommon = A('propertycommon');
        $ccLists = $propertyCommon->getCommunityLists($this->companyID);
        //获取配套设施列表
        $furnitureModel = D('furniture');
        $furnitureLists = $furnitureModel->getFurnitureLists();
        //查询当前操作人
        $userModel = D('user');
        $userInfo = $userModel->find_user_info($this->userID);

        $this->assign('ccLists', $ccLists);
        $this->assign('furnitureLists', $furnitureLists);
        $this->assign('userInfo', $userInfo);
        $this->display();
    }

    /**
     * 登记房源
     */
    public function doAddRoom()
    {
        //接收数据
        $ccId = I('post.ccId', '');
        $bmId = I('post.bmId', '');
        $hmId = I('post.hmId', '');
        $furniture=I('post.furnitureArr', []);sort($furniture);
        $datas = [
            'cm_id' => $this->companyID, 'parent_id' => "{$this->companyID}-{$ccId}-{$bmId}-{$hmId}", 'hm_id' => $hmId,
            'type' => I('post.typeDemand', 1), 'room_type' => I('post.roomType', 1), 'furnish_type' => I('post.furnishType', 2), 'follow_type' => I('post.follow_type', 1),
            'status' => 1,
            'start_time' => I('post.startTime', date('Y-m-d')), 'limit' => I('post.totalMonths', 1), 'sign_time' => I('post.signTime', date('Y-m-d')), 'end_time' => I('post.endTime', ''),
            'trustee_fee' => I('post.trusteeFee', number_format(0, 2)), 'total_trustee_fee' => I('post.totalTrusteeFee', number_format(0, 2)), 'deposit' => I('post.deposit', number_format(0, 2)), 'rent' => I('post.rent', number_format(0, 2)),
            'furniture' => implode(',', $furniture),
            'is_increase' => I('post.is_increase', -1), 'increase_type' => I('post.increase_type', null), 'increase_price' => I('post.increase_price', null), 'increasing_cycle' => I('post.increasing_cycle', null),
            'uid' => I('post.uid', ''),
            'remark' => I('post.remark', ''),
            'update_time' => date('Y-m-d H:i:s'),
            'pictures' => I('post.pictureArr', []),
        ];
        if (!($this->filterAddRoomDatas($ccId, $bmId, $datas))) {
            retMessage(false, null, '参数错误，请检查参数', '参数错误，请检查参数', 4001);
            exit;
        }
        if((strtotime($datas['end_time']))<=(time())) $datas['status']=3;
        $result = $this->roomModel->addRoom($datas);
        $result ? retMessage(true, null) : retMessage(false, null, '登记房源失败！', '登记房源失败！', 4002);
        exit;
    }

    /**
     * 过滤登记房源数据
     * @param int $ccId 楼盘ID
     * @param int $bmId 楼宇ID
     * @param array $datas 数据数组
     * @return bool
     */
    public function filterAddRoomDatas($ccId, $bmId, array $datas)
    {
        if (!$datas['cm_id'] || !$ccId || !$bmId || !$datas['hm_id'] || !$datas['end_time'] || !$datas['uid']) return false;
        if (($datas['is_increase'] == 1) && (!$datas['increase_type'] || !$datas['increase_price'] || !$datas['increasing_cycle'])) return false;
        return true;
    }

    /**
     * 获取房源跟进
     */
    public function getFollows()
    {
        $id = I('post.id', '');
        $type = I('post.type', '');
        $page = I('get.page', 1);
        if (!$this->companyID || !$id || !$type) {
            retMessage(false, null, '参数错误，请检查参数', '参数错误，请检查参数', 4001);
            exit;
        }
        $followModel = D('follow');
        $lists = $followModel->getFollow($id, $type);
        $result = ['lists' => array_slice($lists, ($page - 1) * 5, 5), 'total' => count($lists), 'totalPages' => ceil(count($lists) / 5)];
        $lists ? retMessage(true, $result) : retMessage(false, $result, '查询不到相关记录', '查询不到相关记录', 4002);
        exit;
    }

    /**
     * 添加房源跟进信息
     */
    public function addFollow()
    {
        $id = I('post.id', '');
        $msg = I('post.msg', '');
        if (!$id || !$msg) {
            retMessage(false, null, '参数错误，请检查参数', '参数错误，请检查参数', 4001);
            exit;
        }
        $followModel = D('follow');
        $data = ['rs_id' => $id, 'msg' => $msg, 'uid' => $this->userID, 'create_time' => date('Y-m-d H:i:s')];
        $result = $followModel->addFollow($data);
        $result ? retMessage(true, null) : retMessage(false, null, '添加跟进信息失败', '添加跟进信息失败', 4002);
        exit;
    }

    /**
     * 匹配客源
     */
    public function getMatchs()
    {
        $id = I('post.id', '');
        $page = I('get.page', 1);
        if (!$this->companyID || !$id) {
            retMessage(false, null, '参数错误，请检查参数', '参数错误，请检查参数', 4001);
            exit;
        }
        //查询房源信息
        $roomInfo = $this->roomModel->getRoomInfo($id);
        $customerSourceModel = D('customersource');
        $lists = $customerSourceModel->matchCustomers($this->companyID,$roomInfo);
        foreach ($lists as $li => $list) {
            $lists[$li]['cc_name']=$list['cc_name']?$lists[$li]['cc_name']:'不限';
            $lists[$li]['room_type']=$list['room_type']?C('ROOM_TYPE')[$list['room_type']]:'不限';
            $lists[$li]['type']=$list['type']?C('TYPE_DEMAND')[$list['type']]:'不限';
            $lists[$li]['furnish_type']=$list['furnish_type']?C('FURNISH_TYPE')[$list['furnish_type']]:'不限';
        }
        $result = ['lists' => array_slice($lists, ($page - 1) * 5, 5), 'total' => count($lists), 'totalPages' => ceil(count($lists) / 5)];
        $lists ? retMessage(true, $result) : retMessage(false, $result, '查找不到记录', '查找不到记录', 4002);
        exit;
    }

    /**
     * 房源详情页面
     */
    public function detail()
    {
        $id = I('get.id', '');
        $furnitureModel = D('furniture');
        $furnitureLists = $furnitureModel->getFurnitureLists();
        $roompicModel = D('roompic');
        $pictureLists = $this->arrangePictures($roompicModel->getPictureByRoomId($id));
        $lists = $this->arrangeRoomInfo($this->roomModel->getRoomInfo($id), $furnitureLists);

        $this->assign('furnitureLists', $lists['furniture']);
        $this->assign('roomInfo', $lists['info']);
        $this->assign('pictureLists', $pictureLists);
        $this->display();
    }

    /**
     * 整理房源详情数据
     * @param array $roomInfo 房源信息
     * @param array $furnitureLists 配套设施列表
     * @return array
     */
    public function arrangeRoomInfo(array $roomInfo, array $furnitureLists)
    {
        $propertyCommon = A('propertycommon');
        //获取楼盘所属地
        $roomInfo['address'] = $propertyCommon->getCommunityInfo($roomInfo['cc_id'])['address'];
        $roomInfo['type'] = C('TYPE_DEMAND')[$roomInfo['type']];
        $roomInfo['room_type'] = C('ROOM_TYPE')[$roomInfo['room_type']];
        $roomInfo['furnish_type'] = C('FURNISH_TYPE')[$roomInfo['furnish_type']];
        $roomInfo['follow_type_name'] = C('FOLLOW_TYPE')[$roomInfo['follow_type']];
        $roomInfo['status_name'] = C('ROOM_STATUS')[$roomInfo['status']];
        $roomInfo['start_time'] = date('Y-m-d', strtotime($roomInfo['start_time']));
        $roomInfo['end_time'] = date('Y-m-d', strtotime($roomInfo['end_time']));
        $roomInfo['sign_time'] = date('Y-m-d', strtotime($roomInfo['sign_time']));
        $roomInfo['furniture_arr'] = explode(',', $roomInfo['furniture']);
        if (($roomInfo['limit'] / 12) < 1) {
            $roomInfo['limit_year'] = 0;
            $roomInfo['limit_month'] = $roomInfo['limit'];
        } else {
            $roomInfo['limit_year'] = ceil($roomInfo['limit'] / 12);
            $roomInfo['limit_month'] = $roomInfo['limit'] % 12;
        }
        //获取房源所属房间信息
        $roomInfo['hm_info'] = $propertyCommon->getHouseInfo($roomInfo['hm_id']);
        foreach ($furnitureLists as $f => $furnitureList) {
            $furnitureLists[$f]['checked'] = -1;
            foreach ($roomInfo['furniture_arr'] as $fur) {
                if ($fur == $furnitureList['id']) {
                    $furnitureLists[$f]['checked'] = 1;
                    continue;
                }
            }
        }
        return ['info' => $roomInfo, 'furniture' => $furnitureLists];
    }

    /**
     * 整理房源附件
     * @param array $pictureLists 房源附件列表
     * @return array
     */
    public function arrangePictures(array $pictureLists)
    {
        foreach ($pictureLists as $p => $pictureList) {
            $pictureLists[$p]['thumb_url'] = str_replace(substr($pictureList['url'], strrpos($pictureList['url'], '/')), '/thumbnail' . substr($pictureList['url'], strrpos($pictureList['url'], '/')), $pictureList['url']);
        }
        return $pictureLists;
    }

    /**
     * 保存/中止托管房源
     */
    public function saveRoom()
    {
        $furniture=I('post.furnitureArr', []);sort($furniture);
        $datas = [
            'cm_id' => $this->companyID,
            'id' => I('get.id', ''),
            'follow_type' => I('post.follow_type', ''),
            'status' => I('post.status', ''),
            'remark' => I('post.remark', ''),
            'furniture' => implode(',', $furniture),
            'update_time' => date('Y-m-d H:i:s'),
        ];
        if (!$datas['cm_id'] || !$datas['id'] || !$datas['follow_type'] || !$datas['status']) {
            retMessage(false, null, '参数错误，请检查参数', '参数错误，请检查参数', 4001);
            exit;
        }
        $result = $this->roomModel->saveRoom($datas['cm_id'], $datas['id'], $datas);
        $result ? retMessage(true, null) : retMessage(false, null, '保存/中止托管失败', '保存/中止托管失败', 4002);
        exit;
    }
}