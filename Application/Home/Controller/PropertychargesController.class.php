<?php
/*************************************************
 * 文件名：PropertychargesController.class.php
* 功能：     房产收费控制器
* 日期：     2015.7.23
* 作者：     fei
* 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
***********************************************/
namespace Home\Controller;
use Think\Model;
use Org\Util\RabbitMQ;
define('IMPORT_QUEUE', 'charges_set_queue');
class PropertychargesController extends AccessController{
    
    public function _initialize() {
        parent::_initialize();
        $userMod    = D('user');
    }
    public function index(){                                  //房产收费管理首页
        $compid = $_GET['compid'];    
        $chargesMod = D('charges');
        $buildMod   = D('building');
        $userMod    = D('user');
        $cycleStr   = '';
        
        $allRecord = $chargesMod->selectCharges($compid,0,10);           //r所有收费设置记录
        $recordArr = array();                                      //重新组成记录数组
        foreach($allRecord as $key=>$rec){
            $cycleStr = '';
            $item    = $chargesMod->selectChargesItem($rec['chm_id']);
            $userInfo= $userMod->find_user_info($rec['user_id']);
            $userName= $userInfo['name'];
            $usercode= $userInfo['code'];
            $prop    = $chargesMod->selectPro($rec['cc_id'])['name'];
            $house   = $chargesMod->selectHouse($rec['hm_id'])['number'];
            $build   = $buildMod->selectBuild($rec['bm_id'])['name'];
            $cycle = str_replace(',', '月,', $rec['charging_cycle']).'月';
            $recordArr[$key]['charges'] = $item['name'];
            $recordArr[$key]['cycle']   = $cycle;
            $recordArr[$key]['prop']    = $prop;
            $recordArr[$key]['build']   = $build;
            $recordArr[$key]['house']   = $house;
            $recordArr[$key]['measure'] = C('CHARGES')[$item['style']];
            $recordArr[$key]['category']= C('CHARGES')[$item['category']];
            $recordArr[$key]['billing'] = C('CHARGES')[$item['billing']];
            $recordArr[$key]['price']   = $rec['price'];
            $recordArr[$key]['id']      = $rec['id'];
            $recordArr[$key]['name']    = $userName;
            $recordArr[$key]['code']    = $usercode;
        }
        $record_count = count($recordArr);
        //$property = $chargesMod->selectALLProp($compid);           //查出该企业下所有楼盘
        
        //this->assign('property',$property);
        $this->assign('record',$recordArr);
        $this->assign('Rcount',$record_count);
        $this->assign('compid',$compid);
        $this->display();
    
    } 
    public function flow(){                                          //加载更多AJAX
         
        $count     = $_GET['count'];
        $compId    = $_GET['compid'];
        $chargesMod = D('charges');
        $buildMod   = D('building');
        $allRecord = $chargesMod->selectCharges($compId,$count,10); 
        $html      = '';
        if($allRecord){
            foreach($allRecord as $var){
                $cycleStr = '';
                $item    = $chargesMod->selectChargesItem($var['chm_id']);
                $prop    = $chargesMod->selectPro($var['cc_id'])['name'];
                $house   = $chargesMod->selectHouse($var['hm_id'])['number'];
                $build   = $buildMod->selectBuild($var['bm_id'])['name'];
                $cycle= explode(",",$var['charging_cycle']);
                foreach($cycle as $vo){
                    $cycleStr .= $vo."月";
                }
                $len     =strlen($cycleStr);
                $newstr  =substr($cycleStr,0,$len-3);        
                $measure = C('CHARGES')[$item['style']];
                $category= C('CHARGES')[$item['category']];
                $billing = C('CHARGES')[$item['billing']];
                
                $html .= "<div class='nor_menber'>
                	        	<p>{$prop}
                	        		 <em>{$build}</em><em>{$house}</em>
                	        		 <span>{$var['time']}</span><br/>
                	        		 <span>{$item['name']}</span><span>{$category}</span>
                	        		 <span>{$billing}</span>
                	        		 <span>{$measure}</span>
                	        		 <span>{$var['price']}</span>
                	        		 <span>计费周期：{$newstr}</span>
                	        	</p>
                	        	<a href='{U('Propertycharges/edit?rid=".$var['id']."')}'><img src='/Public/images/u7.png'><br/>编辑</a>
                	        </div> ";
            }
            $result['flag'] = 'success';
            $result['html'] = $html;
            exit (json_encode ($result));
        }elseif($allRecord==NULL){
            $result['flag'] = 'empty';
            $result['html'] = $html;
            exit (json_encode ($result));
        }else{
            $result['flag'] = 'fail';
            $result['html'] = $html;
            exit (json_encode ($result));
        }
    
    }
    
