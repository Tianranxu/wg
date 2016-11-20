<?php
/*************************************************
 * 文件名：PrintController.class.php
 * 功能：     打印控制器
 * 日期：     2016.01.11
 * 作者：     XU
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/

namespace Home\Controller;

class PrintController extends AccessController
{
    protected $propertyModel;

    protected $accountsModel;

    protected $carfeeModel;

    protected $companyModel;

    protected $leaseModel;

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 通知单页面
     */
    public function notice()
    {
        $id = I('get.id', '');
        $type = I('get.type', 'house');
        $function = new \ReflectionMethod(get_called_class(), 'get' . ucwords($type) . 'Notices');
        $lists = $function->invoke($this, $this->companyID, $id);
        //查看该企业下的通知单说明
        $this->companyModel = D('company');
        $noticeRemark = $this->companyModel->selectCompanyAll($this->companyID)['notice_remark'];

        $this->assign('lists', $lists);
        $this->assign('noticeRemark', $noticeRemark);
        $this->display();
    }

    /**
     * 获取房产待缴费列表
     * @param int $compid 企业ID
     * @param int $id 房间ID
     * @return array
     */
    public function getHouseNotices($compid, $id)
    {
        $this->accountsModel = D('accounts');
        $unPayLists = $this->accountsModel->getPayAccounts($compid, [$id]);
        $unPayLists = $this->calculationAccountsMoney($unPayLists);
        //查询该房间所属信息
        $this->propertyModel = D('property');
        $propertyInfo = $this->propertyModel->getPropertyBelog($id, 'house');
        return ['list' => $unPayLists, 'info' => $propertyInfo];
    }

    /**
     * 获取车位待缴费列表
     * @param int $compid 企业ID
     * @param int $id 车位ID
     * @return array
     */
    public function getCarNotices($compid, $id)
    {
        $this->carfeeModel = D('carfee');
        $unPayLists = $this->carfeeModel->getPayAccounts($compid, [$id]);
        $unPayLists = $this->calculationAccountsMoney($unPayLists);
        //查询该车位所属信息
        $carModel=D('car');
        $carResult=$carModel->getCar($id);
        $ccName=$carModel->getCommunityName($carResult['cc_id'])['name'];
        $carInfo=['cc_name'=>$ccName,'card_number'=>$carResult['card_number'],'car_number'=>$carResult['car_number']];
        return ['list' => $unPayLists, 'info' => $carInfo];
    }

    /**
     * 获取合约待缴费列表
     * @param int $compid 企业ID
     * @param int $id 房间ID
     * @return array
     */
    public function getLeaseNotices($compid, $id)
    {
        $this->leaseModel = D('lease');
        $unPayLists = $this->leaseModel->getPayAccounts($compid, $id);
        $unPayLists = $this->calculationAccountsMoney($unPayLists);
        //查询该房间所属信息
        $this->propertyModel = D('property');
        $propertyInfo = $this->propertyModel->getPropertyBelog($id, 'house');
        return ['list' => $unPayLists, 'info' => $propertyInfo];
    }

    /**
     * 计算账单最终金额
     * @param array $unPayLists 账单列表
     * @return array
     */
    public function calculationAccountsMoney(array $unPayLists)
    {
        foreach ($unPayLists as $un => $unPayList) {
            $unPayLists[$un]['preferential_money'] = $unPayList['preferential_money'] = $unPayList['preferential_money'] ? $unPayList['preferential_money'] : number_format(0, 2);
            $unPayLists[$un]['penalty'] = $unPayList['penalty'] = $unPayList['penalty'] ?: number_format(0, 2);
            $unPayLists[$un]['total'] = number_format((floatval($unPayList['money']) - floatval($unPayList['preferential_money']) + floatval($unPayList['penalty'])), 2, '.', '');

        }
        return $unPayLists;
    }

    /**
     * 保存通知单备注
     */
    public function saveNoticeRemark()
    {
        //接收数据
        $compid = I('get.compid', '');
        $noticeRemark = I('post.noticeRemark', '');
        if (!$compid || !$noticeRemark) {
            retMessage(false, null, '参数错误，请检查参数', '参数错误，请检查参数', 4001);
            exit;
        }
        $this->companyModel = D('company');
        $result = $this->companyModel->saveNoticeRemark($compid, $noticeRemark);
        $result ? retMessage(true, null) : retMessage(false, null, '保存备注失败', '保存备注失败', 4002);
        exit;
    }

    /**
     * 打印通知单
     */
    public function printNotice()
    {
        //接收数据
        $type = I('get.type', '');
        $id = I('get.id', '');
        $noticeRemark = I('post.noticeRemark', '');
        //获取数据
        $function = new \ReflectionMethod(get_called_class(), 'get' . ucwords($type) . 'Notices');
        $lists = $function->invoke($this, $this->companyID, $id);
        // 导入PHPExcel类库
        import("Org.Util.PHPExcel");
        // 创建PHPExcel对象
        $PHPExcel = new \PHPExcel();
        $headers = $this->getNoticeHeaders();
        //设置默认字体和字体大小、宽度、单元格全部垂直居中
        for ($i = ord('A'); $i < (count($headers) + ord('A')); $i++) {
            $PHPExcel->getActiveSheet()->getColumnDimension(chr($i))->setWidth(10.5);
        }
        $PHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $PHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setName('微软雅黑')->setSize(14);
        //合并第一行表格并设置标题，水平居中
        $PHPExcel = $this->setNoticeTitle($PHPExcel, $lists['info']['cm_name']);
        //设置打印相关信息
        $PHPExcel = $this->setNoticePrintInfo($PHPExcel, $lists['info'], $type);
        //设置表头
        $PHPExcel = $this->setNoticeHeaders($PHPExcel, $headers);
        //写入数据
        $PHPExcel = $this->layoutNoticeContentPrint($type, $PHPExcel, $lists['list'], $noticeRemark);
        //设置文件名
        $fileName = ($type == 'car') ? date('Y_m') . '_' . $lists['info']['cc_name'] . '收费通知单' . '.xlsx' : date('Y_m') . '_' . $lists['info']['cc_name'] . $lists['info']['bm_name'] . $lists['info']['hm_name'] . '收费通知单' . '.xlsx';
        $fileName = iconv('utf-8', 'gb2312', $fileName);
        //重命名Sheet，设置活动单指数到第一个表，所以Excel打开这是第一个表
        $PHPExcel->getActiveSheet()->setTitle('收费通知单');
        $PHPExcel->setActiveSheetIndex(0);
        //将输出重定向到一个客户端web浏览器（Excel2007）
        header('Content-Type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition:attachment;filename=\"{$fileName}\"");
        header('Cache-Control:max-age=0');
        $PHPExcelWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
        $PHPExcelWriter->save('php://output');
    }

    /**
     * 获取通知单表头
     * @return mixed
     */
    public function getNoticeHeaders()
    {
        $headers = ['项目', '账期', '金额', '优惠', '滞纳金', '小计'];
        return $headers;
    }

    /**
     * 设置通知单标题
     * @param object $PHPExcel PHPExcel
     * @param string $compName 企业名称
     * @return mixed
     */
    public function setNoticeTitle($PHPExcel, $compName)
    {
        $PHPExcel->getActiveSheet()->mergeCells('A1:F1');
        $PHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $PHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $compName . '收费通知单');
        $PHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);
        $PHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(18)->setBold(true);
        return $PHPExcel;
    }

    /**
     * 设置通知单相关打印信息
     * @param object $PHPExcel PHPExcel
     * @param array $infos 该房产相关信息
     * @return mixed
     */
    public function setNoticePrintInfo($PHPExcel, array $infos, $type)
    {
        //写入房产信息和打印日期
        $PHPExcel->getActiveSheet()->mergeCells('A2:C2');
        if($type=='house') $PHPExcel->setActiveSheetIndex(0)->setCellValue('A2', "房产信息：{$infos['cc_name']} {$infos['bm_name']} {$infos['hm_name']}");
        if($type=='car') $PHPExcel->setActiveSheetIndex(0)->setCellValue('A2', "房产信息：{$infos['cc_name']} {$infos['card_number']} {$infos['car_number']}");
        $printDate = date('Y-m-d');
        $PHPExcel->getActiveSheet()->mergeCells('E2:F2');
        $PHPExcel->setActiveSheetIndex(0)->setCellValue('E2', "打印日期：{$printDate}");
        $PHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(25);
        return $PHPExcel;
    }

    /**
     * 设置通知单表头
     * @param object $PHPExcel PHPExcel
     * @param array $headers 表头
     * @return mixed
     */
    public function setNoticeHeaders($PHPExcel, array $headers)
    {
        $key = ord('A');
        foreach ($headers as $header) {
            $colum = chr($key);
            $PHPExcel->setActiveSheetIndex(0)->setCellValue("{$colum}3", $header);
            $PHPExcel->getActiveSheet()->getRowDimension('3')->setRowHeight(25);
            $PHPExcel->getActiveSheet()->getStyle("{$colum}3")->getFont()->setBold(true);
            $key++;
        }
        $PHPExcel->getActiveSheet()->getStyle('A3:F3')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        return $PHPExcel;
    }

    /**
     * 设置通知单内容
     * @param string $type 类型  house-房间，car-车位
     * @param object $PHPExcel PHPExcel
     * @param array $lists 通知单列表
     * @param string $noticeRemark 通知单说明
     * @return mixed
     */
    public function layoutNoticeContentPrint($type, $PHPExcel, array $lists, $noticeRemark = '')
    {
        //数据遍历
        $startColum = $colum = 4;
        $total = 0;
        foreach ($lists as $li => $rows) {
            $span = ord('A');
            if ($type == 'house') $PHPExcel->getActiveSheet()->setCellValue('A' . $colum, $rows['ch_name']);
            if ($type == 'car') $PHPExcel->getActiveSheet()->setCellValue('A' . $colum, '停车费');
            if ($type == 'lease') $PHPExcel->getActiveSheet()->setCellValue('A' . $colum, '租金');
            $PHPExcel->getActiveSheet()->setCellValue('B' . $colum, "{$rows['year']} 年{$rows['month']} 月");
            $PHPExcel->getActiveSheet()->setCellValue('C' . $colum, "\t" . $rows['money'] . "\t");
            $PHPExcel->getActiveSheet()->setCellValue('D' . $colum, "\t" . $rows['preferential_money'] . "\t");
            $PHPExcel->getActiveSheet()->setCellValue('E' . $colum, "\t" . $rows['penalty'] . "\t");
            $PHPExcel->getActiveSheet()->setCellValue('F' . $colum, "\t" . $rows['total'] . "\t");
            $PHPExcel->getActiveSheet()->getRowDimension($colum)->setRowHeight(20);
            $total = $total + $rows['money'] - $rows['preferential_money'] + $rows['penalty'];
            $span++;
            $colum++;
        }
        $PHPExcel->getActiveSheet()->getStyle("C{$startColum}:F{$colum}")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        //设置合计并将中间空白单元格合并
        $PHPExcel->getActiveSheet()->setCellValue('A' . $colum, '合计人民币');
        $PHPExcel->getActiveSheet()->getStyle('A' . $colum)->getFont()->setBold(true);
        $PHPExcel->getActiveSheet()->mergeCells("B{$colum}:E{$colum}");
        $PHPExcel->getActiveSheet()->setCellValue('F' . ($colum), "\t" . number_format($total, 2) . '元' . "\t");
        $PHPExcel->getActiveSheet()->getStyle('F' . $colum)->getFont()->setBold(true);
        $PHPExcel->getActiveSheet()->getRowDimension($colum)->setRowHeight(20);
        $PHPExcel->getActiveSheet()->getStyle('A4:F' . $colum)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        //设置通知单说明，合并该单元格，设置该单元格自动换行
        $PHPExcel->getActiveSheet()->mergeCells('A' . ($colum + 2) . ':F' . ($colum + 2));
        $PHPExcel->getActiveSheet()->getRowDimension(($colum + 2))->setRowHeight(200);
        $PHPExcel->getActiveSheet()->getStyle('A' . ($colum + 2))->getAlignment()->setWrapText(true);
        $PHPExcel->getActiveSheet()->setCellValue('A' . ($colum + 2), $noticeRemark);
        $PHPExcel->getActiveSheet()->getStyle('A' . ($colum + 2))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_TOP);
        return $PHPExcel;
    }

    //收据打印页面
    public function printReceipt(){
        $compid = I('get.compid', '');
        $data = $this->getOrder();
        $data['orderData'] = $this->addUserAndPayType($data['orderData'], $compid);
        $this->assign('compid', $compid);
        $this->assign('order', $data['orderData']);
        $this->assign('type', I('get.type'));
        $this->assign('bills', $data['bills']);
        $this->assign('date', date('Y-m-d'));
        $this->display();
    }

    public function addUserAndPayType($orderData, $compid=''){
        //组合收款人信息（当前系统使用者）
        foreach ($orderData as $key => $order) {
            $uids[] = $order['uid']; 
        }
        $users = D('User')->getUserById($uids);
        foreach ($users as $key => $user) {
            $userData[$user['id']] = $user;
        }
        foreach ($orderData as $key => $order) {
            $orderData[$key]['uname'] = $userData[$order['uid']]['name'];
            $orderData[$key]['code'] = $userData[$order['uid']]['code'];
        }
       return $orderData;
    }

    //获取订单收据数据
    private function getOrder(){
        $type = I('request.type');
        $orderId = I('request.order_id');
        $function = new \ReflectionMethod(get_called_class(), 'get' . ucwords($type) . 'Order');
        return $function->invoke($this, $orderId);
    }

    public function getPropertyOrder($orderId){
        $payOrderModel = D('Payorder');
        $orderData = $payOrderModel->getPayOrder($orderId);
        foreach ($orderData as $key => $order) {
            $orderData[$key]['type_name'] = C("PAY_TYPE_READ.".$order['type']);
        }
        $bills = D('accounts')->getBills($orderData[0]['ac_ids']);
        return ['orderData' => $orderData, 'bills' => $bills];
    }

    public function getCarOrder($orderId){
        $payOrderModel = D('Payorder');
        $orderData = $payOrderModel->getCarPayOrder($orderId);
        foreach ($orderData as $key => $order) {
            $orderData[$key]['type_name'] = C("PAY_TYPE_READ.".$order['type']);
            $orderData[$key]['b_name'] = $order['card_number'];
            $orderData[$key]['h_name'] = $order['car_number'];
        }
        $bills = D('Carfee')->getCarBills($orderData[0]['ac_ids']);
        foreach ($bills as $key => $bill) {
            $bills[$key]['ch_name'] = '停车费';
        }
        return ['orderData' => $orderData, 'bills' => $bills];
    }

    public function getLeaseOrder($orderId){
        $orderData = D('Payorder')->getLeasePayOrder($orderId);
        foreach ($orderData as $key => $order) {
            $orderData[$key]['type_name'] = C("PAY_TYPE_READ.".$order['type']);
        }
        $bills = D('Lease')->getBills($orderData[0]['ac_ids']);
        foreach ($bills as $key => $bill) {
            $bills[$key]['ch_name'] = '租金';
        }
        return ['orderData' => $orderData, 'bills' => $bills];
    }


    //打印收据的方法
    public function doPrint(){
        $remarks = I('post.remark');
        $type = I('post.type');
        $data = $this->getOrder();
        $data['orderData'] = $this->addUserAndPayType($data['orderData']);
        foreach ($data['bills'] as $key => $bill) {
            $data['bills'][$key]['remark'] = $remarks[$key];
        }
        $this->setData($data['orderData'], $data['bills'], $type);
    }

    //放置要打印的数据
    public function setData($orderData, $bills, $type){
        // 导入PHPExcel类库
        import("Org.Util.PHPExcel");
        // 创建PHPExcel对象
        $PHPExcel = new \PHPExcel();
        $PHPExcel->setActiveSheetIndex(0);
        $head = ($type == 'car') ? '车位信息：' : '房产信息：';
        $PHPExcel->getActiveSheet()->setCellValue('A1', $orderData[0]['c_name'].'收款收据');
        if ($type == 'lease') {
            $PHPExcel->getActiveSheet()->setCellValue('A2', $head.$bills[0]['property']); 
        }else{
            $PHPExcel->getActiveSheet()->setCellValue('A2', $head.$orderData[0]['cc_name'].$orderData[0]['b_name'].$orderData[0]['h_name']);    
        }
        $PHPExcel->getActiveSheet()->setCellValue('A3', '交款单位：'.$orderData[0]['pay_user']);
        $PHPExcel->getActiveSheet()->setCellValue('D2', 'NO.'.$orderData[0]['out_trade_no']);
        $PHPExcel->getActiveSheet()->setCellValue('D3', '打印日期：'.date('Y-m-d'));
        //填充excel表中的数据
        $billsHeader = $this->getBillsHeader();
        $payTypeHeader = $this->getTypeHeader();
        $row = 4;
        $PHPExcel = $this->setHeader($PHPExcel, $billsHeader, $row);
        foreach ($bills as $key => $bill) {
            $row++;
            $PHPExcel->getActiveSheet()->setCellValue('A'.$row, $bill['ch_name']);
            $PHPExcel->getActiveSheet()->setCellValue('B'.$row, $bill['year']. '年' . $bill['month']. '月');
            $PHPExcel->getActiveSheet()->setCellValue('C'.$row, "\t".$bill['money']."\t");
            $PHPExcel->getActiveSheet()->setCellValue('D'.$row, $bill['remark']);
        }
        $row++;
        $PHPExcel->getActiveSheet()->setCellValue('A'.$row, '合计人民币');
        $PHPExcel->getActiveSheet()->setCellValue('C'.$row, '￥：'. $orderData[0]['total'].'元');
        $billRow = $row;
        $row = $row+2;
        $PHPExcel = $this->setHeader($PHPExcel, $payTypeHeader, $row);
        foreach ($orderData as $key => $value) {
            $row++;
            $PHPExcel->getActiveSheet()->setCellValue('A'.$row, $value['type_name']);
            $PHPExcel->getActiveSheet()->setCellValue('B'.$row, "\t".$value['p_total']."\t");
            $PHPExcel->getActiveSheet()->setCellValue('C'.$row, $value['remark']);
        }
        $row++;
        $PHPExcel->getActiveSheet()->setCellValue('A'.$row, '合计人民币');
        $PHPExcel->getActiveSheet()->setCellValue('B'.$row, '￥：'. $orderData[0]['total'].'元');
        $typeRow = $row;
        $row++;
        $PHPExcel->getActiveSheet()->setCellValue('A'.$row, '会计：');
        $PHPExcel->getActiveSheet()->setCellValue('C'.$row, '收款人：'.$orderData[0]['uname']);
        $PHPExcel->getActiveSheet()->setCellValue('D'.$row, '收款账号：'.$orderData[0]['code']);

        $PHPExcel = $this->setForm($PHPExcel, $billRow, $typeRow);
        $fileName = ($type == 'lease') ? date('Y_m_') . $bills[0]['property']. '收款收据.xlsx' : date('Y_m_') . $orderData[0]['cc_name'] . $orderData[0]['b_name'] . $orderData[0]['h_name'] . '收款收据.xlsx';
        header('Content-Type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition:attachment;filename=\"{$fileName}\" ");
        header('Cache-Control:max-age=0');
        $PHPExcelWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
        $PHPExcelWriter->save('php://output');
    }

    //设置excel的格式
    public function setForm($PHPExcel, $billRow, $typeRow){
        //设置全部垂直居中
        $PHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        //设置默认字体大小和宽度自适应
        $PHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(12);
        for ($i=ord('A'); $i < ord('D'); $i++) { 
            $PHPExcel->getActiveSheet()->getColumnDimension(chr($i))->setWidth(16);
        }
        $PHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(24);
        //设置标题
        $PHPExcel->getActiveSheet()->mergeCells('A1:D1');
        $PHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true)->setSize(20);
        $PHPExcel->getActiveSheet()->getStyle('A1:D1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置单元格水平方向的对齐
        $PHPExcel->getActiveSheet()->getStyle('D2:D3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $PHPExcel->getActiveSheet()->getStyle('D'.strval($typeRow+1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        //账单金额单元格和付款方式单元格居右
        for ($i=5; $i <= $billRow ; $i++) {
            $PHPExcel->getActiveSheet()->getStyle('C'.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        }
        for ($i=($billRow+3); $i <= $typeRow; $i++) { 
            $PHPExcel->getActiveSheet()->getStyle('B'.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        }
        //给账单表格和付款方式表格划线
        $PHPExcel->getActiveSheet()->getStyle('A4:D'.$billRow)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $PHPExcel->getActiveSheet()->getStyle('A'.strval($billRow+2).':C'.$typeRow)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        return $PHPExcel;
    }

    //设置excel中表格的表头
    private function setHeader($PHPExcel, $headers, $row){
        $key = ord('A');
        foreach ($headers as $header) {
            $PHPExcel->getActiveSheet()->setCellValue(chr($key).$row, $header);
            $key++;
        }
        return $PHPExcel;
    }
    //获取表头
    private function getBillsHeader(){
        return ['项目', '账期', '金额', '备注'];
    }
    private function getTypeHeader(){
        return ['付款方式', '付款金额', '备注'];
    }
}