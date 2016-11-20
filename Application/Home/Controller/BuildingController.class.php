<?php
/*************************************************
 * 文件名：BuildingController.class.php
 * 功能：     访问控制控制器
 * 日期：     2015.7.23
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;

class BuildingController extends AccessController{
    public function _initialize() {
        parent::_initialize();
    }
    
    public function index(){
        $companyid= I('get.compid');
        $proid    = I('get.proid');
        $number   = I('get.number');
        $name     = I('get.name');
        $status   = I('get.status');
        $buildMod = D('building');
        $proMod   = D('property');   
        $allPro   = $proMod->selectProName($proid); 
        if(I('get.search')!='search'){
            $search = 0;
            $get_data['number']= '';
            $get_data['name']  = '';
            $get_data['status']= '';
            $buildInfo = $buildMod->selectAllBuild($proid,0,10);  //查询本企业所有楼宇，只显示前10条.
        }else{
            $search = $_GET['search'];
            $get_data['number']= isset($number)?$number:'';
            $get_data['name']  = isset($name)?$name:'';
            $get_data['status']= isset($status)?$status:'';
            $buildInfo = $buildMod->searchBuild($proid,$number,$name,$status,0,10);              //按条件搜索楼宇
        }
        $proName  = $proMod->selectProName($proid);         //查询本企业名称
  
        $count    = count($buildInfo);
        $this->assign('compID',$companyid);
        $this->assign('proid',$proid);
        $this->assign('proname',$proName['name']);
        $this->assign('bInfo',$buildInfo);
        $this->assign('count',$count);
        $this->assign('allPro',$allPro);
        $this->assign('search',$search);
        $this->assign('get_data',$get_data);
        $this->display();
        
    }
    
    public function flow(){                                              //“加载更多”功能AJAX
       
        $count    = I('post.count');
        $proid    = I('post.proid');
        $proname  = I('post.proname');
        $compid   = I('post.compid');
        $number   = I('post.number');
        $name     = I('post.name');
        $status   = I('post.status');
        $buildMod = D('building');
        if(I('post.search')!='search'){
            $buildInfo = $buildMod->selectAllBuild($proid,$count,10);  //查询本企业所有楼宇，
        }else{
            $buildInfo = $buildMod->searchBuild($proid,$number,$name,$status,$count,10);              //按条件搜索楼宇
        }
        $html     = '';
        if($buildInfo){
            $result['flag'] = 'success';
            $result['html'] = $buildInfo;
            exit (json_encode ($result));
        }elseif($buildInfo==NULL){
            $result['flag'] = 'empty';
            $result['html'] = $buildInfo;
            exit (json_encode ($result));
        }else{
            $result['flag'] = 'fail';
            $result['html'] = $buildInfo;
            exit (json_encode ($result));
        }

    }
    
    public function addbuild(){                                                     //添加楼宇
        $data['name']   = I('post.name');
        $data['cc_id']  = I('post.proid');
        $data['remark'] = I('post.remark');
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['modify_time'] = $data['create_time'];
        $buildMod = D('building');
        if(I('post.bid')==''){
            $number = $buildMod->selectLastNumber($data['cc_id'])['number'];            //查询最后一条楼宇编号
            if(!$number){
                $number = LY0001;
            }else{
                $number     = substr($number,strpos($number,"Y")+1);
                $number     = $number+1;
                $number     = 'LY'.str_pad($number,4,"0",STR_PAD_LEFT);
            }
            $data['number'] = $number;
            $result = $buildMod->add($data);
            
        }else{
            $data['status'] = I('post.status');
            $data['id']          = I('post.bid');
            $data['modify_time'] = date('Y-m-d H:i:s');
            $result = $buildMod->save($data);
        }
        if($result){
            echo 'success';
        }else{
            echo 'fail';
        }
    }      
    
    public function editbuild(){                                      //编辑楼宇e_dis_use
        
        $id       = I('post.id');
        $pname    = I('post.pname');
        $pid      = I('post.pid');
        $buildMod = D('building');
        $data     = $buildMod->selectBuild($id);                         //查询某一个楼宇信息
        if($data){                     
            $result['flag'] = 'success';
            $result['html'] = $data;
            exit (json_encode ($result));
        }else{
            $result['flag'] = 'fail';
            $result['html'] = '';
            exit (json_encode ($result));
        }
        
        
    }
    
   /* public function search_b(){                                      //页面搜索功能
        $pid    = $_POST['property'];
        $number = $_POST['number'];
        $name   = $_POST['name'];
        $status = $_POST['status'];
        
        $buildMod = D('building');
        
        $result = $buildMod->searchBuild($pid,$number,$name,$status);              //按条件搜索楼宇
        if($result){
            echo 'success';
        }else{
            echo 'fail';
        }
    }*/
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