    public function set(){                                  //房产收费管理设置
        $compId    = $_GET['compid'];
        $propMod   = D('charges');
        if($_GET['chargeId']){
            $chargeId  = $_GET['chargeId'];
            $globalSet = $propMod->selectGlobalChargerSet($compId, $chargeId);     //查询所有该企业 下单个全局设置  
            $globalSet['billing_text']  = C('CHARGES')[$globalSet['billing']];  //计费方式
            $globalSet['measure_text']  = C('CHARGES')[$globalSet['measure']];  //计量方式
            $globalSet['category_text'] = C('CHARGES')[$globalSet['category']]; //项目类别
            exit(json_encode ( $globalSet ));
            
        }else{
            $userMod    = D('user');
            $globalsSet = $propMod->selectGlobalChargersSet($compId);  //查询所有该企业 下全局设置       
            $allProperty = $propMod->selectALLProp($compId);           //该企业 下所有楼盘 
            $userInfo    = $userMod->find_user_info($this->userID);
            $this->assign('property',$allProperty);
            $this->assign('user',$userInfo);
            $this->assign('compId',$compId);
            $this->assign('set',$globalsSet);
            $this->display();
        }
    }
    
    public function get_build_house(){                                           //ajax根据楼盘ID获取楼宇房间数据
        $split = $_REQUEST['split'];
        $html  = '';
        switch($split){
            case 1:
                $buildMod = D('building');
                $pid      = $_REQUEST['pid'];
                $result   = $buildMod->selectAllBuild($pid,0,0);                         //查询楼宇
                foreach($result as $build){
                    $html .="<option value={$build['id']}>{$build['name']}</option>" ;
                    
                }                                
                break;
            case 2:
                $houseMod = D('charges');
                $bid      = $_REQUEST['bid'];
                $result   = $houseMod->selectHouseForBuild($bid);                         //查询房间
                foreach($result as $house){
                    $html .="<option value={$house['id']}>{$house['number']}</option>" ;
                
                }
                break;
        }
        $html .= "<option value='' selected>--空--</option>";
        exit($html);
        
        
    }
    
    public function set_list(){                                           //ajax组装html到设置列表
        $prop    = I('post.prop');
        $build   = I('post.build');
        $house   = I('post.house');
        $compId  = I('post.compid');
        $special = $_POST['special'];//I('post.special');
        $propMod = D('charges');                                 //
        $special_array = json_decode ($special, true);              //json转换数组
        $data['cc_id']       = $prop;
        $data['bm_id']       = empty($build)?null:$build;
        $data['hm_id']       = empty($house)?null:$house;
        $data['cm_id']       = $compId;
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['modify_time'] = date('Y-m-d H:i:s');
        $data['user_id']     = $this->userID;
        $status = array('flag'=>'','number'=>2000);//jaxa数据描述
        $same_count = 0; //相同记录统计
        foreach($special_array as $spec){
            $data['price']          = $spec[0];
            $cycle = str_replace("月,",",",$spec[1]);
            $data['charging_cycle'] = $cycle;
            $data['chm_id']         = $spec[2];
            $data['remark']         = $spec[3];
            $new_record = array('price'=>$spec[0],'cycle'=>$cycle);

            $set_record_exist = $propMod->selectRepeatSet($prop,$build,$house,$data['chm_id'],$compId);//查出是否有此记录 
            if($set_record_exist){
                $data['id'] = $set_record_exist['id'];
                $result = $propMod->save($data);
                $status['flag'] = 'update';
            }else{
                $result = $propMod->add($data);
                $status['flag'] = 'add';
            }
          
        }
        if($result){
            $msg = array('charge_setting_id' => $result , 'data'=> $data);                         //推入消息队列
            RabbitMQ::publish(IMPORT_QUEUE, json_encode($msg));
        }else{
            $status['number']= 2001;
        }
        
        exit(json_encode ( $status ));
    }
    
