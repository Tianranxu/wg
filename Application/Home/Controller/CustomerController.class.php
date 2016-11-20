<?php
/*************************************************
 * 文件名：CustomerController.class.class.php
 * 功能：     客户管理控制器
 * 日期：     2015.7.23
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
use Think\Model;
use Org\Util\RabbitMQ;
define('IMPORT_QUEUE', 'customer_import_queue');
class CustomerController extends AccessController{
    public function _initialize() {
        parent::_initialize();
    }
    
    
    public function index(){
        $compid = I('get.compid');
        $name = I('get.name');
        $status = I('get.status');
        $search = I('get.search');
        $customMod = D('customer');
        if($search!='search'){
            $search = 0;
            $get_data['name']  = '';
            $get_data['status']= '';
            $cInfo = $customMod->selectAllClient($compid,0,10);
        }else{
            $get_data['name']  = $name?$name:'';
            $get_data['status']= $status?$status:'';
            $cInfo = $customMod->searchCust($compid,$name,$status,0,10);              //按条件搜索楼宇
        }
        $count     = count($cInfo);
        $this->assign('cinfo',$cInfo);
        $this->assign('compid',$compid);
        $this->assign('count',$count);
        $this->assign('search',$search);
        $this->assign('get_data',$get_data);
        $this->display();
    }
    public function flow(){                                          //加载更多AJAX
        $compid    = I('post.compid');
        $count     = I('post.count');
        $name      = I('post.name');
        $status    = I('post.status');
        $customMod = D('customer');
        
        if(I('post.search')!='search'){
            $custInfo  = $customMod->selectAllClient($compid,$count,10);
        }else{
            $custInfo = $customMod->searchCust($compid,$name,$status,$count,10);
        }
        if($custInfo){
            $result['flag'] = 'success';
            $result['html'] = $custInfo;
            exit (json_encode ($result));
        }elseif($custInfo==NULL){
            $result['flag'] = 'empty';
            $result['html'] = $custInfo;
            exit (json_encode ($result));
        }else{
            $result['flag'] = 'fail';
            $result['html'] = $custInfo;
            exit (json_encode ($result));
        }
    
    }
    

    public function addcustomer(){                //新建客户
        
        $post['name']           = I('get.name');
        $post['cm_id']          = I('get.cid');
        $post['contact_number'] = I('get.phone');
        $post['pu_type']        = I('get.type');
        $post['status']         = I('get.status');
        $post['remark']         = I('get.remark');
        $post['create_time']    = date('Y-m-d H:i:s');
        $post['modify_time']    = $post['create_time'];
        $cuid = I('get.cuid');
        $customerMod            = D('customer');
        if($cuid ==''){
            
            $number = $customerMod->selectLastNumber($post['cm_id'])['number'];
            if(!$number){
                $number = KH00001;
            }else{
                $number     = substr($number,strpos($number,"H")+1);
                $number     = $number+1;
                $number     = 'KH'.str_pad($number,5,"0",STR_PAD_LEFT);
            }
            $post['number'] = $number;
            $result = $customerMod->add($post);
        }else{

            $post['id']          = $cuid;
            $post['modify_time'] = date('Y-m-d H:i:s');
            $result              = $customerMod->save($post);
        }
        if($result){
            echo 'success';
        }else{
            echo 'fail';
        }

    }
    public function edit(){               //编辑客户
        
        $id        = I('get.id');
        $customMod = D('customer');
        $data      = $customMod->selectClient($id);
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
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
