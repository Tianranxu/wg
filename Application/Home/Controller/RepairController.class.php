<?php
/*************************************************
 * 文件名：RepairController.class.php
 * 功能：     维修首页控制器
 * 日期：     2015.10
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
use Think\Model;
class RepairController extends AccessController{



    protected $_userModel;
    protected $compid;

    protected $_companyModel;

    /**
     * 初始化
     */
    public function _initialize()
    {   header("Content-Type:text/html;charset=utf-8");
        parent::_initialize();
        
        $this->_userModel = D('user');
        $this->_companyModel = D('company');
        $this->compid = I('get.compid', '');
    }
    public function index(){
        // 判断是否有企业ID
        if (! $this->compid) {
            $this->error('对不起，你无此权限进入！！', U('User/login'));
            exit();
        }        
        $repairMod = D('Wxrepair');
        $repairGroupMod = D('Repairgroup');
        $cityMod = D('City');
        //企业 下所有分组信息
        $groups = $repairGroupMod->get_repair_group($this->compid);
        //初始化分组信息
        //默认分组
        $d_count = 0;
        $defaArr = array(
            'name' => '默认分组',
            'count' => $d_count,
            'user' => array(),
        );
        //待审核分组
        $e_count = 0;
        $examArr = array(
            'name' => '待审核人员',
            'count' => $e_count,
            'user' => array(),
        );
        //自定分组
        $s_count = 0;
        foreach($groups as $gr){
            $selfArr[$gr['id']] = array(
                'name' => $gr['title'],
                'count' => $s_count,
                'user' => array(),
            );
        }
        //查询企业下所有给维修员
        $repaInfo = $repairMod->get_repairer($this->compid);
        //查出区域跟设备
        foreach($repaInfo as $key=>$rep){
            $dev = $repairMod->get_repair_device($rep['id'],$rep['cm_id']);
            $area = $repairMod->get_repair_area($rep['id'], $rep['cm_id']);
            //开始查找省市
            /*
             * 这里假设一个维修员只能分配在一个城市里如果以后需求变，一个维修员可以在不同城市里工作。此方法就不行
             */
            $res = $cityMod->get_city_name($area[0]['pid']);
            $city = $res['name'];
            $province = $cityMod->get_city_name($res['pid'])['name'];
            $zone = array(
                'prov' => $province,
                'city' => $city,
                'area' => $area
            ); 
            $repaInfo[$key]['device'] = $dev;
            $repaInfo[$key]['zone'] = $zone;
        }
        //组合成数组
        foreach($repaInfo as $info){
            if($info['status']==1){
                $examArr['user'][] = $info;
                $examArr['count'] = ++$e_count;
            }elseif($info['gid']==null){
                $defaArr['user'][] = $info;
                $defaArr['count'] = ++$d_count;
            }else{
                $selfArr[$info['gid']]['user'][]= $info;
            }
        }
        //计算自定义成员总数
        foreach($selfArr as $key=>$arr){
            $count = count($arr['user']);
            $selfArr[$key]['count']= $count;
        }
        //查询全国省 ，审核通过时区域分配用
        $provinces = $this->prov();
        //没选择区域时，默认选项

        
        $this->assign('provin',$provinces);
        $this->assign('defaArr',$defaArr);
        $this->assign('examArr',$examArr);
        $this->assign('selfArr',$selfArr);
        $this->assign('compid',$this->compid);
        $this->display();
    }
    // TODO 新建分组和修改分组名
    public function createGroup(){
        $name = I('post.name');
        $gid = I('post.gid',null);
        $compid = I('post.compid');
        $repairGroupMod = D('Repairgroup');
        if(empty($gid)){
            $data = array(
                'title' => $name,
                'type' => 2,
                'cm_id' => $compid,
                'create_time' =>date('Y-m-d H:i:s',time())
            );
            $result = $repairGroupMod->add($data);
        }else{
            $result = $repairGroupMod->where('id=%d', $gid)
                                     ->setField('title',$name);
        }
        if($result){
            exit('success');
        }
        exit('fail'); 
    }
    // TODO 删除分组或成员
    public function delete(){
        $id = I('post.id');
        $sign = I('post.sign');
        $repairMod = D('Wxrepair');
        switch($sign){
            case 'member':
                //清除维修员表数据
                $result = $repairMod->where('id=%d',$id)
                                    ->setField(array(
                                        'cm_id' => null,
                                        'status' => C('REPAIR_STATUS.NOT_REGI'),
                                        'gid' => null,
                                        'exam_id' => null,
                                        'request' => null,
                                        'number' => null
                                    ));
                //清除设备和区域
                $devTempMod = D('Repairdev');
                $cityTemp = D('Repaircity');
                $devTempMod->where('rid=%d',$id)->delete();
                $cityTemp->where('rid=%d',$id)->delete();
                break;                
            case 'group':
                $repairGroupMod = D('Repairgroup');
                $result = $repairGroupMod->delete($id);
                //把此组的成员移动到默认组下
                $repairMod->move_member_default($id);
                break;
            default :
                exit('fail');    
        }
        if($result){
            exit('success');
        }
        exit('fail'); 
    }
    //移动成员
    public function move(){
        $rid = I('post.rid');
        $gid = I('post.gid',null);
        $repairMod = D('Wxrepair');
        $result = $repairMod->where('id=%d',$rid)
                            ->setField('gid',$gid);
        if($result){
            exit('success');
        }
        exit('fail');
    }
    //分配工作
    public function item(){
        $rid = I('post.rid');
        //查询所有设备项目
        $deviceMod = D('Device');
        $device = $deviceMod->getDeviceList(1);
        //查询维修员已佣有的设备项目
        $repairDevMod = D('Repairdev');
        $devi = $repairDevMod->repairDevi($rid);
        foreach($devi as $de){
            $devID[] = $de['id']; 
        }
        //合并
        $itme = array();
        foreach($device as $ky=>$dev){
            if(in_array($dev['id'],$devID)){
                $itme[$ky]['checked'] = 'checked';
            }else{
                $itme[$ky]['checked'] = '';
            }
            $itme[$ky]['id'] = $dev['id'];
            $itme[$ky]['name'] = $dev['name'];
        }
        if($device){
            exit(json_encode($itme));
        }
        exit(json_encode(array('status'=>'fail')));
    }
    public function do_item(){
        $rid = I('post.rid');
        $devid = rtrim(I('post.devid'),',');
        $cid = I('post.cid');
        $repairDevMod = D('Repairdev');
        $result = $repairDevMod->allotDevi($rid, $cid, $devid);
        if($result){
            exit('success');
        }
        exit('fail');
    }
    //分配区域
    public function area(){
        $rid = I('post.rid');
        $rcid = I('post.rcid');
        //合成省市区数组
        $region = array();
        //查询全国省
        $province = $this->prov();
        if(!empty($rid)){
            //查询维修员已在的区
            $repairCityMod = D('Repaircity');
            $local_area = $repairCityMod->city($rid, $rcid);
            foreach($local_area as $la){
                $areaId[] = $la['id'];
            }
            //市ID
            $cityId = empty($local_area)?2211:$local_area[0]['pid'];
            //省ID
            $local_city = $repairCityMod->selectRegion($cityId);
            $provId = empty($local_area)?2184:$local_city['pid'];
            //省默认值
            foreach($province as $p){
                if(empty($local_area)&& $p[id]==2184){
                    $p['check'] = 'selected';
                }elseif($p[id] == $provId){
                    $p['check'] = 'selected';
                }else{
                    $p['check'] = '';
                }
                $region['prov'][] = $p;
            };
            //市默认值
            $cityMod = D('City');
            $citys = $cityMod->find_city_list($provId);
            foreach($citys as $c){
                if(empty($local_area) && $c['id']==2211){
                    $c['check'] = 'selected';
                }elseif($c[id] == $cityId){
                    $c['check'] = 'selected';
                }else{
                    $c['check'] = '';
                }
                $region['city'][] = $c;
            };
            //区默认值
            $areas = $cityMod->find_area_list($cityId);
            foreach($areas as $a){
                if(in_array($a[id],$areaId)){
                    $a['check'] = 'checked';
                }else{
                    $a['check'] = '';
                }
                $region['area'][] = $a;
            };
        }

        exit(json_encode($region));
    }
    public function do_area(){
        $rid = I('post.rid');
        $status = I('post.status',null);
        $areaid = rtrim(I('post.areaid'),',');
        $cid = I('post.cid');
        $repairCotyMod = D('Repaircity');
        $result = $repairCotyMod->allotArea($rid, $cid, $areaid);
        //更改审核状态
        if(!empty($status)){
            $repairMod = D('Wxrepair');
            $repairMod->exmaRepairer($rid,$status,$this->userID);
        }
        if($result){
            exit('success');
        }
        exit('fail');
    }
    //查询全国省
    protected function prov(){
        $cityMod = D('City');
        $result = $cityMod->select_province_list();
        return $result;
    }
    //查询省下面市
    public function city(){
        $pid = I('post.pid');
        $cityMod = D('City');
        $result = $cityMod->find_city_list($pid);
        exit(json_encode($result));
    }
    //查询  市 下面所有区
    public function county(){
        $pid = I('post.pid');
        $rid = I('post.rid',null);
        $cid = I('post.cid');
        $cityMod = D('City');
        $result = $cityMod->find_area_list($pid);
        if($rid!=null){

            foreach($area as $de){
                $cityID[] = $de['city_id'];
            }
        }
        //合并
        foreach($result as $ky=>$ci){
            if(in_array($ci['id'],$cityID)){
                $allArea[$ky]['checked'] = 'checked';
            }else{
                $allArea[$ky]['checked'] = '';
            }
            $allArea[$ky]['id'] = $ci['id'];
            $allArea[$ky]['name'] = $ci['name'];
        } 
        exit(json_encode($allArea));
    }
    
    //关联物业
    public function relatedProperty(){
        $compid = I('get.compid','');
        $tempModel = D('Repairdev');
        $data = $tempModel->getPropertyByCompid($compid);
        $result = array();
        foreach ($data as $value) {
            $result[$value['cm_id']]['number'] = $value['c_number'];
            $result[$value['cm_id']]['name'] = $value['c_name'];
            $companyInfo = array_key_exists($value['cm_id'], $result) ? $result[$value['cm_id']] : array();
            $deviceInfo =  array_key_exists($value['cc_name'], $companyInfo) ? $companyInfo[$value['cc_name']] : array();
            $deviceInfo[$value['status']][] = $value['d_name'];
            $companyInfo[$value['cc_name']] =  $deviceInfo;
            $result[$value['cm_id']] = $companyInfo;
        }
        foreach ($result  as $key =>$company) {
            foreach ($company as $k => $community) {
                if($community[1] && $k != 'number' && $k != 'name'){
                    $result[$key]['isEmpty'] = -1;
                    break;
                }
                $result[$key]['isEmpty'] = 1;
            }
        }
        $this->assign('compid',$compid);
        $this->assign('result',$result);
        $this->display();
    }

    /**
     * 维修设置页面
     */
    public function timelimit()
    {
        //查询该维修公司的接单超时以及修复超时
        $compInfo = $this->_companyModel->selectCompanyAll($this->companyID);

        $this->assign('compInfo', $compInfo);
        $this->display();
    }

    /**
     * 维修时限设置
     */
    public function setCatchTimeout()
    {
        //接收数据
        $compid = I('post.compid', '');
        $type = I('post.type', '');
        $timelimit = I('post.timelimit', '');
        if (!$compid || !$type) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit;
        }

        $result = $this->_companyModel->repairTimelimit($compid, $type, $timelimit);
        if (!$result) {
            retMessage(false, null, '时限设置失败', '时限设置失败', 4002);
            exit;
        }
        retMessage(true, null);
        exit;
    }
}