    public function edit(){                                  //房产收费管理编辑显示页面
        $rid        = $_GET['rid'];
        $compId     = $_GET['compid'];
        $chargesMod = D('charges');
        $buildMod   = D('building');
        $record     = $chargesMod->selectChargesForId($rid);
        
        $cycleStr = '';
        $item    = $chargesMod->selectChargesItem($record['chm_id']);
        $prop    = $chargesMod->selectPro($record['cc_id']);
        $house   = $chargesMod->selectHouse($record['hm_id']);
        $build   = $buildMod->selectBuild($record['bm_id']);
        $cycle= explode(",",$record['charging_cycle']);
        foreach($cycle as $var){
            if(empty($var)){
                continue;
            }else{
                $cycleStr .= $var.",";
            }
        }
        $newstr = rtrim($cycleStr,',');
        $recordArr['charges'] = $item['name'];
        $recordArr['chargesid'] = $record['chm_id'];
        $recordArr['cycle']   = $newstr;
        $recordArr['prop']    = $prop['name'];
        $recordArr['propid']  = $record['cc_id'];
        $recordArr['build']   = $build['name'];
        $recordArr['buildid'] = $record['bm_id'];
        $recordArr['house']   = $house['number'];
        $recordArr['houseid'] = $record['hm_id'];
        $recordArr['measure'] = C('CHARGES')[$item['style']];
        $recordArr['category']= C('CHARGES')[$item['category']];
        $recordArr['billing'] = C('CHARGES')[$item['billing']];
        $recordArr['price']   = $record['price'];
        $recordArr['id']      = $record['id'];
        $recordArr['remark']  = $record['remark'];
        $this->assign('record',$recordArr);
        $this->assign('compid',$compId);
        $this->display();
    }
    
    public function do_edit(){                                  //房产收费管理编辑保存
        $data['cc_id']            = I('post.pid');
        $data['cm_id']            = I('post.compid');
        $data['bm_id']            = empty(I('post.bid'))?null:I('post.bid');
        $data['hm_id']            = empty(I('post.hid'))?null:I('post.hid');
        $data['chm_id']          = I('post.chmid');
        $data['price']          = I('post.price');//$_POST['price'];
        $data['remark']         = I('post.remark');//$_POST['remark'];
        $data['charging_cycle'] = I('post.cycle');//$_POST['cycle'];
        $data['id']             = I('post.rid');//$_POST['rid'];
        
        $chargesMod = D('charges');
        $result     = $chargesMod->save($data);
        $msg        = array('charge_setting_id' => $data['id'], 'data'=> $data);              //推入消息队列（）
        RabbitMQ::publish(IMPORT_QUEUE, json_encode($msg));
        if($result){
            exit('success');
        }elseif($result==0){
            exit('nochang');
        }else{
            exit('fail');
        }

    }
    
    /**
     * 收费预览页面
     */
    public function review(){
        //接收数据
        $compId=I('get.compid','');
    
        //实例化模型
        $chargesModel=D('charges');
        //查询该企业下所有楼盘
        $propertyList=$chargesModel->selectALLProp($compId);
        
        $this->assign('compid',$compId);
        $this->assign('propertyList',$propertyList);
        $this->display();
    }
    
    /**
     * 根据楼盘ID获取楼宇列表
     */
    public function get_all_building(){
        //接收数据
        $ccId=I('post.cc_id','');
        
        if ($ccId){
            //实例化模型
            $buildingModel=D('building');
            $buildList=$buildingModel->selectAllBuild($ccId,0,0);
            if (!$buildList){
                retMessage(false,null,'查询不到记录','查询不到记录',4002);
                exit;
            }
            retMessage(true,$buildList);
            exit;
        }else {
            retMessage(false,null,'接收不到数据','接收不到数据',4001);
            exit;
        }
    }
    
