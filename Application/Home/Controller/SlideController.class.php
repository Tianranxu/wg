<?php
/**
 * 文件名：SlideController.class.php
 * 功能：幻灯片管理控制器
 * 作者：XU
 * 日期：2015-09-02
 * 版权：CopyRight @2015 风馨科技 All Rights Reserved
 */

namespace Home\Controller;
use Home\Controller\AccessController;

class SlideController extends AccessController{
    /*
     * 初始化函数
     */
    public function _initialize(){
        header("Content-Type:text/html;charset=utf-8");
        parent::_initialize();
        $this->_materialModel = D('Material');
        $this->_weixinModel = D('Weixin');
        $this->slideModel = M('slide');
    }
    
    /*
     * 幻灯片界面函数
     */
    public function index(){
        //通过接口获取图片库中的图片
        $compid = I('request.compid','');
        $page = I('get.page',1);
        $pictureModel = D('Piclibrary');
        $where = array('cm_id' => $compid);
        $pictureSaved = $this->slideModel->field(array('url','name'))->order(array('order'=>'asc'))->where($where)->select();
        $pictureData = $pictureModel->getPiclibraryList($compid);
        $pictureData = array_slice($pictureData,($page-1)*20,20);
        if ($pictureSaved) {
            $this->assign('flag',1);
        }else{
            $this->assign('flag',-1);
        }
        $this->assign('pictureSaved',$pictureSaved);
        $this->assign('page',$page);
        $this->assign('compid',$compid);
        $this->assign('picture',$pictureData);
        $this->display();
    }
    
    /*
     * 添加幻灯片函数
     */
    public function addSlide(){
        
        $this->display();
    }
    
    /*
     * 编辑幻灯片函数
     */
    public function editSlide(){
        $url1 = I('post.url1','');
        $url2 = I('post.url2','');
        $url3 = I('post.url3','');
        $name1 = I('post.name1','');
        $name2 = I('post.name2','');
        $name3 = I('post.name3','');
        $compid = I('post.compid','');
        $check = 0;
        $data = array(
            array(
                'cm_id' => $compid,
                'url' => $url1,
                'order' => 1,
                'name' => $name1
            ),
            array(
                'cm_id' => $compid,
                'url' => $url2,
                'order' => 2,
                'name' => $name2
            ),
            array(
                'cm_id' => $compid,
                'url' => $url3,
                'order' => 3,
                'name' => $name3
            )
         );
        $where = array('cm_id' => $compid);
        $flag = $this->slideModel->field(array('id','cm_id'))->where($where)->select();
        if ($flag) {
            foreach ($flag as $k => $v) {
                $data[$k]['id'] = $v['id'];
                //若没有任何记录被修改，则返回错误
                if (!$this->slideModel->save($data[$k])) {
                    if (!$check == 2) {
                        $check = -1;    
                    }
                }else{
                    $check = 2;
                }
            }
        }else{
            foreach ($data as $k => $v) {
                if (!$this->slideModel->add($v)) {
                    $check = -1;
                    break;
                }
            }
        }
        if ($check == -1) {
            retMessage(false,null,"","",4001);
        }else{
            retMessage(true,1);    
        }
        $this->display();
    }
}