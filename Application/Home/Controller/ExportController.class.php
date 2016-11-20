<?php
/*************************************************
 * 文件名：ExportController.class.php
 * 功能：     导出控制器
 * 日期：     2016.02.03
 * 作者：     XU
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
use Think\Controller;
namespace Home\Controller;

class ExportController extends AccessController{
    //初始化方法
    public function _initialize(){
        parent::_initialize();
    }

    //导出主页面
    public function index(){
        $type = I('get.type');
        $id = I('get.id');
        $function = new \ReflectionMethod(get_called_class(), 'export'.ucwords($type));
        $function->invoke($this, $id);
    }

    //设置表头
    public function setHead($heads, $PHPExcel){
        $i = ord('A');
        foreach ($heads as $key => $head) {
            $PHPExcel->getActiveSheet()->setCellValue(chr($i).'1', $head);
            //内容自适应
            $PHPExcel->getActiveSheet()->getColumnDimension(chr($i))->setWidth(20);
            $i++;
        }
        return $PHPExcel;
    }

    //填充表格内容
    public function setTable($dataList, $PHPExcel){
        $i = 2;
        foreach ($dataList as $key => $data) {
            $j = ord('A');
            foreach ($data as $k => $v) {
                $PHPExcel->getActiveSheet()->setCellValue(chr($j).strval($i), $v);
                $j++;
            }
            $i++;
        }
        return $PHPExcel;
    }

    //将表格输出自动下载（包括设置表名）
    public function outputTable($fileName, $PHPExcel){
        header('Content-Type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition:attachment;filename=\"{$fileName}\" ");
        header('Cache-Control:max-age=0');
        $PHPExcelWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
        $PHPExcelWriter->save('php://output');
    }

    //导出表单
    public function exportForm($form_id){
        $formData = D('Form')->getFormData($form_id);
        // 导入PHPExcel类库
        import("Org.Util.PHPExcel");
        // 创建PHPExcel对象
        $PHPExcel = new \PHPExcel();
        //组装数据
        $heads = [];
        $dataList = [];
        foreach ($formData as $key => $data) {
            $fileName = $data['form_name'].'.xlsx'; //文件名
            foreach ($data['fields'] as $k => $v) {
                if ($v['name'] == '标签' || $v['name'] == '分割线') continue;
                $dataList[$key][$v['name']] = $v['value'];  //表格内容
            }
            $dataList[$key]['审批状态'] = C('FORM_STATUS')[$data['approval_status']];
        }
        foreach ($dataList as $key => $data) {
            //表头
            foreach($data as $k => $v){
                $heads[] = $k;
            }
            break;
        }
        //设置全部垂直居中
        $PHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $this->setHead($heads, $PHPExcel);
        $this->setTable($dataList, $PHPExcel);
        $this->outputTable($fileName, $PHPExcel);
    }
}