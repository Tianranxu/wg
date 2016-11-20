<?php
/*************************************************
 * 文件名：UploaderController.class.php
 * 功能：     上传文件控制器
 * 日期：     2016.03.17
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;

use Think\Controller;

class UploaderController extends Controller
{

    /**
     * 企业ID
     * @var
     */
    protected $compid;

    /**
     * 上传文件的所属类型 imgtxt-图文信息 piclibrary-图片库 affairs-填写表格
     * @var
     */
    protected $type;

    /**
     * POST参数必须接受的值
     * @var array
     */
    protected $keyMap = ['original_filename', 'type', 'exts'];

    /**
     * 允许上传的MIME类型
     * @var array
     */
    protected $keyMimes = [
        'image' => [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        ],
        'file' => []
    ];

    /**
     * Upload类配置
     * @var
     */
    protected $uploadConfig;

    /**
     * 允许上传的文件的后缀对应的MIME类型
     * @var
     */
    protected $uploadMimes;

    /**
     * 初始化
     */
    public function _initialize()
    {
        $this->compid = I('get.compid', '');
        $this->type = I('post.type', '');
        if (!$this->compid) exit($this->getExitDatas(false, '参数错误，请检查参数', null));
    }

    /**
     * 上传
     */
    public function index()
    {
        //接收并过滤数据
        $postDatas = $this->handlePostDatas(I('post.', ''));
        if (!$postDatas) exit($this->getExitDatas(false, '参数错误，请检查参数', null));
        //配置上传参数
        $this->handleUploadConfig(explode(',', $postDatas['exts']));
        //上传文件
        $info = $this->uploadFile($_FILES['upload_file']);
        if (!is_array($info)) exit($this->getExitDatas(false, '上传失败', null));
        $savePaths = $this->uploadConfig['rootPath'] . $this->uploadConfig['savePath'] . $info['savename'];
        //压缩图片
        if (in_array($info['type'], $this->keyMimes['image'])) {
            $savePaths = $this->compressImage($info);
            if (!$savePaths) exit($this->getExitDatas(false, '图片压缩失败', null));
            //新增/编辑图文上传的图片同时添加到图片库
            if ($this->type == 'imgtxt' && !($this->addPictureToPiblibrary($savePaths))) {
                @unlink($savePaths['path']);
                @unlink($savePaths['thumbPath']);
            }
        }
        exit((is_array($info)) ? $this->getExitDatas(true, '上传成功', $savePaths) : $this->getExitDatas(false, $info, null));
    }

    /**
     * 获取返回请求信息
     * @param boolean $success 是否成功，是-true 否-false
     * @param string $msg 返回信息
     * @param mixed $filePath 返回的文件路径，默认为空
     * @return string
     */
    protected function getExitDatas($success, $msg, $filePath = null)
    {
        return json_encode(['success' => $success, 'msg' => $msg, 'file_path' => $filePath]);
    }

    /**
     * 处理POST数据
     * @param array $postDatas POST数据
     * @return bool
     */
    protected function handlePostDatas(array $postDatas)
    {
        if (empty($postDatas)) return false;
        foreach ($postDatas as $post => $postData) {
            foreach ($this->keyMap as $mapKey => $mapName) {
                if (!$postDatas[$mapName]) return false;
            }
        }
        return $postDatas;
    }

    /**
     * 根据文件后缀获取MIME类型
     * @param array $exts
     */
    protected function getUploadMimes(array $exts)
    {
        foreach ($exts as $ext) {
            foreach ($this->keyMimes as $keyMime) {
                if (!$keyMime[$ext]) continue;
                $this->uploadMimes[] = $keyMime[$ext];
            }
        }
        $this->uploadMimes = array_unique($this->uploadMimes);
    }

    /**
     * 获取上传路径
     * @return string
     */
    protected function getSavePath()
    {
        return $this->type . '/' . $this->compid . '/' . date('Y') . '/' . date('m') . '/' . date('d') . '/';
    }

    /**
     * 设置上传类配置
     * @param array $exts 允许上传的文件后缀
     * @return array
     */
    protected function handleUploadConfig(array $exts)
    {
        $this->getUploadMimes($exts);
        return $this->uploadConfig = [
            'maxSize' => 5242880,
            'exts' => $exts,
            'mimes' => $this->uploadMimes,
            'rootPath' => 'Uploads/',
            'savePath' => $this->getSavePath(),
            'autoSub' => false
        ];
    }

    /**
     * 上传文件
     * @param array $fileDatas 文件信息
     * @return array|string
     */
    protected function uploadFile(array $fileDatas)
    {
        $upload = new \Think\Upload($this->uploadConfig);
        $info = $upload->uploadOne($fileDatas);
        return $info ? $info : $upload->getError();
    }

    /**
     * 压缩图片
     * @param array $info 图片信息
     * @return bool
     */
    public function compressImage(array $info)
    {
        $filePath = $this->uploadConfig['rootPath'] . $info['savepath'] . $info['savename'];
        $savePath = $this->uploadConfig['rootPath'] . $info['savepath'] . 'thumbnail/';
        $compressInfo = A('compress')->compressImage($filePath, $filePath, $savePath);
        return $compressInfo ? $compressInfo : false;
    }

    /**
     * 将图文信息上传的图片添加到图片库
     * @param array $savePaths
     * @return bool
     */
    public function addPictureToPiblibrary(array $savePaths)
    {
        $piclibraryResult = D('piclibrary')->addPicture($this->compid, substr($savePaths['path'], (strrpos($savePaths['path'], '/') + 1)), $savePaths['path'], $savePaths['thumbPath']);
        return $piclibraryResult ? true : false;
    }
}