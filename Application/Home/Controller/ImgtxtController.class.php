<?php
/*************************************************
 * 文件名：ImgtxtController.class.php
 * 功能：     图文信息控制器
 * 日期：     2015.10.20
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;

class ImgtxtController extends AccessController
{

    protected $categoryModel;

    protected $imgtxtModel;

    protected $imgtxttempModel;

    protected $piclibraryModel;
    
    protected $companyModel;
    
    protected $formModel;

    /**
     * 初始化
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->categoryModel = D('category');
        $this->imgtxtModel = D('imgtxt');
    }

    /**
     * 上传图片
     * @param int $compid 企业ID
     * @return array|string
     */
    public function uploadImages($compid)
    {
        $upload = new \Think\Upload();
        $upload->maxSize = 2097152;
        $upload->exts = ['jpg', 'jpeg', 'png'];
        $upload->mimes = ['image/jpeg', 'image/png'];
        $upload->rootPath = './Uploads/';
        $upload->savePath = 'imgtxt/' . $compid . date('Y') . '/' . date('m') . '/' . date('d') . '/';
        $upload->autoSub = false;
        $info = $upload->uploadOne($_FILES['imgtxtContentImages']);
        return $info ? $info : $upload->getError();
    }

    /**
     * 获取上传图片
     */
    public function getUploadImages()
    {
        if (!$this->companyID) exit(json_encode(['success' => false, 'msg' => '', 'file_path' => null]));
        $upload = $this->uploadImages($this->companyID);
        (is_array($upload)) ? exit(json_encode(['success' => true, 'msg' => $upload, 'file_path' => __ROOT__ . "/Uploads/{$upload['savepath']}{$upload['savename']}"])) : exit(json_encode(['success' => false, 'msg' => $upload, 'file_path' => null]));
    }
    
    /**
     * 图文信息页面
     */
    public function index()
    {
        // 接收参数
        $page = I('get.page', 1);
        $category = I('get.category', '');
        $keywords = I('get.keywords', '');
        // 查询该企业下的图文信息列表
        $imgtxtResult = $this->imgtxtModel->getImgtxtList($this->companyID, ($page - 1) * 10, 10, $category, $keywords);
        $imgtxtList = $imgtxtResult['list'];
        foreach ($imgtxtList as $id => $imgtxt) {
            $imgtxtIds[] = $id;
        }
        // 查询该企业下的图文信息分类列表
        $categoryList = $this->categoryModel->getNewsist($this->companyID);
        // 查询图文信息对应的分类
        if ($imgtxtIds) {
            $this->imgtxttempModel = D('imgtxttemp');
            $imgtxtTempList = $this->imgtxttempModel->getNewsTempList($imgtxtIds);
        }
        
        // 重新组装数据
        foreach ($imgtxtList as $i => $imgtxt) {
            // 格式化日期
            $imgtxtList[$i]['update_time'] = date('Y年m月d日', strtotime($imgtxt['update_time']));
            
            // 组装分类
            foreach ($imgtxtTempList as $imgtxtTemp) {
                if ($imgtxt['_id'] == $imgtxtTemp['media_id']) {
                    $imgtxtList[$i]['category_name'] = $imgtxtTemp['category_name'];
                    continue;
                }
            }
        }
        //查看企业类型
        $companyType=$this->getCompanyType();
        //dd($imgtxt);
        $this->assign('imgtxtList', $imgtxtList);
        $this->assign('listCount', $imgtxtResult['total']);
        $this->assign('totalPages', ceil($imgtxtResult['total'] / 10));
        $this->assign('categoryList', $categoryList);
        $this->assign('companyType',$companyType);
        $this->display();
    }

    /**
     * 新建/编辑图文信息页面
     */
    public function imgtxt()
    {
        // 接收数据
        $type = I('get.type', 'add');
        // 查询该企业下的图文信息分类列表
        $categoryList = $this->categoryModel->getNewsist($this->companyID);
        //查询该企业是否为工作站
        $companyType=$this->getCompanyType();
        $this->formModel=D('form');
        //查询该工作站下的公用表单和该用户的个人表单
        $formList=$this->formModel->getAllPublishForms($this->companyID,$this->userID);
        $this->assign('formList',$formList);

        //调用图片库
        $piclibraryController=A('piclibrary');
        $piclibraryResult=$piclibraryController->getPiclibrary();
        
        // 编辑图文信息
        if ($type == 'edit') {
            $id = I('get.id', '');
            $imgtxtInfo = $this->imgtxtModel->getImgtxtList($this->companyID, 0, 1, '', '', $id)['list'][$id];
            $this->piclibraryModel = D('piclibrary');
            $imgtxtInfo['thunmb_media_path'] = $this->piclibraryModel->getPicsByMediaIds(array(
                $imgtxtInfo['thunmb_media_id']
            ))[$imgtxtInfo['thunmb_media_id']]['pic_path'];
            //如果该图文信息有绑定表单
            if ($imgtxtInfo['form_id']){
                $imgtxtInfo['form_name']=$this->formModel->selectFormByFormid($imgtxtInfo['form_id'])['name'];
            }
            $this->assign('imgtxtInfo', $imgtxtInfo);
        }
        
        $this->assign('type', $type);
        $this->assign('companyType',$companyType);
        $this->assign('categoryList', $categoryList);
        $this->assign('piclibraryResult',$piclibraryResult);
        $this->display();
    }

    /**
     * 图文保存并生成二维码预览
     */
    public function preview(){
        $result = $this->saveImgtxt();
        if ($result) {
            $media_id = (I('post.type') == 'add') ? $result : I('post.id');
            $umid = D('publicno')->getPublicnoInfo(I('post.compid'))['um_id'];
            $url = "http://{$_SERVER['HTTP_HOST']}/WXClient/detail/mid/{$media_id}/type/".I('post.categoryId')."/umid/{$umid}";
            $path = './Uploads/qrcodes/'.date('Y-m-d');
            if (!is_dir($path)) $aa = mkdir($path, 0777, true);
            $qrcode = A('Qrcode')->getQRcode($url, $path, '', 8, 2, $media_id);
            retMessage(true, $qrcode);
        }else{
            retMessage(false, null, '生成二维码失败', '生成二维码失败', 4001);
        }
    }

    /**
     * 图文发布方法
     */
    public function publish(){
        $id = I('post.id');
        $result = $this->imgtxtModel->publish($id);
        ($result['ok'] ==1) ? retMessage(true,null) : retMessage(false, null, '发布失败', '', 4001);
    }

    /**
     * 保存图文信息
     */
    public function saveImgtxt(){
        // 接收数据
        $compid = I('post.compid', '');
        $id = I('post.id', '');
        $type = I('post.type', '');
        $picPath=I('post.picPath','');
        $thunmbPicPath=I('post.thunmbPicPath','');
        $title = I('post.title', '');
        $author = I('post.author', '');
        $categoryId = I('post.categoryId', '');
        $digest = I('post.digest', '');
        $content = I('post.content', '');
        $formId = I('post.formId', '');
        if (! $compid || ! $type || ! $picPath || !$thunmbPicPath || ! $title || ! $categoryId || ! $content) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
        if ($type == 'edit') {
            if (! $id) {
                retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
                exit();
            }
        }
        // 组装数据
        $data = array(
            'cm_id' => $compid,
            'title' => $title,
            'pic_path' => $picPath,
            'thunmb_pic_path' => $thunmbPicPath,
            'author' => $author,
            'digest' => $digest,
            'content' => $content,
            'form_id' => $formId,
            'category_id' => $categoryId,
            'is_publish' => intval(I('post.isPublish')),
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s')
        );
        // 保存图文信息
        return $this->imgtxtModel->doImgtxt($compid, $data, $type, $id);
    }

    /**
     * 处理图文信息
     */
    public function doImgtxt()
    {
        $result = $this->saveImgtxt();
        if (! $result) {
            retMessage(false, null . '保存图文信息失败', '保存图文信息失败', 4002);
            exit();
        }
        retMessage(true, null);
        exit();
    }

    /**
     * 删除图文信息
     */
    public function doDel()
    {
        // 接收数据
        $compid = I('post.compid', '');
        $_id = I('post._id', '');
        if (! $compid || ! $_id) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
        
        $result = $this->imgtxtModel->doImgtxt($compid, array(
            '_id' => $_id
        ), 'del');
        if (! $result) {
            retMessage(false, null, '删除图文信息失败', '删除图文信息失败', 4002);
            exit();
        }
        retMessage(true, null);
        exit();
    }

    /**
     * 修改分类名称
     */
    public function editCategoryName()
    {
        // 接收数据
        $compid = I('post.compid', '');
        $categoryId = I('post.categoryId', '');
        $categotyName = I('post.categotyName', '');
        if (! $compid || ! $categoryId || ! $categotyName) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
        
        $result = $this->categoryModel->edit_category_name($compid, $categoryId, $categotyName);
        if (! $result) {
            retMessage(false, null, '修改分类名称失败', '修改分类名称失败', 4002);
            exit();
        }
        retMessage(true, null);
        exit();
    }
    
    /**
     * 获取企业类型
     * @return unknown
     */
    public function getCompanyType(){
        //查询该企业是否为工作站
        $this->companyModel=D('company');
        $companyType=$this->companyModel->selectCompanyDetail($this->companyID)['cm_type'];
        return $companyType;
    }
}

