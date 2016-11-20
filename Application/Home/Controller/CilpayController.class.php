<?php
/*************************************************
 * 文件名：CilpayController.class.class.php
 * 功能：     客户缴费控制器
 * 日期：     2015.7.23
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
class CilpayController extends AccessController
{
    public function _initialize() {
        parent::_initialize();
    }
    public function index(){
        header("Content-type:text/html;charset=utf-8");
        $compId  = $_GET['compid'];
        $houseId = $_GET['hid'];
        $buildId  = $_GET['bid'];
        $propId  = $_GET['pid'];
        $chargeMod = D('charges');
        $allProperty = $chargeMod->selectALLProp($compId);           //该企业 下所有楼盘 
        
        if($houseId){
            $buildMod = D('building');
            if($buildId == 'all'){
               $rooms = $chargeMod->selectAllRoom($propId);               //所有房间
            }
               
            
           
        }else{
            $this->assign('property',$allProperty);
            $this->assign('compid',$compId);
            $this->display();
        }
    }
    public function get_build_house(){                                           //ajax根据楼盘ID获取楼宇房间数据
        $split = $_REQUEST['split'];
        switch($split){
            case 1:
                $buildMod = D('building');
                $pid      = $_REQUEST['pid'];
                $result   = $buildMod->selectAllBuild($pid,0,0);                         //查询楼宇
                break;
            case 2:
                $houseMod = D('charges');
                $bid      = $_REQUEST['bid'];
                $result   = $houseMod->selectHouseForBuild($bid);                         //查询房间
                break;
        }
        exit(json_encode($result));
    
    
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}

