<?php
/*************************************************
 * 文件名：TemplateModel.class.php
 * 功能：     微信模板模型
 * 日期：     2015.10.23
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;
use Think\Model;

class TemplateModel extends Model{

    protected $trueTableName = 'fx_sys_wechat_templ';
    
    /**
     * 查询出模板
     * @param int $tid       模板id
     */
    public function selectTempl($tid=null){        
        if($tid){
            $where = "id=%d AND status=1";
            $result = $this->where($where,$tid)->find();
        }else{
            $where = "status=1";
            $result = $this->where($where)->select();
        }
        return $result;
    }
    /*查询出企业模板
     * @param int $appid       企业appid
     */
    public function selectTemplByAppid($appid){
        $field = "wt.id as id,wt.name as tname,wt.style,cm.id as cid,cm.associate as wsid,cm.name as cname,cm.description as shortname,p.authorizer_info as pu_name";
        $where = "p.appid='%s' AND p.cm_id=cm.id AND cm.templet=wt.id AND p.isCancel=-1";
        $table = array('fx_sys_wechat_templ'=>'wt','fx_publicno'=>'p','fx_comp_manage'=>'cm');
        $result = $this->table($table)->field($field)->where($where,$appid)->find();
        return $result;
    }
    /*查询出企业模板
     * @param int $compid       企业id
     */
    public function selectTemplByCompid($compid){
        $field = "wt.id as id,wt.name as tname,wt.style,cm.id as cid,cm.name as cname,cm.description as shortname";
        $where = "cm.id='%s' AND cm.templet=wt.id";
        $table = array('fx_sys_wechat_templ'=>'wt','fx_comp_manage'=>'cm');
        $result = $this->table($table)->field($field)->where($where,$compid)->find();
        return $result;
    }
}