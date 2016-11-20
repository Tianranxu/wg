<?php
/*************************************************
 * 文件名：QrcodeController.class.php
 * 功能：     二维码生成控制器
 * 日期：     2016.03.18
 * 作者：     XU
 * 版权：     Copyright @ 2016 风馨科技 All Rights Reserved
 ***********************************************/

namespace Home\Controller;
use phpqrcode\QRcode;
use Think\Controller;
use Predis\Client;

class QrcodeController extends Controller{
    public function __initialize(){
    }
    /**
     * 生成二维码(若二维码已存在，则从redis中获取)
     * @param strting $content 二维码存放的信息
     * @param string $pathname 二维码存放的路径以及名称
     * @param string $logo 二维码中间的logo
     * @param int $size 二维码的大小
     * @param int $margin 二维码的边缘空白大小
     * @param string $key 二维码图片在redis中的key
     */
    public function getQRcode($content, $path, $logo='', $size, $margin=2, $key){
        //从redis中检测二维码是否已经存在，避免重复生成二维码
        $redis = D('base')->connectRedis();
        $code_path = $redis->get($key);
        if($code_path){
            D('base')->disConnectRedis();
            return $code_path;
        } 
        vendor('phpqrcode.phpqrcode');
        $qrcode = new QRcode();
        $pathname = $path.'/'.time().mt_rand(0, 99).'.png';
        //生成二维码
        $qrcode->png($content, $pathname, 'L', $size, $margin);
        $pathname = $logo ? $this->addLogo($logo, $path, $pathname) : $pathname;
        $redis->set($key, $pathname);
        D('base')->disConnectRedis();
        return $pathname;
    }

    //添加logo
    public function addLogo($logo, $path, $pathname){
        $QR_img = imagecreatefromstring(file_get_contents($pathname));
        $logo_img = imagecreatefromstring(file_get_contents($logo));
        $QR_width = imagesx($QR_img);//二维码图片宽度   
        $QR_height = imagesy($QR_img);//二维码图片高度   
        $logo_width = imagesx($logo_img);//logo图片宽度   
        $logo_height = imagesy($logo_img);//logo图片高度
        $scale = $logo_width/$logo_qr_width;   
        $logo_qr_height = $logo_height/$scale;
        $from_width = ($QR_width - $logo_qr_width) / 2;
        imagecopyresampled($QR_img, $logo_img, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
        $pathname = $path.'/'.time().mt_rand(0, 99).'.png';
        imagepng($QR_img, $pathname);
        return $pathname;
    }
}




