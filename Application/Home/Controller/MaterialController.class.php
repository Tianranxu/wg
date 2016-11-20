<?php
/*************************************************
 * 文件名：MaterialController.class.php
 * 功能：     素材管理控制器
 * 日期：     2015.9.1
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;

class MaterialController extends AccessController{
    protected $_materialModel;
    protected $_weixinModel;
    protected $_categoryModel;
    
    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
        $this->_materialModel=D("material");
    }
    
    /**
     * 获取access_token
     */
    public function get_access_token($compid){
        $this->_weixinModel=D("weixin");
        $access_token = $this->_weixinModel->get_authorizer_access_token($compid);
        if ($access_token == -1) {
            $this->error("授权不可用或已过期，请重新授权",U('publicno/access',array('compid'=>$compid)));
        }
        return $access_token;
        
        //测试用
        /* $baseModel=D('base');
        $redis=$baseModel->connectRedis();
        $cache=$redis->get('access_token:chuyun');
        if ($cache){
            $access_token=$cache;
        }else {
            $access_token=json_decode(file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx7465729deab13e78&secret=3a265b455fb85b5cbd8804968a9fd361'))->access_token;
            $redis->setex('access_token:chuyun',7150,$access_token);
        }
        session(array('expire'=>7200));
        session('access_token:chuyun',$access_token);
        $baseModel->disConnectRedis();
        return $access_token; */
    }
    
    /**
     * 返回ue编辑器信息
     */
    public function get_upload_url(){
        //接收数据
        $compid=I('get.compid','');
        $action=I('get.action','');
        $callback=I('get.callback','');
        
        //实例化Ueditor
        vendor('Ueditor.Ueditor');
        $ueditor=new \Ueditor();
        //获取配置信息
        $result=$ueditor->index($action);
        $resultTemp=json_decode($result);
        if ($resultTemp->type=='.jpg' || $resultTemp->type=='.png' || $resultTemp->type=='.jpeg'){
            //获取上传图片的路径
            $filepath=$resultTemp->url;
            //获取access_token
            $access_token=$this->get_access_token($compid);
            //将图片上传到微信服务器
            $url=$this->_materialModel->uploadimg($access_token,$filepath);
            //将获取的微信url写入
            $resultTemp->url=$url.str_replace('.', '?wx_fmt=', $resultTemp->type);
        }
        $result=json_encode($resultTemp);
        echo $result;
    }
    
    /**
     * 获取该企业下图文信息分类列表
     * @param string $compid     企业ID
     * @return unknown
     */
    public function get_all_imgtxt_cate_list($compid,$type=''){
        $this->_categoryModel=D("category");
        $categoryList=$this->_categoryModel->getNewsist($compid,$type);
        return $categoryList;
    }
    
    /**
     * 修改分类名称
     */
    public function edit_category_name(){
        //接收数据
        $getData=array(
            'compid'=>I('post.compid',''),
            'category_id'=>I('post.category_id',''),
            'categoryName'=>I('post.categoryName',''),
        );
        // 判断是否有企业ID
        if (!$getData['compid'] || !$getData['category_id'] || !$getData['categoryName']) {
            $this->error('对不起，你无此权限进入！！', U('User/login'));
            exit();
        }
        
        //修改名称
        $this->_categoryModel=D("category");
        $result=$this->_categoryModel->edit_category_name($getData['compid'],$getData['category_id'],$getData['categoryName']);
        if (!$result){
            retMessage(false,null,'修改名称失败','修改名称失败',4002);
            exit;
        }
        retMessage(true,null);
        exit;
    }
    
    /**
     * 图文信息首页
     */
    public function image_text(){
        //接收数据
        $getData=array(
            'page'=>I('get.page',1),
            'compid'=>I('get.compid',''),
            'category'=>I('get.category',''),
            'keywords'=>I('get.keywords',''),
        );
        // 判断是否有企业ID
        if (!$getData['compid']) {
            $this->error('对不起，你无此权限进入！！', U('User/login'));
            exit();
        }
        //获取access_token
        $access_token=$this->get_access_token($getData['compid']);
        
        //获取图文信息列表
        $newList=$this->_materialModel->batchget_material($getData['compid'],$access_token,'news',($getData['page']-1)*10,10,$getData['category'],$getData['keywords']);
        foreach ($newList['list'] as $nk=>$nv){
            //TODO 搜索
            //搜索分类
            if ($getData['category']){
                if ($nv['category_id']!=$getData['category']){
                    //去除分类不相同的图文信息
                    unset($newList['list'][$nk]);
                    continue;
                }
            }
            
            foreach ($nv['content']['news_item'] as $nkk=>$nvv){
                //获取图文信息中第一条信息的缩略图
                if ($nkk==0){
                    $media_id=$nvv['thumb_media_id'];
                    $newsImgUrl=$this->_materialModel->get_one_material($access_token,$media_id,$getData['compid'],'images');
                    $newList['list'][$nk]['content']['news_item'][$nkk]['thumb_url']=$newsImgUrl;
                    
                    //搜索标题/作者/摘要
                    if ($getData['keywords']){
                        $titleKeyword=strpos($nvv['title'], $getData['keywords']);
                        $authorKeyword=strpos($nvv['author'], $getData['keywords']);
                        $digestKeyword=strpos($nvv['digest'], $getData['keywords']);
                        
                        //搜索不到任何数据
                        if (!is_numeric($titleKeyword) && !is_numeric($authorKeyword) && !is_numeric($digestKeyword)){
                            unset($newList['list'][$nk]);
                            continue;
                        }
                    }
                }
            }
        }
        //获取该企业下图文信息分类列表
        $categoryList=$this->get_all_imgtxt_cate_list($getData['compid']);
        //有搜索条件时
        if ($getData['category'] || $getData['keywords']){
            //统计图文的总数和总页数
            $newList['total']=ceil(count($newList['list'])/10);
            $newList['news_total']=count($newList['list']);
            
            //将搜索后的数据分页
            if ($getData['page']){
                $newList['list']=array_slice($newList['list'],($getData['page']-1)*10,10);
            }
        }
        
        $this->assign('getData',$getData);
        $this->assign('newList',$newList);
        $this->assign('categoryList',$categoryList);
        $this->display();
    }
    
    /**
     * 加载图片库
     */
    public function load_picture_library(){
        //接收数据
        $getData=array(
            'compid'=>I('post.compid',''),
            'page'=>I('post.page',1)
        );
        if (!$getData['compid']){
            retMessage(false,null,'未接收到数据','未接收到数据',4001);
            exit;
        }
        //获取access_token
        $access_token=$this->get_access_token($getData['compid']);
        
        //获取图片库
        $pictureList=$this->_materialModel->batchget_material($getData['compid'],$access_token,'image',($getData['page']-1)*10);
        if (!$pictureList['list']){
            retMessage(false,null,'查找不到记录','查找不到记录',4002);
            exit;
        }
        retMessage(true,$pictureList);
        exit;
    }
    
    /**
     * 新增图文信息
     */
    public function add_image_text(){
        //接收数据
        $getData=array(
            'compid'=>I('get.compid',''),
        );
        if (!$getData['compid']) {
            $this->error('对不起，你无此权限进入！！', U('User/login'));
            exit();
        }
        //获取access_token
        $access_token=$this->get_access_token($getData['compid']);
        
        //获取图片库
        $pictureList=$this->_materialModel->batchget_material($getData['compid'],$access_token,'image');
        //获取该企业下图文信息分类列表
        $categoryList=$this->get_all_imgtxt_cate_list($getData['compid']);
        
        $this->assign('getData',$getData);
        $this->assign('pictureList',$pictureList);
        $this->assign('categoryList',$categoryList);
        $this->display();
    }
    
    /**
     * 编辑图文信息
     */
    public function edit_image_text(){
        //接收数据
        $getData=array(
            'compid'=>I('get.compid',''),
            'media_id'=>I('get.id','')
        );
        if (!$getData['compid'] || !$getData['media_id']) {
            $this->error('对不起，你无此权限进入！！', U('User/login'));
            exit();
        }
        //获取access_token
        $access_token=$this->get_access_token($getData['compid']);
        
        //获取该图文信息
        $materialInfo=$this->_materialModel->get_one_material($access_token,$getData['media_id'],$getData['compid'],'news')['news_item'][0];
        $thumbMediaId=$materialInfo['thumb_media_id'];
        //获取该图文信息的缩略图
        $newsImgUrl=$this->_materialModel->get_one_material($access_token,$thumbMediaId,$getData['compid'],'images');
        $materialInfo['thumb_url']=$newsImgUrl;
        //查询该图文所属分类
        $categoryId=$this->_materialModel->table('fx_imgtxt_manage')->where(array('media_id'=>$getData['media_id']))->getField('category_id');
        $materialInfo['category_id']=$categoryId;
        //替换img标签中的data-src属性为src
        $materialInfo['content'] = str_replace("data-src", "src", $materialInfo['content']);
        //获取图片库
        $pictureList=$this->_materialModel->batchget_material($getData['compid'],$access_token,'image');
        //获取该企业下图文信息分类列表
        $categoryList=$this->get_all_imgtxt_cate_list($getData['compid']);
        //去除正文内容中因为换行符所造成的js报错
        $materialInfo['content'] = str_replace("\n", "", $materialInfo['content']);
        $materialInfo['content'] = str_replace("\r", "", $materialInfo['content']);
        
        $this->assign('materialInfo',$materialInfo);
        $this->assign('getData',$getData);
        $this->assign('pictureList',$pictureList);
        $this->assign('categoryList',$categoryList);
        $this->display();
    }
    
    /**
     * 图片库
     */
    public function picture_library(){
        //接收数据
        $getData=array(
            'page'=>I('get.page',1),
            'compid'=>I('get.compid',''),
        );
        if (!$getData['compid']) {
            $this->error('对不起，你无此权限进入！！', U('User/login'));
            exit();
        }
        //获取access_token
        $access_token=$this->get_access_token($getData['compid']);
        
        //获取图片库
        $pictureList=$this->_materialModel->batchget_material($getData['compid'],$access_token,'image',($getData['page']-1)*10);
        //重组数组
        $flag=array();
        foreach ($pictureList['list'] as $k=>$v){
            //将列表数据按更新时间降序排列
            $flag[$k][]=$v['update_time'];
        }
        array_multisort($flag,SORT_DESC,$pictureList);
        
        $this->assign('getData',$getData);
        $this->assign('pictureList',$pictureList);
        $this->display();
    }
    
    /**
     * 添加素材
     */
    public function add_material(){
        //接收数据
        $getData=array(
            'compid'=>I('post.compid',''),
            'thumb_media_id'=>I('post.media_id',''),
            'title'=>I('post.title',''),
            'author'=>I('post.author',''),
            'category_id'=>I('post.category_id',''),
            'digest'=>I('post.digest',''),
            'content'=>I('post.contentHtml',''),
            'content_source_url'=>I('post.url',''),
            'filepath'=>I('post.filepath',''),
            'type'=>I('post.type',''),
        );
        if (!$getData['compid'] || !$getData['type']){
            retMessage(false,null,'未接收到数据','未接收到数据',4001);
            exit;
        }
        //获取access_token
        $access_token=$this->get_access_token($getData['compid']);
        
        if ($getData['type']=='news'){
            //新建图文信息
            if (!$getData['thumb_media_id'] || !$getData['title'] || !$getData['category_id'] || !$getData['content']){
                retMessage(false,null,'未接收到数据','未接收到数据',4001);
                exit;
            }
            $getData['content']=htmlspecialchars_decode($getData['content']);
            $result=$this->_materialModel->add_news($getData['compid'],$access_token,$getData['category_id'],$getData['title'],$getData['thumb_media_id'],$getData['author'],$getData['digest'],$getData['content'],$getData['content_source_url']);
            if (!$result){
                retMessage(false,null,'添加失败','添加失败',4002);
                exit;
            }
            retMessage(true,null);
            exit;
        }elseif ($getData['type']=='image'){
            //新建图片素材
            if (!$getData['filepath']){
                retMessage(false,null,'未接收到数据','未接收到数据',4001);
                exit;
            }
            $result=$this->_materialModel->add_material($getData['compid'],$access_token,$getData['type'],$getData['filepath']);
            if (!$result){
                retMessage(false,null,'上传失败','上传失败',4002);
                exit;
            }
            retMessage(true,$result);
            exit;
        }
    }
    
    /**
     * 修改图文素材
     */
    public function edit_material(){
        //接收数据
        $getData=array(
            'compid'=>I('post.compid',''),
            'media_id'=>I('post.media_id',''),
            'thumb_media_id'=>I('post.thumb_media_id',''),
            'title'=>I('post.title',''),
            'author'=>I('post.author',''),
            'category_id'=>I('post.category_id',''),
            'digest'=>I('post.digest',''),
            'content'=>I('post.contentHtml',''),
            'content_source_url'=>I('post.url',''),
            'filepath'=>I('post.filepath',''),
            'type'=>I('post.type',''),
        );
        if (!$getData['compid'] || !$getData['type'] || !$getData['category_id']){
            retMessage(false,null,'未接收到数据','未接收到数据',4001);
            exit;
        }
        //获取access_token
        $access_token=$this->get_access_token($getData['compid']);
        
        $getData['content']=htmlspecialchars_decode($getData['content']);
        $result=$this->_materialModel->update_news($getData['compid'],$access_token,$getData['category_id'],$getData['media_id'],$getData['title'],$getData['thumb_media_id'],$getData['author'],$getData['digest'],$getData['content'],$getData['content_source_url']);
        if (!$result){
            retMessage(false,null,'保存失败','保存失败',4002);
            exit;
        }
        retMessage(true,null);
        exit;
    }
    
    /**
     * 删除素材
     */
    public function del_material(){
        //接收数据
        $getData=array(
            'compid'=>I('post.compid',''),
            'media_id'=>I('post.media_id',''),
            'type'=>I('post.type','')
        );
        if ((!$getData['compid']) || (!$getData['media_id']) || !$getData['type']){
            retMessage(false,null,'未接收到数据','未接收到数据',4001);
            exit;
        }
        //获取access_token
        $access_token=$this->get_access_token($getData['compid']);
        
        $result=$this->_materialModel->del_material($getData['compid'],$access_token,$getData['media_id'],$getData['type']);
        if (!$result){
            retMessage(false,null,'删除素材失败','删除素材失败',4002);
            exit;
        }
        retMessage(true,null);
    }
}


