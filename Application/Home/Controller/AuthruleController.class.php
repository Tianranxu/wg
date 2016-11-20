<?php
/***
 * 文件名：AuthruleController.class.php
 * 功能：权限操作控制器
 * 作者：XU
 * 日期：2015-07-24
 * 版权：Copyright ? 2014-2015 风馨科技 All Right Reserved
 */
namespace Home\Controller;
use Think\Controller;

class AuthruleController extends Controller{
    /**
     * 权限展示主页面
     */
    public function index(){
        $AL     = M('sys_auth_rule','fx_');
        $auth   = $AL->field(array('id','module','type','name','title','status'))->select();
        $this->assign('auth',$auth);
        $this->display();
    }
    
    /**
     * 权限添加页面
     */
    public function add(){
        
        $this->display();
    }
    public function doAdd(){
        //收集表单信息
        $auth['module']      = I('post.module');
        $auth['type']        = I('post.type');
        $auth['name']        = I('post.name');
        $auth['title']       = I('post.title');
        $auth['status']      = I('post.status');
        $auth['rule_cond']   = I('post.condition');
        //将表单信息添加到数据库中
        $AL     = M('sys_auth_rule','fx_');
        if($AL->add($auth)){
            $this->success("添加成功！",U('index'));
        }else{
            $this->error("添加失败！",U('index'));
        }
    }
    
    /**
     * 权限删除函数
     */
    public function delete(){
        $id     = I('get.id');
        $AL     = M('sys_auth_rule','fx_');
        if($AL->delete($id)){
            $this->success("删除成功！",U('index'));
        }else{
            $this->error("删除失败！",U('index'));
        }
    }
    
    /**
     * 权限修改页面
     */
    public function modify(){
        $where['id']     = I('get.id');
        $AL     = M('sys_auth_rule','fx_');
        $auth   = $AL->where($where)->find();
        $this->assign('auth',$auth);
        $this->display();
    }
    public function doModify(){
        //收集表单信息
        $auth['id']         = I('post.id');    
        $auth['module']     = I('post.module');
        $auth['type']       = I('post.type');
        $auth['name']       = I('post.name');
        $auth['title']      = I('post.title');
        $auth['status']     = I('post.status');
        $auth['rule_cond']  = I('post.  condition');
        
        //将修改的数据写入数据库
        $AL     = M('sys_auth_rule','fx_');
        if($AL->save($auth)){
            $this->success("修改成功！",U('index'));
        }else{
            $this->error("修改失败！",U('index'));
        }
    }
}