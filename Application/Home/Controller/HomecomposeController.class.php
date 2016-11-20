<?php
/*************************************************
 * 文件名：HomecomposeController.class.php
 * 功能：     首页排版控制器
 * 日期：     2015.7.23
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
use Think\Controller;
class HomecomposeController extends AccessController
{
    public function index(){
        header("Content-Type:text/html;charset=utf-8");
        $compid = I('get.compid');
        $imageMod = D('homecompose');
        $compMod = D('company');
        //企业 是否邦定 工作站
        $biotinge = $compMod->selectCompanyAll($compid)['associate'];
        $workName = $compMod->selectCompanyAll($biotinge)['name'];
        
        //企业所佣有的菜单图标
        $own = $imageMod->selectIsMenus($compid);
        $own_menus = array();
        //系统所有菜单图标
        $all_menus = array();
        $all = $imageMod->selectMenus($compid);
        //所有菜单类型分开
        foreach($all as $allIcon){            
            switch($allIcon['type']){
                case 1:
                    $allIcon['gName'] = 'property';
                    $all_menus['sys'][] = $allIcon;
                    break;
                case 2:
                    //绑定工作站才显示图标
                    if($biotinge){
                        $allIcon['gName'] = 'work';
                        $all_menus['work'][] = $allIcon;
                    }
                    break;
                case 3:
                    $allIcon['gName'] = 'serve';
                    $all_menus['serve'][] = $allIcon;
                    break;
                default:
                    break;          
            }
        }
        //企业菜单类型分开
        foreach($own as $ownIcon){
            if($ownIcon['nomen']!=null){
                $ownIcon['title'] = $ownIcon['nomen'];
            }
            switch($ownIcon['type']){
                case 1:
                    //保存菜单ID
                    $sys_id[] = $ownIcon['id'];
                    $ownIcon['gName'] = 'property';
                    break;
                case 2:
                    //保存菜单ID
                    $work_id[] = $ownIcon['id'];
                    $ownIcon['gName'] = 'work';
                    break;
                case 3:
                    //保存菜单ID
                    $serve_id[] = $ownIcon['id'];
                    $ownIcon['gName'] = 'serve';
                    break;
                default:
                    break;
            }
            $own_menus[] = $ownIcon;
        }
        //从所有菜单中去除已定义的菜单
        $all_menus['sys'] = $this->__delMenus($all_menus['sys'],$sys_id);
        $all_menus['serve'] = $this->__delMenus($all_menus['serve'],$serve_id);
        if(isset($biotinge)){
            $all_menus['work'] = $this->__delMenus($all_menus['work'],$work_id);
        }

        $this->assign('a_menus', $all_menus);
        $this->assign('o_menus', $own_menus);
        $this->assign('compid', $compid);
        $this->assign('biotinge', $biotinge);
        $this->assign('workName', $workName);
        $this->display();
    }
    public function compose(){
        $ord_arr = $_POST['order'];//I('post.order'); 
        if($ord_arr=='[]'){
            exit('empty');
        }
        $compid = I('post.compid');
        $ord_arr = json_decode($ord_arr,true);
        $menusMod = D('companymenus'); 
        $result = $menusMod->availableMenusOrd($compid, $ord_arr);//更新企业下所有菜单
        if($result){
            exit('success');
        }else{
            exit('fail');
        }
        
    }
    //查询工作站
    public function search(){
        $word = I('post.key');
        $compMod = D('company');
        $result = $compMod->selectTypeComp($word,3);
        exit(json_encode ($result));
    }
    //开始绑定
    public function binding(){
        $work = I('post.work');
        $comp = I('post.comp');
        $compMod = D('company');
        $result = $compMod->where("id=%d",$comp)->setField('associate',$work);
        $response = $result?'success':'fail';
        exit($response);
    }
    //解除绑定
    public function unBinding(){
        $comp = I('post.comp');
        $compMod = D('company');
        $compMenusMod = D('Companymenus');
        $compMenusMod->delWorkMenus($comp, 2);
        $result = $compMod->where("id=%d",$comp)->setField('associate',null);
        $response = $result?'success':'fail';
        exit($response);
    }
    //重置菜单
    public function reset(){
        $compid = I('post.compid');
        $compMenusMod = D('Companymenus');
        $result = $compMenusMod->delWorkMenus($compid);
        if($result){
            exit('success');
        }else{
            exit('fail');
        }
    }
   /* //修改菜单名
    public function rename(){
        $cid = I('post.cid');
        $mid = I('post.mid');
        $name = I('post.name');
        $companyMenusMod = D('companymenus');
        $result = $companyMenusMod->where("cm_id=%d AND menu_id=%d",$cid,$mid)->setField('nomen',$name);
        $response = $result?'success':'fail';
        exit($response);
    }*/
    //数组排序方法
    private function _usort($arr){
        usort($arr, function($a, $b){
            $sort_a = $a['ord_id'];
            $sort_b = $b['ord_id'];
            if($sort_a>$sort_b){
                return 1;
            }else{
                return -1;
            }
        
        });
        return $arr;
    }
    //去已定义的菜单
    protected function __delMenus($arr1, $id){

        foreach($arr1 as $key => $val){
           if(in_array($val['id'],$id)){
              unset($arr1[$key]);
           }
        }
        return $arr1;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}