    /**
     * 收费预览
     */
    public function get_charges_setting(){
        //接收数据
        $compId=I('post.compid','');
        $page=I('post.page',1);
        
        if ($compId){
            $ccId=I('post.cc_id','');
            $bmId=I('post.bm_id','');
            $hmNumber=I('post.hm_number','');
            $category=I('post.category','');
            $chargingCycle=I('post.charging_cycle','');
            
            //实例化模型
            $chargesModel=D('charges');
            //查询收费设置列表
            $chargesSettingList=$chargesModel->get_charges_setting_list($compId,$ccId,$bmId,$hmNumber,$category,$chargingCycle);
            if ($chargesSettingList[0]!=C('OPERATION_SUCCESS')){
                retMessage(false,null,'查询不到记录','查询不到记录',4002);
                exit;
            }
            //计算数据的总页数
            $total=ceil(count($chargesSettingList[1])/10);
            if ($page>$total){
                retMessage(false,null,'全部加载完毕','全部加载完毕',4003);
                exit;
            }
            $offset=($page-1)*10;
            $chargesSettingList[1]=array_slice($chargesSettingList[1], $offset,10);
            $data=array('list'=>$chargesSettingList[1],'total'=>$total);
            retMessage(true,$data);
            exit;
        }else {
            retMessage(false,null,'未接收到数据','未接收到数据',4001);
            exit;
        }
    }
    
    /**
     * 未出账单页面
     */
    public function unbill(){
        //接收数据
        $compId=I('request.compid','');
        if (! $compId) {
            $this->error('对不起，你无此权限进入！！', U('User/login'));
            exit();
        }
        $flag = I('post.flag', '');
        
        //实例化模型
        $propertyModel=D('property');
        $chargeModel=D('charge');
        $chargesModel=D('charges');
        
        if (!$flag){
            //未带搜索条件
            
            //获取年份列表
            $year=date('Y',time());
            for ($i=(int)$year;$i>($year-20);$i--){
                $yearList[]=$i;
            }
            //查询该企业下所有费项
            $houseIds=$propertyModel->get_all_house_ids($compId,'company');
            $houseInfoList=$propertyModel->get_house_belong($houseIds);
            $chargeList=$chargeModel->get_charge_list($compId)[1]['list'];
            //查询楼盘和楼宇列表
            $propertyList=$chargesModel->selectALLProp($compId);
            //查询该企业下所有的未生成账单
            $unBillList=$chargesModel->get_unbill_list($compId,$houseInfoList,0,10);
            
            $this->assign('compid',$compId);
            $this->assign('yearList',$yearList);
            $this->assign('propertyList',$propertyList);
            $this->assign('chargeList',$chargeList);
            $this->assign('unBillList',$unBillList['list']);
            $this->assign('total',$unBillList['total']);
            $this->display();
        }else {
            //附带搜索条件
            //接收数据
            $year=I('post.year','');
            $month=I('post.month','');
            $cc_id=I('post.cc_id','');
            $bm_id=I('post.bm_id','');
            $hm_number=I('post.hm_number','');
            $charge_id=I('post.charge_id','');
            
            //查询该企业下所有的未生成账单
            if ($bm_id){
                $id=$bm_id;
                $type='building';
            }elseif ($cc_id){
                $id=$cc_id;
                $type='community';
            }else {
                $id=$compId;
                $type='company';
            }
            $houseIds=$propertyModel->get_all_house_ids($id,$type);
            $houseInfoList=$propertyModel->get_house_belong($houseIds);
            $unBillList=$chargesModel->get_unbill_list($compId,$houseInfoList,0,10,$year,$month,$hm_number,$charge_id);
            if (!$unBillList){
                retMessage(false,null,'查询不到记录','查询不到记录',4002);
                exit;
            }
            retMessage(true,$unBillList['list']);
            exit;
        }
    }
    
    /**
     * 未出账单-加载更多
     */
    public function loadMore(){
        //接收数据
        $compId = I('post.compid', '');
        $flag = I('post.flag', '');
        $page = I('post.page', '');
        
        if ($compId && $flag && $page){
            //实例化模型
            $propertyModel=D('property');
            $chargesModel=D('charges');
            if ($flag==1){
                //未带搜索条件
                //查询该企业下所有的未生成账单
                $houseIds=$propertyModel->get_all_house_ids($compId,'company');
                $houseInfoList=$propertyModel->get_house_belong($houseIds);
                $unBillList=$chargesModel->get_unbill_list($compId,$houseInfoList,$page,10);
                if (!$unBillList){
                    retMessage(false,null,'查询不到记录','查询不到记录',4002);
                    exit;
                }
                if (($page/10)>=$unBillList['total']){
                    retMessage(false,null,'全部加载完毕','全部加载完毕',4003);
                    exit;
                }
                retMessage(true,$unBillList['list']);
                exit;
            }elseif ($flag==2){
                //附带搜索条件
                $year=I('post.year','');
                $month=I('post.month','');
                $cc_id=I('post.cc_id','');
                $bm_id=I('post.bm_id','');
                $hm_number=I('post.hm_number','');
                $charge_id=I('post.charge_id','');
                
                //查询该企业下所有的未生成账单
                if ($bm_id){
                    $id=$bm_id;
                    $type='building';
                }elseif ($cc_id){
                    $id=$cc_id;
                    $type='community';
                }else {
                    $id=$compId;
                    $type='company';
                }
                $houseIds=$propertyModel->get_all_house_ids($id,$type);
                $houseInfoList=$propertyModel->get_house_belong($houseIds);
                $unBillList=$chargesModel->get_unbill_list($compId,$houseInfoList,$page,10,$year,$month,$hm_number,$charge_id);
                if (!$unBillList){
                    retMessage(false,null,'查询不到记录','查询不到记录',4002);
                    exit;
                }
                if (($page/10)>=$unBillList['total']){
                    retMessage(false,null,'全部加载完毕','全部加载完毕',4003);
                    exit;
                }
                retMessage(true,$unBillList['list']);
                exit;
            }
        }else {
            retMessage(false,null,'未接收到数据','未接收到数据',4001);
            exit;
        }
    }
    
