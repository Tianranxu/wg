<?php
/*************************************************
 * 文件名：PiclibraryController.class.php
 * 功能：     图片库控制器
 * 日期：     2015.10.27
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;

use Home\Controller\AccessController;

class PiclibraryController extends AccessController
{

    protected $piclibraryModel;

    protected $categoryModel;

    protected $imgtxttempModel;

    /**
     * 初始化
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->piclibraryModel = D('piclibrary');
        $this->categoryModel = D('category');
        $this->imgtxttempModel = D('imgtxttemp');
    }
    
    public function getPiclibrary(){
        //接收数据
        $compid=I('request.compid',$this->companyID);
        $flag=I('post.flag',0);
        $category = I('request.category', 0);
        $page=I('request.page',1);
        
        // 查询已分组的图片
        $picTemp = $this->imgtxttempModel->getPicTempList($compid);
        $mediaIds = array();
        foreach ($picTemp as $temp) {
            $allMediaIds[] = $temp['media_id'];
            if ($category == $temp['category_id']) {
                $mediaIds[] = $temp['media_id'];
            }
        }
        
        // 筛选未分组图片
        $unPictureList = $this->piclibraryModel->getPiclibraryList($compid, 'nin', $allMediaIds);
        $unListCount = count($unPictureList);
        if (! $category) {
            $pictureList = $unPictureList;
        }
        // 筛选已分组图片
        if ($category) {
            $pictureList = $this->piclibraryModel->getPiclibraryList($compid, 'in', $mediaIds);
        }
        // 统计数据总数
        $listCount = count($pictureList);
        // 统计总页数
        $totalPages = ceil(count($pictureList) / 10);
        // 数据分页
        $pictureList = array_slice($pictureList, ($page - 1) * 10, 10);
        $pictureList=array_values($pictureList);
        
        // 查询图片库分组列表以及各自总数
        $groupList = $this->imgtxttempModel->countAllPiclibrary($compid);
        foreach ($groupList as $g => $group) {
            if (! $group['media_id']) {
                $groupList[$g]['total'] = 0;
            }
        }
        //组装数据
        $data=array(
            'category'=>$category,
            'pictureList'=>$pictureList,
            'groupList'=>$groupList,
            'listCount'=>$listCount,
            'unListCount'=>$unListCount,
            'totalPages'=>$totalPages
        );
        
        if ($flag){
            retMessage(true,$data);
            exit;
        }
        
        return $data;
    }

    /**
     * 图片库页面
     */
    public function index()
    {
        // 接收数据
        $page = I('get.page', 1);
        $category = I('get.category', 0);
        
        //获取图片库
        $result=$this->getPiclibrary();
        //获取企业类型
        $companyType=A('imgtxt')->getCompanyType();
        
        $this->assign('category', $result['category']);
        $this->assign('pictureList', $result['pictureList']);
        $this->assign('groupList', $result['groupList']);
        $this->assign('listCount', $result['listCount']);
        $this->assign('unListCount', $result['unListCount']);
        $this->assign('totalPages', $result['totalPages']);
        $this->assign('companyType',$companyType);
        $this->display();
    }

    /**
     * 新增图片
     */
    public function add()
    {
        // 接收数据
        $compid = I('post.compid', '');
        $picName = I('post.filename', '');
        $picPath = I('post.filepath', '');
        $thunmbPicPath = I('post.thunmbFilePath', '');
        if (! $compid || ! $picName || ! $picPath || ! $thunmbPicPath) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
        
        $result = $this->piclibraryModel->addPicture($compid, $picName, $picPath, $thunmbPicPath);
        if (! $result) {
            retMessage(false, null, '添加图片失败', '添加图片失败', 4002);
            exit();
        }
        retMessage(true, $result);
        exit();
    }

    /**
     * 新建/编辑分组
     */
    public function addGroup()
    {
        // 接收数据
        $compid = I('post.compid', '');
        $groupName = I('post.groupName', '');
        $type = I('post.type', '');
        $categoryId = I('post.categoryId', '');
        if (! $compid || ! $groupName || ! $type) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
        if ($type == 'edit' && ! $categoryId) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
        
        $result = $this->categoryModel->doCategory($compid, $groupName, 101, $type, $categoryId);
        if (! $result) {
            retMessage(false, null, '保存分组失败', '保存分组失败', 4002);
            exit();
        }
        retMessage(true, null);
        exit();
    }

    /**
     * 移动分组
     */
    public function moveGroup()
    {
        // 接收数据
        $compid = I('post.compid', '');
        $categoryId = I('post.categoryId', '');
        $mediaIds = I('post.picArr', '');
        if (! $compid || ! $mediaIds) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
        
        $result = $this->imgtxttempModel->moveGroup($categoryId, $mediaIds);
        if (! $result) {
            retMessage(false, null, '移动分组失败', '移动分组失败', 4002);
            exit();
        }
        retMessage(true, null);
        exit();
    }

    public function doDel()
    {
        // 接收数据
        $compid = I('post.compid', '');
        $doType = I('post.doType', '');
        $categoryId = I('post.categoryId', '');
        $picArr = I('post.picArr', '');
        if (! $compid || ! $doType) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
        
        $function = new \ReflectionMethod(__CLASS__, 'del' . $doType);
        if ($doType == 'picture' || $doType == 'onePicture')
            $result = $function->invoke($this, $picArr);
        if ($doType == 'group')
            $result = $function->invoke($this, $compid, $categoryId);
        
        if (! $result) {
            retMessage(false, null, '删除失败', '删除失败', 4002);
            exit();
        }
        retMessage(true, null);
        exit();
    }

    /**
     * 删除图片
     * 
     * @param array $picArr            
     * @return boolean
     */
    public function delpicture(array $picArr)
    {
        if (! $picArr)
            return false;
        $result = $this->piclibraryModel->delPicture($picArr);
        if (! $result)
            return false;
        return true;
    }

    /**
     * 删除分组
     * 
     * @param string $compid
     *            企业ID
     * @param string $categoryId
     *            分组ID
     * @return boolean
     */
    public function delgroup($compid, $categoryId)
    {
        if (! $categoryId)
            return false;
        $result = $this->imgtxttempModel->delGroup($compid, $categoryId);
        if (! $result)
            return false;
        return true;
    }
}


