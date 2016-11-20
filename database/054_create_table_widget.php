<?php
$dir = dirname(__DIR__);
require $dir.'/ThinkPHP/Library/Vendor/Zh2py/CUtf8_PY.php';
use Zh2Py\CUtf8_PY;
use Think\Model;

$zh2py = new CUtf8_PY();


$datas = array();
$datas[] = array(
    'type' => 'label',
    'name' => '标签',
    'input_name' => $zh2py->encode('标签'),
    'values' => '',
    'default_value' => '',
    'placeholder' => '请输入标签'
);
$datas[] = array(
    'type' => 'hr',
    'name' => '分割线',
    'input_name' => $zh2py->encode('分割线')
);
$datas[] = array(
    'type' => 'text',
    'name' => '姓名',
    'input_name' => $zh2py->encode('姓名'),
    'values' => '',
    'default_value' => '',
    'placeholder' => '请输入姓名'
);
$datas[] = array(
    'type' => 'date',
    'name' => '出生年月',
    'input_name' => $zh2py->encode('出生年月'),
    'values' => '',
    'default_value' => '',
    'placeholder' => '请选择日期'
);
$datas[] = array(
    'type' => 'text',
    'name' => '身份证',
    'input_name' => $zh2py->encode('身份证'),
    'values' => '',
    'default_value' => '',
    'placeholder' => '请输入身份证'
);
$datas[] = array(
    'type' => 'text',
    'name' => '联系电话',
    'input_name' => $zh2py->encode('联系电话'),
    'values' => '',
    'default_value' => '',
    'placeholder' => '请输入联系电话'
);
$datas[] = array(
    'type' => 'text',
    'name' => '户口地',
    'input_name' => $zh2py->encode('户口地'),
    'values' => '',
    'default_value' => '',
    'placeholder' => '请输入户口地'
);
$datas[] = array(
    'type' => 'text',
    'name' => '现居住地',
    'input_name' => $zh2py->encode('现居住地'),
    'values' => '',
    'default_value' => '',
    'placeholder' => '请输入现居住地'
);
$datas[] = array(
    'type' => 'text',
    'name' => '工作单位',
    'input_name' => $zh2py->encode('工作单位'),
    'values' => '',
    'default_value' => '',
    'placeholder' => '请输入工作单位'
);
$datas[] = array(
    'type' => 'radio',
    'name' => '性别',
    'input_name' => $zh2py->encode('性别'),
    'values' => [
        '男',
        '女'
    ],
    'default_value' => '男'
);
$datas[] = array(
    'type' => 'text',
    'name' => '年龄',
    'input_name' => $zh2py->encode('年龄'),
    'values' => '',
    'default_value' => '',
    'placeholder' => '请输入年龄'
);
$datas[] = array(
    'type' => 'date',
    'name' => '结婚时间',
    'input_name' => $zh2py->encode('结婚时间'),
    'values' => '',
    'default_value' => '',
    'placeholder' => '请选择日期'
);
$datas[] = array(
    'type' => 'date',
    'name' => '离婚时间',
    'input_name' => $zh2py->encode('离婚时间'),
    'values' => '',
    'default_value' => '',
    'placeholder' => '请选择日期'
);
$datas[] = array(
    'type' => 'text',
    'name' => '配偶姓名',
    'input_name' => $zh2py->encode('配偶姓名'),
    'values' => '',
    'default_value' => '',
    'placeholder' => '请输入配偶姓名'
);
$datas[] = array(
    'type' => 'radio',
    'name' => '初婚',
    'input_name' => $zh2py->encode('初婚'),
    'values' => [
        '是',
        '否'
    ],
    'default_value' => '是'
);
$datas[] = array(
    'type' => 'radio',
    'name' => '再婚',
    'input_name' => $zh2py->encode('再婚'),
    'values' => [
        '男再女初',
        '女再男初',
        '双方再婚'
    ],
    'default_value' => '男再女初'
);
$datas[] = array(
    'type' => 'radio',
    'name' => '政策内外',
    'input_name' => $zh2py->encode('政策内外'),
    'values' => [
        '内',
        '外'
    ],
    'default_value' => '内'
);
$datas[] = array(
    'type' => 'text',
    'name' => '出生证号',
    'input_name' => $zh2py->encode('出生证号'),
    'values' => '',
    'default_value' => '',
    'placeholder' => '请输入出生证号'
);
$datas[] = array(
    'type' => 'date',
    'name' => '收养时间',
    'input_name' => $zh2py->encode('收养时间'),
    'values' => '',
    'default_value' => '',
    'placeholder' => '请选择日期'
);
$datas[] = array(
    'type' => 'text',
    'name' => '收养证号',
    'input_name' => $zh2py->encode('收养证号'),
    'values' => '',
    'default_value' => '',
    'placeholder' => '请输入收养证号'
);
$datas[] = array(
    'type' => 'text',
    'name' => '征收理由',
    'input_name' => $zh2py->encode('征收理由'),
    'values' => '',
    'default_value' => '',
    'placeholder' => '请输入征收理由'
);
$datas[] = array(
    'type' => 'text',
    'name' => '征收机关',
    'input_name' => $zh2py->encode('征收机关'),
    'values' => '',
    'default_value' => '',
    'placeholder' => '请输入征收机关'
);
$datas[] = array(
    'type' => 'text',
    'name' => '应征金额',
    'input_name' => $zh2py->encode('应征金额'),
    'values' => '',
    'default_value' => '',
    'placeholder' => '请输入应征金额'
);
$datas[] = array(
    'type' => 'text',
    'name' => '已征金额',
    'input_name' => $zh2py->encode('已征金额'),
    'values' => '',
    'default_value' => '',
    'placeholder' => '请输入已征金额'
);
$datas[] = array(
    'type' => 'text',
    'name' => '备注',
    'input_name' => $zh2py->encode('备注'),
    'values' => '',
    'default_value' => '',
    'placeholder' => '请输入备注'
);
$datas[] = array(
    'type' => 'upload',
    'name' => '附件',
    'input_name' => $zh2py->encode('附件')
);

try {
    // 连接MongoDB
    $mongo = new MongoClient();
    // 连接wg库下的fx_widget集合
    $collection = $mongo->wg->fx_widget;
    // 删除集合
    $collection->drop();
    
    // 批量添加文档
    $collection->batchInsert($datas);
    echo 'Collection has been successful created!';
    foreach ($datas as $data) {
        echo $data['_id'] . 'has been insert into this collection!' . PHP_EOL;
    }
    exit();
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    exit();
}