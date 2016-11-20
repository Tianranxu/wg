<?php
/*************************************************
 * 文件名：InformationController.class.php
* 功能：     资讯排版控制器
* 日期：     2015.7.23
* 作者：     fei
* 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
***********************************************/
namespace Home\Controller;
use Think\Controller;
class InformationController extends AccessController
{
    public function index(){
        $compid = I('get.compid');
        $categoryMod = D('Information');
        $compMod = D('company');
        $cotegory = $categoryMod->selectCategory($compid);
        $usableCotegory = $categoryMod->selectUsableCategory($compid);
        //查询公司是什么类型
        $cm_type = $compMod->selectCompanyDetail($compid)['cm_type'];
        $this->assign('cotegory', $cotegory);
        $this->assign('usableCotegory', $usableCotegory);
        $this->assign('compid', $compid);
        $this->assign('cm_type', $cm_type);
        $this->display();   
    }
    public function organize(){
        $compid = I('post.compid');
        $order = I('post.order');
        $nots = I('post.not');
        $iconid = $_POST['icon'];
        $iconArr= json_decode($iconid,true);
        $categoryMod = D('Information');
        //更新图标
        $categoryMod->updateIcon($iconArr);
        $Mod = M('model');
        $nots = rtrim($nots, ',');
        $order = rtrim($order, ',');
        $orderArr = explode(',', $order);
        $Mod->startTrans();
        $update_order = $categoryMod->sequence($orderArr, $order);//写入排序号
        $update_status = $categoryMod->condition($nots, $order);//更新状态位
        if($update_order && $update_status){
            $Mod->commit();
            exit('success');
        }else{
            $Mod->rollback();
            exit('fail');
        }
    }
}
    