    /**
     * 未出账单-录入优惠
     */
    public function do_discount(){
        //接收数据
        $id=I('post.accountId','');
        $preferentialMoney=I('post.discount','');
        $description=I('post.discountRemark','');
        
        if ($id && $preferentialMoney){
            //组装更新数据
            $data=array(
                'id'=>$id,
                'preferential_money'=>floatval($preferentialMoney),
                'description'=>$description,
                'status'=>1,
                'modify_time'=>date('Y-m-d H:i:s',time())
            );
            
            //实例化模型
            $chargesModel=D('charges');
            //录入优惠
            $result=$chargesModel->add_discount($id,$data);
            if (!$result){
                retMessage(false,null,'录入失败','录入失败',4002);
                exit;
            }
            retMessage(true,true);
        }else {
            retMessage(false,null,'未接收到数据','未接收到数据',4001);
            exit;
        }
    }
    
    /**
     * 未出账单-删除账单
     */
    public function del_discount(){
        //接收数据
        $id=I('post.account_id','');
        
        if ($id){
            //组装更新数据
            $data=array(
                'id'=>$id,
                'preferential_money'=>0,
                'description'=>null,
                'status'=>'-1',
                'modify_time'=>date('Y-m-d H:i:s',time())
            );
            //实例化模型
            $chargesModel=D('charges');
            //删除优惠
            $result=$chargesModel->del_discount($id,$data);
            if (!$result){
                retMessage(false,null,'删除优惠失败','删除优惠失败',4002);
                exit;
            }
            retMessage(true,true);
        }else {
            retMessage(false,null,'未接收到数据','未接收到数据',4001);
            exit;
        }
    }
    
    /**
     * 未出账单-生成账单
     */
    public function generate_bills(){
        //接收数据
        $compId = I('post.compid', '');
        $year=I('post.year','');
        $month=I('post.month','');
        $cc_id=I('post.cc_id','');
        $bm_id=I('post.bm_id','');
        $hm_number=I('post.hm_number','');
        $charge_id=I('post.charge_id','');
        
        if ($compId){
            //组装数据
            $data=array(
                'cm_id'=>$compId,
                'data'=>array(
                    'year'=>$year,
                    'month'=>$month,
                    'charge_id'=>$charge_id
                )
            );
            //楼宇ID存在
            if ($bm_id){
                $data['data']['id']=$bm_id;
                $data['data']['type']='building';
            }
            //楼宇ID不存在，存在楼盘ID
            if ($cc_id && (!$bm_id)){
                $data['data']['id']=$cc_id;
                $data['data']['type']='community';
            }
            //楼盘和楼宇ID不存在，使用企业ID
            if ($compId && (!$cc_id) && (!$bm_id)){
                $data['data']['id']=$compId;
                $data['data']['type']='company';
            }
            
            //实例化模型
            $chargesModel=D('charges');
            $result=$chargesModel->generate_bills($compId,$data,$hm_number);
            if (!$result){
                retMessage(false,null,'生成账单失败','生成账单失败',4002);
                exit;
            }
            retMessage(true,true);
            exit;
        }else {
            retMessage(false,null,'未接收到数据','未接收到数据',4001);
            exit;
        }
    }
}


