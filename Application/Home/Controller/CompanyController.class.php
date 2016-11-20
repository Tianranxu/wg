<?php
/*************************************************
 * 文件名：CompanyController.class.php
 * 功能：     企业管理控制器
 * 日期：     2015.7.23
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
use Think\Model;
use TemplateSMS\CCPRestSmsSDK;
class CompanyController extends AccessController{
    
    public function _initialize() {
        parent::_initialize();
    }

    public function allActions($moduleName){   //查出此控制器下所有操作（包括没权限禁用的）
        $assign = array();
        $model  = D('access');
        $tote   = $model->selectModule($moduleName);         //查出此模块下所有操作
        return $tote;
    }

    public function validAction($tote, $id) {
        $assign = array();
        foreach($tote as $item){
            $menu = substr($item['name'],strrpos($item['name'],'\\',-1));
            $assign[$item['id']]['name']   = $menu;
            $assign[$item['id']]['title']  = $item['title'];
            $status = in_array($item['id'],$id)? 'normal': 'forbidden';
            $assign[$item['id']]['status'] = $status;
        }
        return $assign;
    }

    public function homePageMenu($id) {
        #TODO: 此id为fx_sys_auth_rule中的id.
        $needIds = array('6', '13', '19','112');
        $menu = array();
        foreach($needIds as $item){
            $status = in_array($item,$id)? 'normal': 'forbidden';
            $menu[$item]['status'] = $status;
        }
        return $menu;
    }

    public function allOperation($moduleName, $id){ //查出此控制器下所有操作（包括没权限禁用的）
        $tote = $this->allActions($moduleName);
        return $this->validAction($tote, $id);
    }

    public function index(){                                  //企业管理首页
        
        $companyMod = D('company');
        $grotempMod  = D('grouptemp');
        $roletempMod = D('roletemp');
        $groupArr   = array();                                //默认群组组
        $defGroup    = array();
        $mold       = array();                                //算定义群组
        $stopService= array();                                //停止服务群组

        $this->menuName = $this->allOperation($this->contrName, $this->ruleID);
        $actions    = $this->allActions($this->contrName);

        $groupArr   = $companyMod->selectGroup($this->userID);     //群组二维数组
        $groupids = array_map(function ($x){return $x['id'];}, $groupArr);
        //$groupids = array_map(function ($x){return $x->id;}, $groupArr);这是谁添加的，我屏蔽了要不然出错？？


        $companies = $companyMod->selectCompanyByGroups($groupids, $this->userID);
        $companyIDs = array_map(function ($x){return $x['id'];}, $companies);

        $rules = $roletempMod->selectRuleIdByUserInCompany($this->userID, $companyIDs);
        //var_dump($groupArr);exit;
        $peoples = $grotempMod->selectPeopleCountByCompanies($companyIDs);

        $allGroups = array();
        foreach ($groupArr as $gi => $g) {
            $allGroups[$g['id']] = array(
                'id' => $g['id'],
                'type' => $g['type'],
                'company' => array(),
                'title' => $g['title'],
                'count' => 0
            );
        }
        foreach ($companies as $ci => $c) {
            switch ($c['category']){
                case 1 :
                    $cm_type = '物管';
                    break;
                case 2 :
                    $cm_type = '维修';
                    break;
                case 3 :
                    $cm_type = '工作站';
                    break;
                default:
                    $cm_type = '';
                    break;
            }
            $company = array(
                'id' => $c['id'],
                'name' => $c['name'],
                'category' => $cm_type,
                'cate_id' => $c['category'],
                'number' => $c['number']
            );
            foreach ($peoples as $pi => $p) {
                if ($p['cm_id'] == $c['id']) {
                    $company['count'] = $p['count'];
                    break;
                }
            }
            $auth = '';
            foreach ($rules as $ri => $r) {
                if($r['cm_id'] == $c['id']){
                    //循环查询本人在此公司所有角色中是否有 '6', '13', '19','112‘权限
                    $auth .= $r['rule_id'].',';
                }
            }
            $company['menu'] = $this->homePageMenu(explode(",", rtrim($auth)));
            $allGroups[$c['group_id']]['company'][] = $company;
            $allGroups[$c['group_id']]['count'] = count($allGroups[$c['group_id']]['company']);

        }
        
        foreach ($allGroups as $gi => $g) {
            switch($g['type']){
                case 1:
                    $defGroup = $g;
                    break;
                case 2:
                    $mold[$gi] = $g;
                    break;
                case 3:
                    $stopService = $g;                 
                    break;
            }
        }
        $grotempMod  = D('grouptemp');
        $allCompany = $grotempMod->selectcompanyforUser($this->userID);        //查询 本用户下所有企业
        $this->assign('defgroup',$defGroup);                     //发送默认群组
        $this->assign('group',$mold);                            //发送自定义群组
        $this->assign('sotpgroup',$stopService);                 //发送停止服务群组
        $this->assign('meun',$this->menuName);                   //发送所有操作菜单
        $this->assign('userid',$this->userID);                   //发送userid
        $this->assign('company',$allCompany);                   //发送所有企业
        $this->display();
    }
    /*******************************************************************************************以下全是AJAX****************************************************************************/
    public function getpeople($cid='',$gid=''){                                //AJAX获取企业人员
        $companyID   = $cid==''?$this->companyID:$cid;
        $companyMod  = D('company'); 
        $grotempMod  = D('grouptemp');
        $peopleArr   = $grotempMod->selectPeople($companyID);           //查询此分组和企业下所有人员信息
        $selfRoleFor = $companyMod->selectRoleName($companyID,$this->userID);   //查询自己在此企业下是什么角色
        $selfRoleArr = array();
        foreach($selfRoleFor as $vo){
            $selfRoleArr[] = $vo['id'];
        }
        //管理员ID
        $admin = $companyMod->getAdminIdToCompId($companyID);
        //是否是管理员
        $manage = in_array($admin,$selfRoleArr)?1:-1;
        //菜单权限
        $this->menuName = $this->allOperation($this->contrName, $this->ruleID);

        foreach($peopleArr as $var){ 
            $roleStr = '';                                 //个人在此企业的所有角色
            $roleNameArr = $companyMod->selectRoleName($companyID,$var['id']);       //查询角色名字        
            $self = $var['id']==$this->userID?'normal':'forbidden';         //是否是自己
            $islog = $var['ip']==null?'(待注册)':'';                             //检测 是否注册登入过
            foreach($roleNameArr as $val){
                $roleArr[] = $val['id'];
                $roleStr  .= "<span>{$val['name']}</span>";
            }
            $var['log'] = $islog;
            $var['style'] = $islog==''?'has':'not';
            $var['manage'] = $manage;
            $var['self'] = $self;
            $var['role'] = $roleStr;
            $var['del'] = $this->menuName[16]['status'];
            $var['move'] = $this->menuName[17]['status'];
            $var['set'] = $this->menuName[18]['status'];
            $people[] = $var;
        }
       exit(json_encode ($people));
        
    }
    public function addgroup(){                             //添加新组
       
        $data['title'] = I('post.name'); 
        $gid  = I('post.groupid');  
        $data['modify_time']= date('Y-m-d H:i:s');
        $groupMod   = D('group');
        $model      = new Model();
        $model->startTrans();
        if(empty($gid)){
            $data['user_id']    = $this->userID;
            $data['type']       = 2;
            $data['description']= "自定义群组";
            $data['create_time']= date('Y-m-d H:i:s');
            $result = $groupMod->add($data);
        }else{           
            $data['id'] = $gid;
            $result = $groupMod->save($data);
        }
        if($result){          
             $model->commit();
             echo 'success';
  
        }else{
            $model->rollback();
            echo 'fail';
             
        }
    }
    public function delgroup(){                       //删除群组
        $id         = I('post.groupid');
        $def_id     = I('post.def_gid');
        $groupMod   = D('group');
        $grotempMod = D('grouptemp');
        $model      = new Model();
        $model->startTrans(); 
        $Gresult  = $groupMod->delete($id);                       //删除组表记录
        $update_grouptemp_table = $grotempMod->update_group($id, $def_id);  //更新数据
        if($update_grouptemp_table){
            $model->commit();
            exit('success');
        }else{
            $model->rollback();
            exit('fail');
        }



    } 
    public function addmember(){                                //添加成员分配角色
        $cid         = I('get.companyid');
        $gid         = I('get.groupid');
        $companyMod  = D('company');
        //TODO 根据公司类型
        $type        = $companyMod->getRoleTypeToCompId($cid); //查询出所属角色数据
        $role        = $companyMod->selectAllRole($type);
       if($role){
            $html['status'] = 'ok';
        }else{
            $html['status'] = 'fail';
        }
        $html['role']   = $role;                  
        exit (json_encode ($html));
    }
    public function do_add(){                                      //添加成员
        $userName = I('post.name');
        $userRole = I('post.roleid');            //是数组
        $roleid   = explode(',',$userRole);
        $userPhone= I('post.phone');
        $cid      = I('post.companyid');
        $gid      = I('post.groupid');
        
        $userMod     = D('user');
        $userTempMod = D('roletemp');
        $groTempMod  = D('grouptemp');
        $groupMod    = D('group');
        $i=0;
        $Model       = new model();
        $Model->startTrans();
        $is_exist    = $userMod->selectUserInfo($userPhone);                   //判断手机号是否存在
        $add_user_id = $is_exist['id'];
        $group_id    = $groTempMod->be_exist($add_user_id,$cid);
        if($is_exist){  
            if($group_id){
                $userMod->where('id='.$add_user_id)->setField('create_time',date('Y-m-d H:i:s'));//更新用户创建时间
                $Model->commit();
                exit('4000');
            }else{
                foreach($roleid as $vo){
                    $role[] = array('cm_id'=>$cid,'role_id'=>$vo,'user_id'=>$add_user_id);                   
                }
                $allot_role = $userTempMod->addAll($role);         //写入分配权限
                
                $default_group_id = $groupMod->selectDefaultgid($add_user_id)['id']; //查用户默认分组
                $allot_group      = $groTempMod->add(array('user_id'=>$add_user_id,'cm_id'=>$cid,'group_id'=>$default_group_id));
                $userMod->where('id='.$add_user_id)->setField('create_time',date('Y-m-d H:i:s'));//更新用户创建时间
            }
            if(!$allot_role){
                $Model->rollback();
                exit('4001');
            }elseif(!$allot_group){
                $Model->rollback();
                exit('4002');
            }
            $Model->commit();
            exit('2000');
        
        }else{                                                      
            vendor("TemplateSMS.CCPRestSmsSDK");                                              //加载短信平台插件
            $SendTemplateSMS = new CCPRestSmsSDK(ACCOUNT_SID, AUTH_TOKEN, SMS_CHUYUN_APPID);
            $groupMod = D('group');
            $data['name']         = $userName;
            $data['code']         = $userPhone;
            $pass                 = random_password(6,'123456789');
            $data['dyn_password'] = MD5($pass);
            $data['create_time']  = date('Y-m-d H:i:s');
            //写入用户数据库
            $Uresult = $userMod->add($data);
            //更新邀请人字段
            $userMod->updateInviter($Uresult, $this->userID);
            
            $groData[] = array('type'=>1,'title'=>'默认分组','description'=>'系统自带','create_time'=>date('Y-m-d H:i:s'),'user_id'=>$Uresult,'modify_time'=>date('Y-m-d H:i:s'));
            $groData[] = array('type'=>3,'title'=>'停止服务','description'=>'系统自带','create_time'=>date('Y-m-d H:i:s'),'user_id'=>$Uresult,'modify_time'=>date('Y-m-d H:i:s')); 
            $allot_def_group  = $groupMod->addAll($groData);         //写入两条系统自带分组
            
            $Gdata = array('user_id'=>$Uresult,'cm_id'=>$cid,'group_id'=>$allot_def_group);                  
            $allot_group = $groTempMod->add($Gdata);                              //写入新增的用户和分组中间表                                                      
            
            foreach($roleid as $vo){            
                $role_data[] = array('cm_id'=>$cid,'role_id'=>$vo,'user_id'=>$Uresult);
            }
            $role_data[] = array('cm_id'=>null,'role_id'=>DEFAULT_USER,'user_id'=>$Uresult);  //添加一条默认权限
            $allot_role = $userTempMod->addAll($role_data);                                  //写入所有权限
            if(!$allot_role){
                $flag = '4001';
                $Model->rollback();
                exit($flag);
            }elseif(!$allot_group){
                $flag = '4002';
                $Model->rollback();
                exit($flag);
            }
            $msgStatus = sendMsg($SendTemplateSMS,$data['code'],array($pass),$this->userID);
            if($msgStatus){
                $Model->commit();
                $flag = '2000';
            }else{
                $Model->rollback();
                $flag = '4003';
            }

            exit($flag);
        }                         
    }
    //查询手机号是否存在数据库
    public function selectphone(){
        $number  = I('get.phone');
        $name    = I('get.name');
        $userMod = D('user');
        $result  = $userMod->selectUserInfo($number);
        if($result){
            exit (json_encode ($result));
        }else{  
            $result['name'] = '';
            $result['id']   = null;
            $result['code'] = null;
            exit (json_encode ($result));
        }
    }
    public function delmember(){                                  //删除成员
        $cid         = I('post.companyid');
        $uid         = I('post.userid');
        $roltempMod  = D('roletemp');
        $compMod     = D('company');
        $admin = $compMod->getAdminIdToCompId($cid);
        $result = $roltempMod->selectManage($cid,$uid,$admin);
        if($result['Manage'] && $result['count'] == 1){                                       //判断是否只有一个管理员，如果是就不能删除
            exit('only') ;
        }else{
            $result = $compMod->deleteTempTable($uid, $cid)  ;//删除 用户在此企业 下所有记录
            if($result){
                exit('success');
            }else{
                exit('fail');
                 
            }

        }
        
    }
    public function editpowe(){                                          //权限编辑
        $uid         = I('post.userid');
        $cid         = I('post.companyid');
        $roleTempMod = D('company');
        $roleMod     = D('role');
        $compMod   = D('Company');
        // TODO 根据公司类型
        $type = $compMod->getRoleTypeToCompId($cid); //查询出所属角色数据
        $allRole = $roleMod->allRole($type);                                     //查出 所有业务角色
        $roleid = $roleTempMod->selectRoleName($cid,$uid);                    //获取该企业下此用户所有角色
        $allRole = $allRole?$allRole:array();
        $userRole    = array();
        foreach($roleid as $vo){
            $userRole[] = $vo['id'];
        }                                
        if($roleid){
            $html['status'] = 'ok';
        }else{
            $html['status'] = 'fail';
        }
        $html['is_role'] = $userRole;
        $html['all_role'] = $allRole;
        
        exit (json_encode ($html));
        
    }
    public function do_editpowe(){                                               //把权限写入数据库
        $cid         = I('post.companyid');
        $uid         = I('post.userid');
        $ridStr      = I('post.roleid');
        $rid         = explode(',',$ridStr);
        $roleTmepMod = D('roletemp');
        $Model       = new model();
        $already     = $roleTmepMod->editpowe($uid,$cid);         //查询个人在某公司下所有角色
        $alreadyRole = array();
        $Model->startTrans();
        $compMod = D('company');
        //公司管理员ID
        $admin = $compMod->getAdminIdToCompId($cid);
        $manage = $roleTmepMod->selectManage($cid, $uid, $admin);             //判断是否是管理员，
        if($manage['Manage'] && $manage['count'] == 1){
            $is_mansge = true;
        }else{
            $is_mansge = false;
        }
            
        foreach($already as $vo){
            $alreadyRole[] = $vo['role_id'];
            if(!in_array($vo['role_id'],$rid)){
                //管理员ID
                $compMod = D('Company');
                $admin = $compMod->getAdminIdToCompId($cid);
                if(!$is_mansge || $vo['role_id'] != $admin){
                    $result = $roleTmepMod->where("user_id={$vo['user_id']} AND cm_id={$vo['cm_id']} AND role_id={$vo['role_id']}")->delete();
                    if(!$result){
                        $Model->rollback();
                        exit('delFail');
                    }
                }else{
                    $Model->rollback();
                    exit('only');
                }
            }
        }
        foreach($rid as $val){
            $data['user_id'] = $uid;
            $data['cm_id']   = $cid;
            $data['role_id'] = $val;
            if(!in_array($val,$alreadyRole)){
                $result = $roleTmepMod->add($data);
                if(!$result){
                    $Model->rollback();
                    exit('addFail');
                }
            }
        }
        $Model->commit();
        exit('success');   
    }
    public function delcomp(){                                                   //删除企业
        $id      = I('post.companyid');
        $compMod = D('company');
        $data    = array('status'=>-1,'is_delete'=>-1);
        $Cresult = $compMod->where('id=%d', $id)->setField($data);
        //如果是维修公司就禁用与之相关的物业公司维修设备中间表
        $cm_type = $compMod->where('id=%d',$id)->getField('cm_type');
        if($cm_type == C('COMPANY_TYPE.REPAIR')){
            $compDevMod = D('Compdevice');
            $compDevMod->setCompanyDeviceStatus($id);
        }
        if($Cresult){
            
            echo 'success';
        }else{
            echo 'fail';
        }
    }
    public function stopcomp(){                                                       //停止企业
        $cid = I('post.companyid');
        $compMod = D('company');
        $grotempMod = D('grouptemp');
        //企业表status更改为-1
        $Cresult = $compMod->where('id=%d', $cid)->setField('status',-1);
        //移动到停止分组
        $updateResult = $grotempMod->updateGroupAll($cid, 3);
        //如果是维修公司就禁用与之相关的物业公司维修设备中间表
        $cm_type = $compMod->where('id=%d', $cid)->getField('cm_type');
        if($cm_type == C('COMPANY_TYPE.REPAIR')){
            $compDevMod = D('Compdevice');
            $compDevMod->setCompanyDeviceStatus($cid);
        }
        if($updateResult){
            echo 'success';
        }else{
            echo 'fail';
        }
    }
    public function renewcomp(){                                                      //恢复服务
    
        $cid = $_POST['companyid'];
        
        $compMod = D('company');
        $grotempMod = D('grouptemp');        
        $Cresult = $compMod->where('id='.$cid)->setField('status',1);
        $updateResult = $grotempMod->updateGroupAll($cid, 1);
      
        if($updateResult){
            echo 'success';
        }else{
            echo 'fail';
        }
    }
    public function movetocomp(){                                         //移动到某个企业
        $cid        = I('post.companyid');
        $uid        = I('post.userid');
        $gid        = I('post.groupid');  
        $newCid     = I('post.newcompanyid');
        $compMod = D('company');
        $grotempMod = D('grouptemp');
        $roltempMod = D('roletemp');
        //公司管理员ID
        $admin = $compMod->getAdminIdToCompId($cid);
        $result     = $roltempMod->selectManage($cid, $uid, $admin);
        if($result['Manage'] && $result['count'] == 1){                                       //判断是否只有一个管理员，如果是就不能移动
            exit('only') ;
        }
        $is_exist_comp = $grotempMod->selectgroupID($newCid,$uid); //判断是否已在目标企业

        if($is_exist_comp){
            exit('exist');
        }
        $data       = array('cm_id'=>$newCid);
        $Cresult = $grotempMod->where("user_id={$uid} AND cm_id={$cid}")->setField($data);
        if($Cresult){
            echo 'success';
        }else{
            echo 'fail';
        }
    }
  
    public function addcompany(){                               //新建企业页面
        $companyMod = D('company');
        $userMod = D('user');
        if(I('post.number')!='found'){    

            $group = $companyMod->selectGroupForAdd($this->userID);///selectGroupForAdd
            $current_user_info = $userMod->find_user_info($this->userID);
            $this->assign('current_user',$current_user_info);
            $this->assign('group',$group);
            $this->display();
        }else{                                           //要用事务处理
            $number = $this->checkcode();
            if ($number === 0 ){
                $this->error("邀请码不正确",U('company/addcompany'));
            }
            $model        = new Model();
            $groupTempMod = D('grouptemp');
            $usertemp     = D('roletemp');
            
            $lastNO     = $companyMod->selectLast();                             //创建一个唯一企业编号      
            $lastNO     = substr($lastNO,strpos($lastNO,"Y")+1);
            $lastNO     = $lastNO+1;
            $lastNO     = 'QY'.str_pad($lastNO,6,"0",STR_PAD_LEFT);
            $model->startTrans();
            $post = array(
                'create_time' => date('Y-m-d H:i:s'),
                'modify_time' => date('Y-m-d H:i:s'),
                'number' => $lastNO,
                'name' => I('post.name'),
                'cm_type' => I('post.cm_type'),
                'description' => I('post.description'),
                'contacts' => I('post.contacts'),
                'office_phone'=> I('post.office_phone'),
                'mobile_num' => I('post.mobile_num'),
                'e_mail' => I('post.e_mail'),
                'remark' => I('post.remark'),
                'code' => I('post.code')           
            );
            //添加企业表
            $Cresult = $companyMod->data($post)->add();
            //新建企业默认手机端添加三个菜单和添加预警
            if($post['cm_type']==1){
                $menuMod = D('companymenus');
                $menuName = ["社区资讯", "账单缴费","公共报修"];
                for($m=0;$m<3;$m++){
                    $menus[$m] = array(
                        'cm_id' => $Cresult,
                        'menu_id' => $m+1,
                        'ord_id' => $m,
                        'icon_id' => $m+18,
                        'nomen' => $menuName[$m]
                    );
                }
                $menuMod->addAll($menus);
                //添加预警
                for($w=0;$w<3;$w++){
                    $warning[$w] = array(
                        'cm_id' => $Cresult,
                        'type' => $w+1,
                        'create_time' => date('Y-m-d H:i:s')
                    );
                }
                $warnMod = D('Warning');
                $warnMod->addAll($warning);
            }

            //添加分组中间表
            $data =array(
                'group_id' => I('post.group_id'),
                'user_id' => $this->userID,
                'cm_id' => $Cresult
            );
            $Grestult = $groupTempMod->data($data)->add();
            //管理员ID
            $admin = $companyMod->getAdminIdToCompId($Cresult);
            //添加角色中间表（本人新建企业 就给本人创建这个企业的管理角色）
            $powe = array(
                'user_id' => $this->userID,
                'cm_id' => $Cresult,
                'role_id' => $admin
            );
            $Uresult = $usertemp->add($powe);
            //创建工作站时，为工作站添加图文分类(有一个叫办事的特殊分类）
            if($post['cm_type']==3){
                $cateMod = D('category');
                //15个自定义分类
                for($i=0;$i<15;$i++){
                    $category[$i] = array(
                        'name' => '自定义分类'.$i,
                        'type' => 1,
                        'cm_id' => $Cresult,
                        'sequence' => $i
                    );
                }
                //办事 的特殊分类
                $category[15] = array(
                    'name' => '办事',
                    'type' => 7,
                    'cm_id' => $Cresult,
                    'sequence' => null
                );
                $cateMod->addAll($category);
            }

            //创建物业公司时，生成固定的32个费项
            if ($post['cm_type'] == 1) {
                $chargeModel = D('Charge');
                for ($i = 0; $i < 32; $i++) {
                    $charges[$i] = array(
                        'number' => $i+1,
                        'cm_id' => $Cresult,
                        'create_time' => date('Y-m-d H:i:s'),
                    ); 
                }
                $Fresult = $chargeModel->addCharges($charges);
                if(!$Fresult){
                    $model->rollback();
                    $this->error('保存失败',U('Company/addcompany'));
                }
            }
           if($Cresult && $Grestult && $Uresult){
                 $model->commit();
                 $this->success('保存成功',U('Company/index'));
           }else{        
                 $model->rollback();
                 $this->error('保存失败',U('Company/addcompany'));
           }
        }  

    }

    /***********************************************************************************************************************************************************************************************/
    public function editcompany(){                            //编缉企业页面
        
        $companyID  = I('get.companyid');
        $companyMod = D('company');
        if($companyID){     
            $details = $companyMod->selectCompanyDetail($companyID);
            $groupid = $details['cm_type'];
            
            $grotempMod = D('grouptemp');
            $groupID    = $grotempMod->selectgroupID($companyID,$this->userID);
            $allGroup   = $companyMod->selectGroupForAdd($this->userID); 
    
            for($i=0;$i<count($allGroup);$i++){
                if($allGroup[$i]['id']==$groupID['group_id']){
                    $allGroup[$i]['sele'] = 'selected';
                }else{
                    $allGroup[$i]['sele'] = false;
                }
            }

            $this->assign('details',$details);
            $this->assign('group',$allGroup);
            //dump($allGroup);exit;
            $this->display();
        }else{
            $model        = new Model();
            $groupTempMod = D('grouptemp');
            $groupMod     = D('group');
            $model->startTrans();                                     //事务处理
            
            $post['id']          = I('post.companyid');
            $post['name']        = I('post.name');
            $post['modify_time'] = date('Y-m-d H:i:s');
            $post['description'] = I('post.description');
            $post['contacts']    = I('post.contacts');
            $post['office_phone']= I('post.office_phone');
            $post['mobile_num']  = I('post.mobile_num');
            $post['e_mail']      = I('post.e_mail');
            $post['remark']      = I('post.remark');
             
            $data['group_id']    = I('post.group_id');
            $data['user_id']     = $this->userID;
            $data['cm_id']       = I('post.companyid');
            $gResult = $groupTempMod->where(array("cm_id"=>$data['cm_id'],"user_id"=>$data['user_id']))->save($data); //更改分组      
            $edit_comp = $companyMod->data($post)->save();
            $gResult = $gResult===false?false:true;
            if($edit_comp && $gResult){
                $model->commit();
                $this->success('编辑成功',U('Company/index'));
            }else{
                $model->rollback();
                $this->error('编辑失败',U('Company/editcompany?companyid='.$post['id']));
            }
        }
  
        
    }
    public function invitepeople(){                            //邀请人
         header("Content-type: text/html; charset=utf-8");
        $companyMod   = D('company');
        $roleMod = D('role');
        if(empty($_POST)){
            $companyIds = '';
            $allCompanyAndRoleId = $companyMod->selectCompanyRole($this->userID);//查询出所有企,角色id，
            $allRoleName = $roleMod->allRole(2);
            foreach($allRoleName as $name){
                $role[$name['id']] = $name['name'];          
            }
            foreach($allCompanyAndRoleId as $var){
                $companyIds .= $var['cid'].',';
                $in_Companp[$var['cid']][$var['rid']] = $role[$var['rid']];     
            }
            
            $companyIds = rtrim($companyIds,',');
            $allCompany = $companyMod->selectCompanyInfo($companyIds);
            $i = 0;
            foreach($allCompany as $company){
                $inCompanpRole[$i]['cid'] = $company['id'];
                $inCompanpRole[$i]['cname'] = $company['name'];
                //管理员ID
                $admin = $companyMod->getAdminIdToCompId($company['id']);
                foreach($in_Companp as $cid=>$rid){
                    if($company['id']==$cid){
                        $inCompanpRole[$i]['role'] = $rid ;
                    }
                } 
                if(!array_key_exists($admin,$inCompanpRole[$i]['role'])){
                    unset($inCompanpRole[$i]);
            }
            }
            $this->assign('comp_role',$comp_role);
            $this->display();
        }else{
            //加载短信平台插件
            vendor("TemplateSMS.CCPRestSmsSDK");
            $SendTemplateSMS = new CCPRestSmsSDK(ACCOUNT_SID, AUTH_TOKEN, SMS_CHUYUN_APPID);
            $userMod     = D('user');
            $userTempMod = D('usertemp');
            $groupTempMod= D('grouptemp');
            $groupMod    = D('group');
            $Model       = new Model();
            foreach($_POST as $c=>$n){
                if($c=='__hash__'){continue;}
                $position = strpos($c,'_');
                $key = substr($c,$position+1,1);
                $val = substr($c,0,$position);
                $useInfo[$key][$val] = $n;
            }
            foreach($useInfo as $val){
                if($val['code']==''){
                    continue;
                }
                //初始化角色和公司
                $roleid    = empty($val["role"])?null:$val["role"];
                $companyid = empty($val["comp"])?null:$val["comp"];
                //判断手机号是否存在
                $is_exist = $userMod->selectUserInfo($val['code']);
                //如果用户存在，没选择公司
                if($is_exist && !$companyid){
                    continue;
                }
                //判断是否已在邀请公司中
                $is_company = $userMod->userInCompany($val['code'], $companyid);
                //如果用户存在此公司
                if($is_exist && $is_company){
                    continue;
                }
                //如果用户存在但还不属于此公司就添加到公司中
                if($is_exist && !$is_company){
                    //查出用户默认分组
                    $defaultGroup = $groupMod->selectDefaultgid($is_exist['id']);
                    $group = array(
                        'user_id' => $is_exist['id'],
                        'cm_id' => $companyid,
                        'group_id' => $defaultGroup['id']
                    );
                    $groupTempMod->add($group);
                    //如果有分配角色
                    if($companyid && $roleid) {
                        $allotOrole = array(
                            'cm_id' => $companyid,
                            'role_id' => $roleid,
                            'user_id' => $is_exist['id']
                        );
                        $userTempMod->add($allotOrole);

                    }
                    //更新邀请人字段
                    $userMod->updateInviter($is_exist['id'], $this->userID);
                    continue;
                }
                $Model->startTrans();
                //随机密码
                $pass = (random_password(6,'123456789'));
                //用户信息
                $userData = array(
                    'code'=>$val['code'],
                    'name'=>$val['name'],
                    'dyn_password'=>MD5($pass),
                    'create_time'=>date('Y-m-d H:i:s'),
                );
                //写入数据库
                $uid = $userMod->add($userData);
                //更新邀请人字段
                $userMod->updateInviter($uid, $this->userID);
                //写入两条系统自带分组      
                $groData[] = array(
                    'type'=>1,
                    'title'=>'默认分组',
                    'description'=>'系统自带',
                    'create_time'=>date('Y-m-d H:i:s'),
                    'user_id'=>$uid,
                    'modify_time'=>date('Y-m-d H:i:s')                    
                );
                $groData[] = array(
                    'type'=>3,
                    'title'=>'停止服务',
                    'description'=>'系统自带',
                    'create_time'=>date('Y-m-d H:i:s'),
                    'user_id'=>$uid,
                    'modify_time'=>date('Y-m-d H:i:s')                   
                );              
                $def_group = $groupMod->addAll($groData); 
                //给新增用户一个普通用户角色
                $record[] = array(
                    'cm_id'=>null,
                    'role_id'=>DEFAULT_USER,
                    'user_id'=>$uid                   
                );
                //给新增用户分配公司角色
                if($companyid!=null&&$roleid!=null){
                    $record[] = array(
                        'cm_id'=>$companyid,
                        'role_id'=>$roleid,
                        'user_id'=>$uid                      
                    );
                    //新增的用户分组中间表
                    $Gdata = array(
                        'user_id' => $uid,
                        'cm_id' => $companyid,
                        'group_id' => $def_group
                    );
                    $groupTempMod->add($Gdata);
                }
                $role_temp = $userTempMod->addAll($record);                                
                if($uid && $role_temp && $def_group){
                    $msgStatus = sendMsg($SendTemplateSMS,$val['code'],array($pass),$this->userID);
                    if($msgStatus){
                        $Model->commit();
                    }else{
                        $Model->rollback();
                        $msgError[] = $val['code'];
                    }
                }else{
                    $Model->rollback();
                    //保存添加失败手机用户
                    $userError[] = $val['code'];      
                }   
            }   
    
           if(is_array($userError) || is_array($msgError)){
               
                $userNO = implode(',',$userError);
                $msgNO = implode(',',$msgError);
                $this->error("邀请失败人：{$userNO}<br />发送信息失败的人：{$msgNO}");
            }else{
                $this->success("邀请成功",U('company/invitelist'));
            }
            
          }   
    }
    
    public function invitelist(){                            //邀请人列表
        header("Content-type: text/html; charset=utf-8");
        $userMod     = D("user");
        //查询出属于我的邀请人ID
        $inviterListId = $userMod->belongToMe($this->userID);
        $inviterStr = '';
        foreach($inviterListId as $id){
            $inviterStr .= $id['id'].',';
        }
        $inviterStr = rtrim($inviterStr, ',');
        //查询被邀请的人员信息
        $inviterInfo  = $userMod->selectInvite($inviterStr);

        $this->assign('info',$inviterInfo);
        $this->display();
        
        
        
    }

    public function checkcode(){
        $code = I('post.code');
        echo D('invite_code')->selectNumberOfCode($code);
    }
    /*
     * 
     */
    public function getFaultTimeout(){
        set_time_limit(0);
        header("Content-Type: text/event-stream\n\n;");

        $faultModel=M('Faulttimeout');
        $data=$faultModel->where('type=2')->count();
        var_dump($data);
        echo 'data:第一条数据'."\n";
        echo "data:$data"."\n";
        //$this->display();
        
    }
}
