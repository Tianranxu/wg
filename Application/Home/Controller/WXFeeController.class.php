<?php
/*************************************************
 * 文件名：WXClientController.class.php
 * 功能：     微信控制器
 * 日期：     2015.9.27
 * 作者：     L
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
use Org\Util\RabbitMQ;

class WXFeeController extends WXClientController
{

    protected $openid;

    protected $appid;

    protected $compid;

    protected $wxbindModel;

    protected $propertyModel;

    protected $buildingModel;

    protected $wxunpayModel;

    protected $wxorderModel;

    protected $personModel;

    protected $carModel;

    protected $templet;

    protected $payorderModel;

    protected $publicnoModel;

    protected $accountsModel;

    protected $carfeeModel;

    /**
     * 初始化
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->assign('templ', $this->templet);
    }

    /**
     * 获取access_token
     */
    public function get_access_token()
    {
        $this->_weixinModel = D("weixin");
        $access_token = $this->_weixinModel->get_authorizer_access_token($this->compid);
        if ($access_token == -1) {
            $this->error("授权不可用或已过期，请重新授权", U('publicno/access', array(
                'compid' => $this->compid
            )));
        }
        return $access_token;
    }

    /**
     * 账单缴费首页
     */
    public function index()
    {
        $this->checkInfo();
        $this->assign('templet', $this->templet);
        $this->display();
    }

    /**
     * 物业费页面
     */
    public function fees()
    {
        // 获取用户绑定且设置为缴费的房产
        $this->wxbindModel = D('wxbind');
        $houseInfo = $this->wxbindModel->getCurrentActiveItem($this->openid, 1);
        $total = 0;
        if ($houseInfo) {
            // 查询用户设置的缴费房产待缴费用
            $this->wxunpayModel = D('wxunpay');
            $list = $this->wxunpayModel->getTotalCharges($houseInfo['hm_id'], 'eq', 1);
            foreach ($list as $v) {
                $total += $v['money'] - $v['preferential_money'] + $v['penalty'];
            }
        }

        $this->assign('houseInfo', $houseInfo);
        $this->assign('total', number_format($total, 2, '.', ''));
        $this->display();
    }

    /*
     * 停车费页面
     */
    public function carfee()
    {
        $this->wxbindModel = D('wxbind');
        $carInfo = $this->wxbindModel->getCurrentActiveItem($this->openid, 2);
        $total = 0;
        if ($carInfo) {
            $wxunpayModel = D('wxunpay');
            $list = $wxunpayModel->getCarTotalCharges($carInfo['id'], 'eq', 1);
            foreach ($list as $k => $v) {
                $total += $v['money'] - $v['preferential_money'] + $v['penalty'];
            }
        }
        $this->assign('carInfo', $carInfo);
        $this->assign('total', $total);
        $this->display();
    }

    /**
     * 房产待缴费用页面
     */
    public function housewait()
    {
        // 查询用户设置的缴费财产的待缴费账单
        $this->wxunpayModel = D('wxunpay');
        $this->wxbindModel = D('wxbind');
        $houseInfo = $this->wxbindModel->getCurrentActiveItem($this->openid, 1);
        $unPayList = $this->wxunpayModel->getPayList($houseInfo['hm_id'], 'eq', 1);
        $list = $this->recombinPayListByDate($unPayList);

        $this->assign('list', $list);
        $this->assign('hmId', $houseInfo['hm_id']);
        $this->display();
    }

    /*
     * 车辆待缴费用页面
     */
    public function carwait()
    {
        $this->wxunpayModel = D('wxunpay');
        $this->wxbindModel = D('wxbind');
        $carInfo = $this->wxbindModel->getCurrentActiveItem($this->openid, 2);
        $unPayList = $this->wxunpayModel->getCarPayList($carInfo['id'], 'eq', 1);
        $list = $this->recombinPayListByDate($unPayList);//dump($list);exit;

        $this->assign('list', $list);
        $this->assign('carId', $carInfo['id']);
        $this->display();
    }

    /**
     * 房产订单确认页面
     */
    public function houseconfirm()
    {
        // 接收数据
        $id = I('get.id', '');
        // 查询提交的订单详情
        $this->wxorderModel = D('wxorder');
        $info = $this->wxorderModel->getTempOrderInfo($this->compid, $id);
        // 查询当前设置缴费房产的信息
        $this->propertyModel = D('property');
        $houseBelong = array_shift($this->propertyModel->get_house_belong([$info['hm_id']]));
        $this->accountsModel = D('accounts');
        $lists = $this->accountsModel->getBills($info['ac_ids']);
        foreach ($lists as $li => $list) {
            $lists[$li]['total'] = $list['money'] - $list['preferential_money'] + $list['penalty'];
            $lists[$li]['total'] = number_format($lists[$li]['total'], 2, '.', '');
        }
        // 查询用户基本信息
        $this->personModel = D('person');
        $userInfo = $this->personModel->getUserInfo($this->openid, $this->compid);
        //获取该公众号的商户ID和API秘钥
        $this->publicnoModel = D('publicno');
        $publicnoInfo = $this->publicnoModel->getPublicnoInfo($this->compid);
        $payInfo = [
            'mchId' => $publicnoInfo['mch_id'],
            'apiKey' => $publicnoInfo['api_key']
        ];
        $this->assign('payInfo', $payInfo);
        if ($publicnoInfo['mch_id'] && $publicnoInfo['api_key']) {
            $wxPay = $this->getWxPay($id, ($info['total'] * 100), $publicnoInfo['mch_id'], $publicnoInfo['api_key'], 1);
            // 组装支付后要写入的数据
            $writeData = $wxPay['jsApiParameters'];
            $writeData->openid = $this->openid;
            $writeData->outTradeNo = $wxPay['outTradeNo'];
            $writeData->orderId = $id;
            $writeData->orderType = 1;
            $writeData->acIds = $info['ac_ids'];
            $writeData->compid = $info['cm_id'];
            $writeData->total = $info['total'];
            $this->assign('signPackage', $wxPay['signPackage']);
            $this->assign('jsApiParameters', $wxPay['jsApiParameters']);
            $this->assign('writeData', json_encode($writeData));
        }
        $this->assign('houseBelong', $houseBelong);
        $this->assign('list', $lists);
        $this->assign('total', $info['total']);
        $this->assign('userInfo', $userInfo);
        $this->assign('compid', $this->compid);
        $this->display();
    }

    /**
     * 检查订单中是否存在已缴账单
     */
    public function checkAccountsByOrder()
    {
        //接收数据
        $id = I('post.id', '');
        $type = I('post.type', '');
        if (!$id || !$type) {
            retMessage(false, null, '参数错误，请检查参数', '参数错误，请检查参数', 4001);
            exit;
        }
        $orderType = 1;
        if ($type == 'car') $orderType = 2;
        $this->wxorderModel = D('wxorder');
        $acIds = $this->wxorderModel->getAccountIds($id, $orderType)['ac_ids'];
        if ($type == 'car') {
            $this->carfeeModel = D('carfee');
            $results = $this->carfeeModel->getCarBills($acIds);
        } else {
            $this->accountsModel = D('accounts');
            $results = $this->accountsModel->getBills($acIds);
        }
        $flag = 0;
        foreach ($results as $result) {
            if ($result['status'] == 2) {
                $flag = 1;
                break;
            }
        }
        ($flag == 1) ? retMessage(false, null, '该订单存在已缴账单', '该订单存在已缴账单', 4002) : retMessage(true, null);
        exit;
    }

    /**
     * 支付成功后处理订单
     */
    public function payOrder()
    {
        $postDatas = (empty(I('post.', []))) ? retMessage(false, null, '参数不足，请检查参数', '参数不足请检查参数', 4001) : I('post.', []);
        foreach ($postDatas as $post => $postData) {
            if (!$postData) retMessage(false, null, '参数不足，请检查参数', '参数不足请检查参数', 4001);
        }
        // 组装数据
        $data = ['appid' => $postDatas['appId'], 'timestamp' => $postDatas['timestamp'], 'noncestr' => $postDatas['nonceStr'], 'package' => $postDatas['package'], 'signtype' => $postDatas['signType'], 'paysign' => $postDatas['paySign'],
            'openid' => $postDatas['openid'], 'out_trade_no' => $postDatas['outTradeNo'], 'ac_ids' => $postDatas['acIds'], 'cm_id' => $postDatas['compid'],
            'order_id' => $postDatas['orderId'], 'total' => $postDatas['total'], 'pay_date' => $postDatas['payDate'], 'pay_return_date' => $postDatas['payReturnDate'],
            'type' => $postDatas['orderType'], 'status' => $postDatas['status'], 'create_time' => date('Y-m-d H:i:s')
        ];
        //添加缴费记录
        $this->payorderModel = D('payorder');
        $payOrderResult = $this->payorderModel->recordPayOrder($data);
        if (!$payOrderResult) retMessage(false, null, '订单支付记录写入失败', '订单支付记录写入失败', 4002);
        //添加PC端消息通知记录
        $noticeResult = A('pushmsg')->sendNoticesToPcAdministors($postDatas['compid'], C('NOTICE_SEND_TYPE')[($postDatas['orderType'] + 3)]['name']);
        if(!$noticeResult) retMessage(false, null, 'PC消息通知记录写入失败', 'PC消息通知记录写入失败', 4003);
        //发送消息给客服(缴费管理员)
        $users = D('WXuser')->getUserByType($this->compid, C('WXUSER_TYPE.CHARGE_MG'));    //找出该公司的缴费管理员
        if ($users) {
            $pay_user = D('WXuser')->WXuserInfo($postDatas['openid'])['nickname'];
            $long_id = D('Weixin')->getTemplateId($this->compid, C('TEMPLATE_MSG.SUBMITED_FEE'));
            $fee_type = ($postDatas['orderType'] == 1) ? '物业费' : '停车费';
            foreach ($users as $key => $user) {
                $temp_data['content'][$key] = [
                    'touser' => $user['openid'],
                    'template_id' => $long_id,
                    'data' => [
                        'first' => ['value' => '您好，您有客户完成了一笔微信缴费', 'color' => '#173177'],
                        'keyword1' => ['value' => $postDatas['outTradeNo'], 'color' => '#173177'],
                        'keyword2' => ['value' => $pay_user, 'color' => '#173177'],
                        'keyword3' => ['value' => $postDatas['total'], 'color' => '#173177'],
                        'keyword4' => ['value' => $fee_type, 'color' => '#173177'],
                        'keyword5' => ['value' => date('Y-m-d H:i:s'), 'color' => '#173177'],
                        'remark' => ['value' => '请您前往后台查看相关的缴费信息，谢谢！', 'color' => '#173177'],
                    ],
                ];
            }
            $temp_data['compid'] = $this->compid;
            $temp_data['msgtype'] = 'property';
            RabbitMQ::publish($this->templateMsgQueue, json_encode($temp_data));
        }
        retMessage(true, null);
    }

    /**
     * 统一微信下单
     *
     * @param string $id
     *            订单ID
     * @param float $total
     *            订单总金额
     * @return multitype:string number NULL
     */
    public function getWxPay($id, $total, $mchId, $apiKey, $type)
    {
        //$total = 1;
        // 实例化微信JSSDK
        vendor('Jssdk.JSSDK');
        $JSSDK = new \JSSDK($this->get_access_token(), $this->appid);
        $result['signPackage'] = $JSSDK->getSignPackage();

        // 使用统一支付接口，获取prepay_id
        vendor('WxPayPubHelper.WxPayPubHelper');
        $unifiedOrder = new \UnifiedOrder_pub($this->appid, $mchId, $apiKey);
        // 设置参数
        $unifiedOrder->setParameter('openid', $this->openid);
        if ($type == 1) $unifiedOrder->setParameter('body', '物业费');
        if ($type == 2) $unifiedOrder->setParameter('body', '停车费');
        $timeStamp = time();
        $result['outTradeNo'] = strval(date('Ymd') . '-' . $id . '-' . $timeStamp);
        $unifiedOrder->setParameter('out_trade_no', $result['outTradeNo']); // 订单号
        $unifiedOrder->setParameter('total_fee', $total); // 总金额
        $unifiedOrder->setParameter('notify_url', U('houseconfirm', array(
            'id' => $id
        ))); // 通知地址
        $unifiedOrder->setParameter('trade_type', 'JSAPI'); // 交易类型
        // 非必填参数，商户可根据实际情况选填
        // $unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
        // $unifiedOrder->setParameter("device_info","XXXX");//设备号
        // $unifiedOrder->setParameter("attach","XXXX");//附加数据
        // $unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
        // $unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
        // $unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
        // $unifiedOrder->setParameter("openid","XXXX");//用户标识
        // $unifiedOrder->setParameter("product_id","XXXX");//商品ID
        $prepay_id = $unifiedOrder->getPrepayId();

        /**
         * 使用jsapi调起支付*
         */
        // 使用JsApi接口
        $jsApi = new \JsApi_pub($this->appid, $mchId, $apiKey);
        $jsApi->setPrepayId($prepay_id);
        $result['jsApiParameters'] = json_decode($jsApi->getParameters());

        return $result;
    }

    /**
     * 房产缴费记录
     */
    public function housepayrecord()
    {
        $this->wxbindModel = D('wxbind');
        $hmId = $this->wxbindModel->getCurrentActiveItem($this->openid, 1)['hm_id'];
        //获取用户所有缴费记录
        $this->payorderModel = D('payorder');
        $payRecordList = $this->payorderModel->getPayRecordList($this->openid, $hmId);
        $lists = $this->recombinPayRecord($payRecordList);

        $this->assign('lists', $lists);
        $this->display();
    }

    /**
     * 车位缴费记录
     */
    public function carpayrecord()
    {
        $this->wxbindModel = D('wxbind');
        $carId = $this->wxbindModel->getCurrentActiveItem($this->openid, 2)['id'];
        //获取用户所有缴费记录
        $this->payorderModel = D('payorder');
        $payRecordList = $this->payorderModel->getPayRecordList($this->openid, $carId, 2);
        $lists = $this->recombinPayRecord($payRecordList);

        $this->assign('lists', $lists);
        $this->display();
    }

    /**
     * 获取单月的房产缴费记录
     */
    public function getHouseOneMonthPayRecord()
    {
        //接收数据
        $umid = I('get.umid', '');
        $type = I('post.type', 1);
        $year = I('post.year', '');
        $month = I('post.month', '');
        if (!$umid || !$type || !$year || !$month) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit;
        }

        $this->payorderModel = D('payorder');
        $payRecordList = $this->payorderModel->getPayRecordList($this->openid, $type, $year . '-' . $month);
        $list = $this->recombinPayRecord($payRecordList);
        $list ? retMessage(true, array_values($list)) : retMessage(false, null, '查询不到该月的缴费记录', '查询不到该月的缴费记录', 4002);
        exit;
    }

    public function recombinPayRecord($payRecordList)
    {
        //重组数据
        $list = array();
        foreach ($payRecordList as $p => $pay) {
            //按付款时间分组
            $list[$pay['order']['pay_date']]['order'] = $pay['order'];
            $list[$pay['order']['pay_date']]['accounts'] = $pay['accounts'];
            $list[$pay['order']['pay_date']]['day'] = date('d', strtotime($pay['order']['pay_date']));
            $list[$pay['order']['pay_date']]['hour'] = date('H', strtotime($pay['order']['pay_date']));
            $list[$pay['order']['pay_date']]['minute'] = date('i', strtotime($pay['order']['pay_date']));
        }
        return $list;
    }

    /*
     * 车辆订单确认界面
     */
    public function carconfirm()
    {
        // 接收数据
        $id = I('get.id', '');
        $this->wxbindModel = D('wxbind');
        $carInfo = $this->wxbindModel->getCurrentActiveItem($this->openid, 2);
        $this->wxbindModel = D('wxbind');
        // 查询提交的订单详情
        $this->wxorderModel = D('wxorder');
        $info = $this->wxorderModel->getTempOrderInfo($this->compid, $id, 'car');
        $this->carfeeModel = D('carfee');
        $lists = $this->carfeeModel->getCarBills(explode(',', $info['ac_ids']));
        foreach ($lists as $li => $list) {
            $lists[$li]['total'] = $list['money'] - $list['preferential_money'] + $list['penalty'];
            $lists[$li]['total'] = number_format($lists[$li]['total'], 2, '.', '');
        }
        // 查询用户基本信息
        $this->personModel = D('person');
        $userInfo = $this->personModel->getUserInfo($this->openid, $this->compid);
        //获取该公众号的商户ID和API秘钥
        $this->publicnoModel = D('publicno');
        $publicnoInfo = $this->publicnoModel->getPublicnoInfo($this->compid);
        $payInfo = [
            'mchId' => $publicnoInfo['mch_id'],
            'apiKey' => $publicnoInfo['api_key']
        ];
        $this->assign('payInfo', $payInfo);
        if ($publicnoInfo['mch_id'] && $publicnoInfo['api_key']) {
            $wxPay = $this->getWxPay($id, ($info['total'] * 100), $publicnoInfo['mch_id'], $publicnoInfo['api_key'], 2);
            // 组装支付后要写入的数据
            $writeData = $wxPay['jsApiParameters'];
            $writeData->openid = $this->openid;
            $writeData->outTradeNo = $wxPay['outTradeNo'];
            $writeData->orderId = $id;
            $writeData->orderType = 2;
            $writeData->acIds = $info['ac_ids'];
            $writeData->compid = $info['cm_id'];
            $writeData->total = $info['total'];
            $this->assign('signPackage', $wxPay['signPackage']);
            $this->assign('jsApiParameters', $wxPay['jsApiParameters']);
            $this->assign('writeData', json_encode($writeData));
        }

        $this->assign('carInfo', $carInfo);
        $this->assign('list', $lists);
        $this->assign('total', $info['total']);
        $this->assign('userInfo', $userInfo);
        $this->display();
    }

    /**
     * 房产信息页面
     */
    public function houselist()
    {
        // 查询用户绑定的房产列表
        $this->wxbindModel = D('wxbind');
        $houseBindList = $this->wxbindModel->getHouseBindList($this->openid, 1);
        // 将已设置为缴费的房产移动到开头
        foreach ($houseBindList as $k => $v) {
            if ($v['is_pay'] == 1) {
                unset($houseBindList[$k]);
                array_unshift($houseBindList, $v);
            }
        }

        $this->assign('houseBindList', $houseBindList);
        $this->display();
    }

    /**
     * 房产绑定页面
     */
    public function bind()
    {
        //获取楼盘列表
        $propertyCommon = A('propertycommon');
        $ccLists = $propertyCommon->getCommunityLists($this->compid);
        $this->assign('ccLists', $ccLists);
        $this->assign('openid', $this->openid);
        $this->display();
    }

    /**
     * 房产历史账单
     */
    public function househistory()
    {
        // 查询用户设置缴费房产的账单列表
        $this->wxunpayModel = D('wxunpay');
        $this->wxbindModel = D('wxbind');
        $houseInfo = $this->wxbindModel->getCurrentActiveItem($this->openid, 1);
        $payList = $this->wxunpayModel->getPayList($houseInfo['hm_id'], 'gt', -1, date('Y'));
        $list = $this->recombinPayListByDate($payList);

        $this->assign('list', $list);
        $this->display();
    }

    /*
     * 车辆历史账单
     */
    public function carhistory()
    {
        $this->wxbindModel = D('wxbind');
        $this->wxunpayModel = D('wxunpay');
        $carInfo = $this->wxbindModel->getCurrentActiveItem($this->openid, 2);
        $payList = $this->wxunpayModel->getCarPayList($carInfo['id'], 'gt', -1, date('Y'));
        $list = $this->recombinPayListByDate($payList);
        $this->assign('list', $list);
        $this->assign('type', 2);
        $this->display();
    }

    /**
     * 车辆信息页面
     */
    public function carlist()
    {
        // 查询用户绑定的车辆列表
        $this->wxbindModel = D('wxbind');
        $carBindList = $this->wxbindModel->getHouseBindList($this->openid, 2);
        // 将已设置为缴费的房产移动到开头
        foreach ($carBindList as $k => $v) {
            if ($v['is_pay'] == 1) {
                unset($carBindList[$k]);
                array_unshift($carBindList, $v);
            }
        }

        $this->assign('carBindList', $carBindList);
        $this->display();
    }

    /**
     * 车辆绑定页面
     */
    public function carbind()
    {
        //获取楼盘列表
        $propertyCommon = A('propertycommon');
        $ccLists = $propertyCommon->getCommunityLists($this->compid);
        $this->assign('ccLists', $ccLists);
        $this->display();
    }

    /**
     * 查询楼盘列表
     */
    public function getPropertyList()
    {
        // 接收数据
        $getData = array(
            'umid' => I('get.umid', ''),
            'type' => I('post.type', '')
        );
        if (!$getData['umid'] || !$getData['type']) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }

        // 根据公众号appid获取楼盘列表
        if ($getData['type'] == 1) {
            $this->propertyModel = D('property');
            $propertyList = $this->propertyModel->getPropertyByAppid($this->appid);
            if (!$propertyList) {
                retMessage(false, null, '查询不到楼盘', '查询不到楼盘', 4002);
                exit();
            }
            retMessage(true, $propertyList);
            exit();
        }

        // 接收数据
        $id = I('post.id', '');
        if (!$id) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
        // 根据楼盘ID获取楼宇列表
        if ($getData['type'] == 2) {
            $this->buildingModel = D('building');
            $buildingList = $this->buildingModel->selectAllBuild($id, 1);
            if (!$buildingList) {
                retMessage(false, null, '查询不到楼宇', '查询不到楼宇', 4002);
                exit();
            }
            retMessage(true, $buildingList);
            exit();
        }

        // 根据楼宇ID获取房间列表
        if ($getData['type'] == 3) {
            $this->propertyModel = D('property');
            $houseList = $this->propertyModel->get_house_list($id, '', 0, '', '', 1);
            if (!$houseList) retMessage(false, null, '搜索不到房间', '搜索不到房间', 4002);
            // 取出所有房间ID
            $hmIds = array_map(function ($houseValue) {return $houseValue['id'];}, $houseList['list']);
            $this->wxbindModel = D('wxbind');
            $screenHouseList = $this->wxbindModel->getHouseBindList($this->openid, 1);
            // 重组数据
            foreach ($houseList['list'] as $hk => $hv) {
                foreach ($screenHouseList as $sv) {
                    if ($hv['id'] == $sv['hm_id']) {
                        // 将用户已绑定的房产去除
                        unset($houseList['list'][$hk]);
                        continue;
                    }
                }
            }
            $houseList['list'] = array_values($houseList['list']);
            retMessage(true, $houseList['list']);
        }
    }

    /**
     * 获取业主手机号码（隐藏后四位）
     */
    public function getMobile()
    {
        //接收数据
        $umid = I('get.umid', '');
        $houseId = I('post.houseId', '');
        if (!$umid || !$houseId) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit;
        }

        $this->propertyModel = D('property');
        $result = $this->propertyModel->getHouseInfo($houseId)['mobile_number'];
        if (!$result) {
            retMessage(false, null, '查询不到业主手机号码', '查询不到业主手机号码', 4002);
            exit;
        }
        //隐藏业主手机号码后四位
        $result = str_replace(substr($result, 7, 4), '', $result);
        retMessage(true, $result);
        exit;
    }

    /**
     * 绑定房产/车辆
     */
    public function bindOn()
    {
        // 接收数据
        $umid = I('get.umid', '');
        $id = I('post.id', '');
        $mobile = I('post.mobile', '');
        $type = I('post.type', '');
        if (!$umid || !$id || !$type) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
        //判断业主手机号码是否正确
        $this->propertyModel = D('property');
        if ($type == 1) {
            if (!$mobile) {
                retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
                exit;
            }
            $checkMobile = $this->propertyModel->getHouseInfo($id)['mobile_number'];
            if ($checkMobile != $mobile) {
                retMessage(false, null, '业主手机号码不正确', '业主手机号码不正确', 4003);
                exit;
            }
        }
        // 判断用户是否首次绑定房产/车辆
        $this->wxbindModel = D('wxbind');
        $isBind = $this->wxbindModel->getHouseBindList($this->openid, $type);
        if ($type == 2) {
            foreach ($isBind as $bind) {
                if ($id == $bind['car_id']) {
                    retMessage(false, null, '该车辆已被绑定', '该车辆已被绑定', 4004);
                    exit;
                }
            }
        }
        if (!$isBind) {
            $isPay = 1;
        }
        $result = $this->wxbindModel->bindOn($this->openid, $type, $id, $isPay);
        $result ? retMessage(true, null) : retMessage(false, null, '绑定失败', '绑定失败', 4002);
    }

    /**
     * 解除绑定房产/车辆
     */
    public function unBindOn()
    {
        // 接收数据
        $umid = I('get.umid', '');
        $id = I('post.id', '');
        $type = I('post.type', '');
        if (!$umid || !$id || !$type) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }

        $this->wxbindModel = D('wxbind');
        $result = $this->wxbindModel->unBindOn($this->openid, $type, $id);
        if (!$result) {
            retMessage(false, null, '解除绑定失败', '解除绑定失败', 4002);
            exit();
        }
        retMessage(true, null);
        exit();
    }

    /**
     * 切换缴费房产/车辆
     */
    public function changePay()
    {
        // 未设置缴费的ID
        $umid = I('get.umid', '');
        $unSetId = I('post.unSetId', '');
        // 已设置缴费的ID
        $setId = I('post.setId', '');
        $type = I('post.type', '');
        if (!$umid || !$unSetId || !$setId || !$type) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }

        $this->wxbindModel = D('wxbind');
        $result = $this->wxbindModel->changePay($this->openid, $type, $unSetId, $setId);
        if (!$result) {
            retMessage(false, null, '切换缴费设置失败', '切换缴费设置失败', 4002);
            exit();
        }
        retMessage(true, null);
        exit();
    }

    /**
     * 提交待缴费订单
     */
    public function submitUnPayOrder()
    {
        // 接收数据
        $id = I('post.id', '');
        $umid = I('get.umid', '');
        $total = I('post.totalAmount', '');
        $acIds = I('post.arr', []);
        $type = I('post.type', '');
        if (!$id || !$umid || !$total || empty($acIds) || !$type) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }

        $this->wxorderModel = D('wxorder');
        $result = $this->wxorderModel->submitUnPayOrder($this->compid, $acIds, $id, $total, $this->openid, 'weixin', $type);
        $result ? retMessage(true, $result) : retMessage(false, null, '提交订单失败', '提交订单失败', 4002);
        exit;
    }

    /**
     * 获取用户设置缴费房产中某个月的账单
     */
    public function getHouseOneMonthPay()
    {
        // 接收数据
        $umid = I('get.umid', '');
        $year = I('post.year', '');
        $month = I('post.month', '');
        if (!$umid || !$year || !$month) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }

        // 查询某个月的历史账单
        $this->wxunpayModel = D('wxunpay');
        $this->wxbindModel = D('wxbind');
        $houseInfo = $this->wxbindModel->getCurrentActiveItem($this->openid, 1);
        $list = $this->recombinPayListByDate($this->wxunpayModel->getPayList($houseInfo['hm_id'], '', '', $year, $month));
        if (!$list) {
            retMessage(false, null, '查询不到账单', '查询不到账单', 4002);
            exit();
        }
        retMessage(true, $list);
        exit();
    }

    /*
     * 获取用户设置车辆中某个月的账单
     */
    public function getCarOneMonthPay()
    {
        // 接收数据
        $umid = I('get.umid', '');
        $year = I('post.year', '');
        $month = I('post.month', '');
        if (!$umid || !$year || !$month) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
        // 查询某个月的历史账单
        $this->wxunpayModel = D('wxunpay');
        $this->wxbindModel = D('wxbind');
        $carInfo = $this->wxbindModel->getCurrentActiveItem($this->openid, 2);
        $list = $this->recombinPayListByDate($this->wxunpayModel->getCarPayList($carInfo['id'], '', '', $year, $month));
        if (!$list) {
            retMessage(false, null, "无相关账单", "无相关账单", 4002);
            exit();
        }
        retMessage(true, $list);
    }

    /**
     * 按月份重组账单列表
     *
     * @param string $payList
     *            账单列表
     * @return Ambigous <multitype:, number>
     */
    public function recombinPayListByDate($payList)
    {
        $list = array();
        foreach ($payList as $k => $v) {
            // 计算总费用
            $v['total'] += $v['money'] - $v['preferential_money'] + $v['penalty'];
            $v['total'] = number_format($v['total'], 2, '.', '');
            $payList[$k]['total'] = $v['total'];
            if (strlen($v['month']) == 1) {
                $v['month'] = str_pad($v['month'], 2, 0, STR_PAD_LEFT);
            }
            // 根据月份分组
            $list[$v['year'] . '-' . $v['month']]['bills'][] = $v;
            $list[$v['year'] . '-' . $v['month']]['month'] = $v['month'];
            $list[$v['year'] . '-' . $v['month']]['year'] = $v['year'];
            $list[$v['year'] . '-' . $v['month']]['total'] += $v['total'];
        }
        return $list;
    }

    /**
     * 检查该楼盘下是否存在该车辆
     */
    public function checkCar()
    {
        // 接收数据
        $umid = I('get.umid', '');
        $ccId = I('post.id', '');
        $carNumber = I('post.carNumber', '');
        $user = I('post.user', '');
        if (!$umid || !$ccId || !$carNumber || !$user) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }

        $this->carModel = D('car');
        $result = $this->carModel->checkCar($ccId, str_replace(' ', '', $carNumber), $user);
        if (!$result) {
            retMessage(false, null, '查询不到该车辆', '查询不到该车辆', 4002);
            exit();
        }
        retMessage(true, $result['id']);
        exit();
    }
}


