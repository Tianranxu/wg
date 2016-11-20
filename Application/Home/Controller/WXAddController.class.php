<?php
/*
 * 文件名：WXAddController.class.php
 * 功能：微信新建报障控制器
 * 日期：2015-11-16
 * 作者：fei
 * 版权：Copyright @ 2015 风馨科技 All Rights Reserved
 */

namespace Home\Controller;

class WXAddController extends WXClientController {
    public function _initialize(){
        header("Content-Type:text/html;charset=utf-8");
        parent::_initialize();
    }
    
    //新建故障单
    public function newRepair(){
        $this->checkInfo();
        //获取当前用户信息
        $wxuserMod = D('WXuser');
        
        $propertyMod = D('Wxbind');
        $propMod = D('Property');
        $WXuser = $wxuserMod->WXuserInfo($this->openid);
        //获取当前用户绑定的房产
        $result = $propertyMod->getHouseBindList($this->openid);
        //楼盘所属地
        $prop = $propMod->get_property_list($WXuser['cm_id'], 0, '', '', 1);
        $proLlist = json_decode($prop, true)[1]['list'];
        foreach($proLlist as $pro){
            //楼盘所属地
            $addr_id[$pro['id']] = $pro['address_id'];
        }
        //组成数组
        foreach($result as $re){
            $property[] = array(
              'id' => $re['houseInfo']['id'] ,
              'info' => $re['houseInfo']['community_name'].$re['houseInfo']['build_name'].$re['houseInfo']['number'],
              'hm_number' => $re['houseInfo']['hm_number'],
              'bu_id' => $re['houseInfo']['bm_id'],
              'cc_id' => $re['houseInfo']['cc_id'],
              'cm_id' => $re['houseInfo']['cm_id'],
              'cm_name' => $re['houseInfo']['id'],
              'addr_id' => $addr_id[$re['houseInfo']['cc_id']]
            );
        }
        $this->assign('user', $WXuser);
        //选用的模板
        $this->assign('templet', $this->templet);
        $this->assign('compid', $this->compid);
        $this->assign('openid', $this->openid);
        $this->assign('property', $property);
        $this->assign('proLlist', $proLlist);
        $this->assign('umid',$this->umid);
        $this->display();
    }
    //获取楼盘信息
    public function property(){   
        $compid = I('post.cm_id');
        $propMod = D('Property');
        $prop = $propMod->get_property_list($compid, 0, '', '', 1);
        $proLlist = json_decode($prop)[1]->list;
        $proLlist = $proLlist!=null?$proLlist:array('fail');
        exit(json_encode($proLlist));
    }
    //获取楼栋信息
    public function build(){
        $cc_id = I('post.p_id');
        //所有正常的楼栋
        $buildMod = D('building');
        $builds = $buildMod->selectAllBuild($cc_id, 0, 0, 1);
        $builds = $builds!=null?$builds:array('fail');
        exit(json_encode($builds));
    }
    //获取设备
    public function device(){
        $cc_id = I('post.p_id');
        //所有正常的故障设备
        $compDeviMod = D('Compdevice');
        //查询楼盘绑定维修公司列表
        $CompBindRepairs = $compDeviMod->getBindList($cc_id, 1);
        $CompBindRepairs = $CompBindRepairs!=null?$CompBindRepairs:array('fail');
        exit(json_encode($CompBindRepairs));
    }
    //获取故障现象
    public function phenomenon(){
        $dev_id = I('post.dev_id');
        $phenomMod = D('phenomenon');
        $phenoms = $phenomMod->getPhenomenonList($dev_id);
        $phenoms = $phenoms!=null?$phenoms:array('fail');
        exit(json_encode($phenoms));
    }
    
}