<?php
/*************************************************
 * 文件名：WechatuserController.class.php
 * 功能：     微信用户分组控制器
 * 日期：     2015.9.1
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
use Think\Controller;
class WechatuserController extends AccessController
{
    protected $accessToken;
    protected $compid;
    public function _initialize() {
        parent::_initialize();
        $this->compid = I('get.compid')?I('get.compid'):I('post.compid');
        $weixinMod = D('Weixin');
        $this->accessToken = $weixinMod->get_authorizer_access_token($this->compid);//正式环境 //测试环境;//test_access_token()
    }
    public function index()
    {   header("Content-type: text/html; charset=utf-8");
        $gid = I('get.groupid');
        $gid = $gid!=null?$gid:-1;
        $wechatModel = D('wechatuser');
        //$wechatModel->testWrite();//写入测试数据用
        //用户分组列表;
        $groupList = $wechatModel->getGroupList($this->accessToken)->groups;
        //总关注人数
        $total = 0; 
        //归类好的分组数组
        $classify = array();
        //分类 分组
        foreach($groupList as &$group){ 
            //把未分组改成默认组
            if($group->id==0){
                $group->name = '默认组';
            }
            $classify[$group->id] = $group;
            if($group->id != 1){
                $total += $group->count;
            }

        }
        //黑名单分组
        $black = $classify[1];
        //除黑名以外的其它分组
        $groups = $classify;
        unset($classify[1]);
        $normal = $classify;
        //分组排序
        $this->my_sort($normal);
 //开始拉取用户信息列表 
        if($gid==-1){
            //用户信息列表
            $allList = $wechatModel->readRedis($this->compid);
            //用户分组归类
        }else{           
            //此分组下用户信息列表
            $allList = $wechatModel->readRedis($this->compid, 0, $gid);
            foreach($groups as $group){
                if($group->id==$gid){
                    $curr_group = array(
                        'id' => $group->id,
                        'name' => $group->name
                    );
                }
            }
        }
        //所有用户信息
        $this->assign('list',$allList);
        //除黑名单以外的分组
        $this->assign('groups',$normal);
        //所有分组
        $this->assign('allGroup',$groupList);
        //黑名单分组
        $this->assign('blackGroup',$black);
        //所有用户总数（）
        $this->assign('total',$total);
        //当前页面所在的用户组
        $this->assign('gid',$gid);
        //当前页面所属组
        $this->assign('curr_group',$curr_group);
        $this->assign('compid',$this->compid);
        $this->display();
        
    }
    //加载更多
	function load_more(){
	    $page = I('get.page',0);
	    $dislodge = $page*10;
	    $gid = I('get.groupid','*');
	    $wechatModel = D('wechatuser');	     
	    //用户信息列表
	    $userInfo = $wechatModel->readRedis($this->compid, $dislodge, $gid);
	    //用户分组列表
	    $groups = $wechatModel->getGroupList($this->accessToken)->groups;
	    //把未分组改成默认组
	    foreach($groups as &$group){
	        if($group->id == 0){
	            $group->name = '默认组';
	        }
	        $classify[$group->id] = $group;
	    }
	    $userInfo['group'] = $classify;          
	    exit(json_encode ( $userInfo ) );
	}
	// 移动到某分组
    function moveTogroup(){
        $openid = I('post.openid');
        //新组ID
        $newGid = I('post.newGid');
        //旧组ID
        $oldGid = I('post.oldGid');
        $wechatModel = D('wechatuser');
        //移动微信端数据
        $result = $wechatModel->moveToGroup($this->accessToken, $this->compid, $openid, $newGid);
        if($result->errmsg == 'ok') {
            //redis
            $move = $wechatModel->moveRedis($this->compid, $oldGid, $newGid, $openid);
            $success = array(
                'errcode' => 0,
                'errmsg' => 'ok'
            );
            $fail = array(
                'errcode' => 4005,
                'errmsg' => 'fail'
            );
            $result = $move ? $success : $fail;
        }
        exit(json_encode($result));
    }
    //修改备注
    function modifyRemark(){  
        $openid = I('post.openid');
        $compid = I('post.compid');
        $remark = I('post.remark');
        $gid = I('post.gid');
        $wechatModel = D('wechatuser');
        //更新微信端
        $result = $wechatModel->setRemark($this->accessToken, $openid, $remark);
        $WXstatus = json_decode($result)->errmsg;
        if($WXstatus == 'ok'){
            //更新redis
            $wechatModel->setRemarkRedis($compid, $remark, $gid, $openid);
        }
        exit($result);
    }
    //创建分组
    function create(){
        $groupName = I('post.groupName');
        $wechatModel = D('wechatuser');
        //已存在的所有分组
        $groups = $wechatModel->getGroupList($this->accessToken)->groups;
        foreach($groups as $name){
            $group[] = $name->name;
        }
        if(in_array($groupName, $group)){
            $result = '{"errcode":-1,"errmsg":"exist"}';
            exit($result);
        }
        $result = $wechatModel->createGroup($this->accessToken, $groupName);
        $groupInfo = json_decode ( $result )->group;
        if(empty($groupInfo->id)){
            exit($result);
        }else{
            $result_ = '{"errcode":0,"errmsg":"ok"}';
            exit($result_);
        }
    }
    //分组重命名
    function editGroup(){
        $groupid = I('post.groupid');
        $groupName = I('post.groupName');
        $wechatModel = D('wechatuser');
        //已存在的所有分组
        $groups = $wechatModel->getGroupList($this->accessToken)->groups;
        foreach($groups as $name){
            $group[] = $name->name;
        }
        if(in_array($groupName, $group)){
            $result = '{"errcode":-1,"errmsg":"exist"}';
            exit($result);
        }
        $result = $wechatModel->modifyGroupName($this->accessToken, $groupid, $groupName);
        exit($result);
    }
    //删除分组
    function delGroup(){
        $groupid = I('post.groupid');
        $compid = I('post.compid');
        $wechatModel = D('wechatuser');
        //删除微信端分组
        $delete = $wechatModel->deleteGroup($this->accessToken, $groupid);
        if($delete=='{}'){
            //删除redis
            $redis = $wechatModel->delRedisGroup($compid, $groupid);
            $delete =  $redis?'{"errcode": 0, "errmsg": "ok"}':'{"errcode": -1, "errmsg": "fail"}';
        }
        exit($delete);
    }
    //数组排序
    protected  function my_sort($array){
        usort($array, function($a, $b){
            $sort_a = $a->id;
            $sort_b = $b->id;
            if($sort_a>$sort_b){
                return 1;
            }elseif($sort_a<$sort_b){
                return -1;
            }else{
                return 0;
            }
        });
    }

}