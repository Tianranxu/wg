<?php
/*************************************************
 * 文件名：CompressController.class.php
 * 功能：     图片压缩控制器
 * 日期：     2016.03.22
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;

use Think\Controller;

class CompressController extends Controller
{

    /**
     * 第一次压缩图片尺寸
     * @var
     */
    protected $firstCompressSize;

    /**
     * 第二次压缩图片尺寸
     * @var
     */
    protected $secondCompressSize;

    /**
     * 初始化
     */
    public function _initialize()
    {
        $this->firstCompressSize = C('COMPRESS_SIZE')['first'];
        $this->secondCompressSize = C('COMPRESS_SIZE')['second'];
    }

    /**
     * 压缩图片
     * @param string $filePath 原图路径
     * @param string $firstPath 第一次压缩图片的保存路径
     * @param string $secondPath 第二次压缩图片的保存路径
     * @return bool|mixed
     */
    public function compressImage($filePath, $firstPath, $secondPath)
    {
        list($width, $height) = getimagesize($filePath);
        $newSizeInfo = $this->handleNewImageWidthAndHeight($width, $height, $this->firstCompressSize, $this->secondCompressSize);
        if (!$newSizeInfo) return $this->noCompress($filePath, $secondPath) ? ['path' => $firstPath, 'thumbPath' => $secondPath . substr($firstPath, (strrpos($firstPath, '/') + 1))] : false;
        $function = new \ReflectionMethod(get_called_class(), 'compress' . ucfirst($newSizeInfo['type']));
        $result = $function->invoke($this, ['width' => $width, 'height' => $height], $newSizeInfo['size'], $filePath, ['first' => $firstPath, 'second' => $secondPath]);
        return $result ? ['path' => $firstPath, 'thumbPath' => $secondPath . substr($firstPath, (strrpos($firstPath, '/') + 1))] : false;
    }

    /**
     * 设置压缩图片的宽高
     * @param int $width 原图的宽度
     * @param int $height 原图的高度
     * @param int $firstCompressSize 第一次压缩图片的尺寸
     * @param int $secondCompressSize 第二次压缩图片的尺寸
     * @return array|bool
     */
    protected function handleNewImageWidthAndHeight($width, $height, $firstCompressSize, $secondCompressSize)
    {
        if ($width > $firstCompressSize || $height > $firstCompressSize) return $this->handleFirstWidthAndHeight($width, $height, $firstCompressSize);
        if ($width <= $firstCompressSize && $height <= $firstCompressSize) return $this->handleSecondWidthAndHeigh($width, $height, $secondCompressSize);
        return false;
    }

    /**
     * 设置第一次压缩图片的宽高
     * @param int $width 原图的宽度
     * @param int $height 原图的宽度
     * @param int $firstCompressSize 第一次压缩图片的尺寸
     * @return array|bool
     */
    public function handleFirstWidthAndHeight($width, $height, $firstCompressSize)
    {
        if (($width > $firstCompressSize && $height > $firstCompressSize && $height > $width)) return ['type' => 'first', 'size' => ['width' => ($firstCompressSize / $height) * $width, 'height' => $firstCompressSize]];
        if ($width > $firstCompressSize) return ['type' => 'first', 'size' => ['width' => $firstCompressSize, 'height' => ($firstCompressSize / $width) * $height]];
        if ($height > $firstCompressSize) return ['type' => 'first', 'size' => ['width' => ($firstCompressSize / $height) * $width, 'height' => $firstCompressSize]];
        return false;
    }

    /**
     * 设置第二次压缩图片的宽高
     * @param int $width 原图/第一次压缩图片的宽度
     * @param int $height 原图/第二次压缩图片的高度
     * @param int $secondCompressSize 第二次压缩图片的尺寸
     * @return array|bool
     */
    public function handleSecondWidthAndHeigh($width, $height, $secondCompressSize)
    {
        if ($width > $secondCompressSize && $height > $secondCompressSize && $height > $width) return ['type' => 'second', 'size' => ['width' => ($secondCompressSize / $height) * $width, 'height' => $secondCompressSize]];
        if ($width > $secondCompressSize) return ['type' => 'second', 'size' => ['width' => $secondCompressSize, 'height' => ($secondCompressSize / $width) * $height]];
        if ($height > $secondCompressSize) return ['type' => 'second', 'size' => ['width' => ($secondCompressSize / $height) * $width, 'height' => $secondCompressSize]];
        return false;
    }

    /**
     * 第一次压缩图片
     * @param array $originalSize 原图尺寸
     * @param array $sizes 压缩图片尺寸
     * @param string $filePath 原图路径
     * @param array $savePath 压缩图片保存路径
     * @return bool
     */
    public function compressFirst(array $originalSize, array $sizes, $filePath, array $savePath)
    {
        //压缩图片同时覆盖原图
        $return = $this->handleCompress($originalSize, $sizes, $filePath, $savePath['first']);
        if (!$return) return false;
        $newSizeInfo = $this->handleSecondWidthAndHeigh($sizes['width'], $sizes['height'], $this->secondCompressSize);
        if (!$newSizeInfo) {
            //删除原图
            @unlink($filePath);
            return false;
        }
        $result = $this->compressSecond(['width' => $sizes['width'], 'height' => $sizes['height']], ['width' => $newSizeInfo['size']['width'], 'height' => $newSizeInfo['size']['height']], $filePath, $savePath['second']);
        return $result ? true : false;
    }

    /**
     * 创建缩略图目录
     * @param string $savePath 保存缩略图的路径
     * @return bool
     */
    public function createThumbnail($savePath)
    {
        if (is_dir($savePath)) return $savePath;
        $result = mkdir($savePath, 0755);
        return $result ? true : false;
    }

    /**
     * 第二次压缩图片
     * @param array $originalSize 原图尺寸
     * @param array $sizes 压缩图片尺寸
     * @param string $filePath 原图路径
     * @param array|string $savePath 压缩图片保存路径
     * @return bool
     */
    public function compressSecond(array $originalSize, array $sizes, $filePath, $savePath)
    {
        $savePath = (is_array($savePath)) ? $savePath['second'] : $savePath;
        if (!($this->createThumbnail($savePath))) return false;
        $return = $this->handleCompress($originalSize, $sizes, $filePath, $savePath . substr($filePath, strrpos($filePath, '/')));
        if (!$return) {
            @unlink($filePath);
            return false;
        }
        return true;
    }

    /**
     * 无需压缩图片，直接复制图片到缩略图目录
     * @param string $filePath 原图路径
     * @param string $savePath 保存路径
     * @return bool
     */
    public function noCompress($filePath, $savePath)
    {
        $fileName = substr($filePath, (strrpos($filePath, '/') + 1));
        $thumbNail = $this->createThumbnail($savePath);
        if (!$thumbNail) return false;
        $result = copy($filePath, $savePath . $fileName);
        return $result ? true : false;
    }

    /**
     * 处理压缩图片
     * @param array $originalSize 原图尺寸
     * @param array $sizes 压缩图片尺寸
     * @param string $filePath 原图路径
     * @param string $savePath 压缩图片保存路径
     * @return bool
     */
    public function handleCompress(array $originalSize, array $sizes, $filePath, $savePath)
    {
        //新建一个真彩色图像
        $imageWp = imagecreatetruecolor($sizes['width'], $sizes['height']);
        //由文件或 URL 创建一个新的JPEG图象
        $image = imagecreatefromjpeg($filePath);
        //重采样拷贝部分图像并调整大小
        $copy = imagecopyresampled($imageWp, $image, 0, 0, 0, 0, $sizes['width'], $sizes['height'], $originalSize['width'], $originalSize['height']);
        //输出JEPG图象到浏览器或文件
        $return = imagejpeg($imageWp, $savePath);
        return $return ? true : false;
    }
}