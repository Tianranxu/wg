<?php
define('COMPANY_MANAGE', env('COMPANY_MANAGE', 3));   //物业管理员ID
define('REPAIR_MANAGE', env('REPAIR_MANAGE', 6));   //维修管理员ID
define('WORK_MANAGE', env('WORK_MANAGE', 9));   //工作站管理员ID
define('DEFAULT_USER', env('DEFAULT_USER', 2));    //普通用户ID
define('UPLOAD_PATH', env('IMPORT_UPLOAD_PATH', '/Public/updata/excel/'));   //上传文件保存路径
define('ROOT', dirname(__FILE__) . '/');

return array(
    /***********收费管理********************/
    //收费项目，包括计费方式（单价*数量和定额）、计量方式（建筑面积、套内面积、分摊面积）
    'CHARGES' => array(
        //id   =>  value 涉及到计量方式的，为避免和数据库中的相关id（如仪表类的id）重复，改为负数，后面如有添加，则从-8开始
        '1' => '单价*面积',
        '2' => '单价*读数',
        '3' => '单价*数量',
        '4' => '定额',
        '-5' => '建筑面积',
        '-6' => '套内面积',
        '-7' => '分摊面积',
    ),

    /**数据库操作标识**/
    'NO_DATA' => -1,
    'OPERATION_SUCCESS' => 2,
    'OPERATION_FAIL' => 1,

    //初云社区物业管理系统配置
    'CHUYUN_APPID' => env('CHUYUN_APPID', 'wx717ca718b86bf895'),
    'CHUYUN_APPSECRET' => env('CHUYUN_APPSECRET', '0c79e1fa963cd80cc0be99b20a18faeb'),
    'CHUYUN_TOKEN' => env('CHUYUN_TOKEN', 'chuyun_thirdparty'),
    'CHUYUN_ENCODINGAESKEY' => env('CHUYUN_ENCODINGAESKEY', 'dVfZOGNBWA56ZL2ZUOeMl9H5eH7LrQqY3odxf3Rrf8N'),

    'DEBUG' => true,

    //表单令牌相关配置参数
    'TOKEN_ON' => true, //是否开启令牌验证，默认关闭
    'TOKEN_NAME' => '__hash__', //令牌验证的表单隐藏字段名称，默认为__hash__
    'TOKEN_TYPE' => 'md5',  //令牌哈希验证规则，默认MD5
    'TOKEN_RESET' => true,  //令牌验证出错后是否重置令牌，默认true

    //故障的状态
    'FAULT_STATUS' => array(
        'NOT_YET' => -1,        //未接单
        'CATCHED' => 1,         //已接单
        'REPAIRED' => 2,        //已修复
        'EVALUATED' => 3,       // 已评价
        'SHIFTED' => 4,             //已转单
        'FINISH' => 5,                //结单  
        'CANCEL' => -9,             //微信用户取消
        'HANGED' => -4,             //挂起
    ),
    //故障超时状态
    'FAULT_OVERTIME' => [
        'OVERTIME' => 1,         //超时重发
        'OVERTIME_TWICE' => 2, //超时两次转后台
        'OVERTIME_REPAIR' => 3, //超时修复
    ],

    //故障的类型
    'FAULT_TYPE' => array(
        'PC' => 1,
        'WECHAT' => 2,
    ),
    //初云快修号
    'CHUYUN' => array(
        'appid' => 'wx5bb63bdbafc12fb5',
        'secret' => '6409345ec3a9ddf7a4b8ab91b81fe813',
    ),
    //维修员分组号
    'GROUP' => array(
        'default' => 1,
        'examine' => 2,
    ),

    /**导入设置**/
    //导入类型
    'IMPORT_TYPE' => array(
        'METER' => 1,
        'CAR' => 2,
        'CUSTOMER' => 3,
        'HOUSE' => 4,
        'BILLS' => 5
    ),
    //导入状态
    'IMPORT_STATUS' => array(
        'IMPORT_ING' => -1,
        'IMPORT_FAIL' => -2,
        'IMPORT_SUCCESS' => 1
    ),
    //维修员状态
    'REPAIR_STATUS' => array(//1:待审核 2：审核通过 3：审核不通过 -1:未注册
        'PENDING' => 1,
        'PASS' => 2,
        'NOT_PASS' => 3,
        'NOT_REGI' => -1
    ),
    //access_token过期时间
    'EXPIRES_IN' => 6480,

    //维修员公众号，测试时为初云实验室（默认），上线后是初云维修员
    'REPAIR_PUBLICNO' => array(
        'APPID' => env('RP_APPID', 'wx5bb63bdbafc12fb5'),
        'APPSECRET' => env('RP_APPSECRET', '6409345ec3a9ddf7a4b8ab91b81fe813'),
    ),

    'TITLE_TYPE' => array(
        'LIST' => 1,
        'PERSONAL' => 2,
        'REPAIRING' => 3,
    ),
    //故障统计类型
    'STATISTICS' => [
        1 => 'week',
        2 => 'month',
        3 => 'year',
        4 => 'custom'
    ],

    'RANKING' => [
        1 => '差',
        2 => '合格',
        3 => '一般',
        4 => '良好',
        5 => '优秀',
    ],

    //维修公司超时设置类型
    'FAULT_LIMIT_TYPE' => [
        'CATCH_LIMIT_TYPE' => env('CATCH_LIMIT_TYPE', 1),
        'REPAIR_LIMIT_TYPE' => env('REPAIR_LIMIT_TYPE', 2),
    ],
    //维修公司超时设置默认值
    'FAULT_LIMIT_DEFAULT' => [
        'CATCH_LIMIT_TIMEOUT' => env('CATCH_LIMIT_TIMEOUT', 10),
        'REPAIR_LIMIT_TIMEOUT' => env('REPAIR_LIMIT_TIMEOUT', 180)
    ],

    //企业类型
    'COMPANY_TYPE' => array(
        'PROPERTY' => 1,            //物业公司
        'REPAIR' => 2,                  //维修公司
        'WORKSTATION' => 3,      //工作站
    ),
    //角色类型
    'ROLE_TYPE' => array(
        'SYS' => 1,
        'PROPERTY' => 2,
        'REPAIR' => 3,
        'WORK' => 4
    ),

    //付款方式
    'PAY_TYPE' => [
        'CASH_PAY' => [
            'VALUE' => 1,
            'NAME' => '现金'
        ],
        'CARD_PAY' => [
            'VALUE' => 2,
            'NAME' => '刷卡'
        ],
        'BANK_PAY' => [
            'VALUE' => 3,
            'NAME' => '银行卡'
        ],
        'WEIXIN_PAY' => [
            'VALUE' => 4,
            'NAME' => '微信支付'
        ],
        'OTHER_PAY' => [
            'VALUE' => 99,
            'NAME' => '其他'
        ],
    ],

    'PAY_TYPE_READ' => [
        1 => '现金',
        2 => '刷卡',
        3 => '银行卡',
        4 => '微信支付',
        99 => '其他',
    ],


    //账单类型：房产和车辆
    'BILL_TYPE' => [
        'PROPERTY' => 1,
        'CAR' => 2,
        'LEASE' => 3
    ],

    //账单缴费状态
    'BILL_STATUS' => [
        'GENERATED' => -1,        //已生成，未发布
        'PUBLISHED' => 1,           //已发布，未缴费
        'PAYED' => 2,                   //已缴费      
    ],

    //房源户型
    'ROOM_TYPE' => [
        1 => '一室',
        2 => '二室',
        3 => '三室',
        4 => '四室',
        5 => '四室以上',
        99 => '其他',
    ],

    //房源类型
    'TYPE_DEMAND' => [
        1 => '纯住宅',
        2 => '纯办公',
        3 => '住宅改办公',
    ],

    //装修说明
    'FURNISH_TYPE' => [
        1 => '毛坯',
        2 => '简装',
        3 => '精装',
        4 => '豪装',
        99 => '其他',
    ],

    //客源状态
    'CUSTOMER_STATUS' => [
        1 => '未签约',
        2 => '已签约',
        3 => '中止委托',
    ],

    'INTENTION' => [
        1 => '求租',
        2 => '求购',
    ],

    //跟进方式
    'FOLLOW_TYPE' => [
        1 => '普通跟进',
        2 => '重点跟进',
        3 => '放弃房源'
    ],

    //房源状态
    'ROOM_STATUS' => [
        1 => '待租',
        2 => '已租',
        3 => '中止托管',
    ],

    //合约状态
    'CONTRACT_STATUS' => [
        -2 => '已中止',
        -1 => '已到期',
        1 => '已生效',
    ],

    //预警类型
    'WARNING_TYPE' => [
        'CONTRACT' => 1,    //合同
        'BILL' => 2,              //账单
        'ROOM' => 3,           //房源
    ],

    //表单审核状态
    'FORM_STATUS' => [
        '-1' => '未审核',
        '1' => '通过',
        '2' => '不通过',
    ],

    //系统消息通知类型
    'NOTICE_TYPE' => [
        ['type' => 1, 'name' => 'unacceptFault', 'url' => __ROOT__.'/fault/faultList/lt/15.html?st=-1&compid=','content'=>'您有新的超时未接故障，请及时处理！'],
        ['type' => 2, 'name' => 'feedback','url'=>__ROOT__.'/feedback/index/compid/','content'=>'您有新的意见反馈，请及时处理！'],
        ['type' => 3, 'name' => 'uncheckForm','url'=>__ROOT__.'/form/index.html?compid=','content'=>'您有新的待审核表单，请及时处理！'],
        ['type' => 4, 'name' => 'weixinPropertyPay','url'=>__ROOT__.'/pay/payDetails.html?compid=','content'=>'您有新的微信用户物业费缴费记录！'],
        ['type' => 5, 'name' => 'weixinCarPay','url'=>__ROOT__.'/pay/carPayDetails.html?compid=','content'=>'您有新的微信用户停车费缴费记录！'],
        ['type' => 6, 'name' => 'shiftFault', 'url' => __ROOT__ . '/fault/faultList/lt/15.html?st=4&compid=', 'content' => '您有新的转单故障，请及时处理！'],
    ],

    //系统消息发送类型
    'NOTICE_SEND_TYPE' => [
        2=>['name'=>'Feedback','value'=>246],
        3=>['name'=>'UncheckForm','value'=>131],
        4=>['name'=>'WeixinPropertyPay','value'=>273],
        5=>['name'=>'WeixinCarPay','value'=>275],
    ],

//微信模板消息(记录短id)
    'TEMPLATE_MSG' => [
        'SUBMITED_FORM' => 'OPENTM207135098',               //微信提交表单后通知客服
        'CHECKED_FORM' => 'OPENTM207276863',              //表单审核后结果通知用户
        'FEEDBACK' => 'OPENTM206165478',                        //反馈回复后通知用户以及微信提交反馈后通知客服
        'SUBMITED_FEE' => 'OPENTM401013908',               //微信用户缴费后通知客服 
        'FAULT' => 'OPENTM204060969',            //第二次超时未接故障通知客服以及转单故障通知客服
    ],

    //微信用户类型
    'WXUSER_TYPE' => [
        'USER' => 1,                              //普通用户
        'FEEDBACK_MG' => 2,             //反馈管理员
        'FORM_MG' => 3,                   //表单管理员
        'REPAIR_MG' => 4,                   //维修管理员
        'CHARGE_MG' => 5,               //收费管理员
    ],

    //消息模板公众号类型
    'PUBLICNO_TYPE' => [
        'PROPERTY' => 1,        //物业号
        'REPAIR' => 2,             //维修号
    ],

    //压缩图片尺寸
    'COMPRESS_SIZE' => [
        'first' => 1024,
        'second' => 256
    ],
);

