<?php

/**
 * 返回JSON信息
 * @param string $flag              返回状态
 * @param unknown $data        返回数据
 * @param string $msg              返回信息
 * @param string $detailMsg     返回详细信息
 * @param string $number        返回状态码
 */
function retMessage($flag = false, $data = array(), $msg = "成功", $detailMsg = "成功", $number = "2000")
{
    $result = array();
    $result['number'] = $number;
    $result['flag'] = $flag;
    $result['msg'] = $msg;
    $result['detailMsg'] = $detailMsg;
    $result['data'] = $data;
    exit(json_encode($result));
}

function var_show($cont)
{
    echo "<pre>";
    var_dump($cont);
    echo "</pre>";
}

/**
 * 生成随机密码
 *
 * @param string $length
 *            指定长度
 * @param string $characters
 *            要生成字符集
 */
function random_password($length, $characters = 'abcdefgh1234567890')
{
    if ($characters == '') {
        return '';
    }
    $chars_length = strlen($characters) - 1;
    
    mt_srand((double) microtime() * 1000000);
    
    $pwd = '';
    while (strlen($pwd) < $length) {
        $rand_char = mt_rand(0, $chars_length);
        $pwd .= $characters[$rand_char];
    }
    
    return $pwd;
}
// 开始发送临时密码短信
function sendMsg($SendTemplateSMS, $phone, $temp_pass, $userid)
{
    $result = $SendTemplateSMS->sendTemplateSMS($phone, $temp_pass, SMS_TEMPLETE);
    if ($result->statusCode == 0) {
        $userMod = D('user');
        $addTimesResult = $userMod->increase_sms_times($userid);
        return true;
    }
    return false;
}

/**
 * 导入客户日志
 *
 * @param string $compid
 *            企业ID
 * @param string $fileName
 *            导入文件名称
 * @param string $account
 *            导入账号
 * @param string $personnel
 *            导入人员
 * @param string $time
 *            导入时间
 * @param string $success
 *            成功数
 * @param string $fail
 *            失败数
 * @param string $error
 *            错误信息
 */
function client_log($compId, $fileName, $account, $personnel, $time, $success = '', $fail = '', $error = '', $type = 3)
{
    $logMod = D('importlog');
    $data['cm_id'] = $compId;
    $data['name'] = $fileName;
    $data['code'] = $account;
    $data['user_name'] = $personnel;
    $data['import_time'] = $time;
    $data['success'] = $success;
    $data['failures'] = $fail;
    $data['error_no'] = $error;
    $data['il_type'] = $type;
    $data['create_time'] = date('Y-m-d H:i:s');
    
    $result = $logMod->add($data);
    
    if ($result) {
        return true;
    } else {
        return false;
    }
}

/**
 * 中英文截取字符串
 * @param string $str
 * @param number $start
 * @param number $length
 * @param string $charset
 * @param boolean $suffix
 * @return unknown|string
 */
function csubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
{
    if (function_exists("mb_substr")) {
        if (mb_strlen($str, $charset) <= $length) return $str;
        $slice = mb_substr($str, $start, $length, $charset);
    } else {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        if (count($match[0]) <= $length) return $str;
        $slice = join("", array_slice($match[0], $start, $length));
    }
    if ($suffix) return $slice . "…";
    return $slice;
}

/**
 * 打印数据并exit，同laravel的dd()函数
 * @param mixed $mixed
 */
function dd($mixed)
{
    dump($mixed);
    exit;
}






















