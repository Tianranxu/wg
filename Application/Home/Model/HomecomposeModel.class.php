<?php
/*************************************************
 * 文件名：HomecomposeModel.class.php
 * 功能：    首页排版模型
 * 日期：     2015.9.1
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

class HomecomposeModel extends WeixinModel
{    
    protected $trueTableName = 'fx_wechat_menus';
    

    public function selectIsMenus($compid){
        $field = 'wm.id,wm.icon_id as sys_icon,cm.icon_id as user_icon,wm.title,si.url_address as image_url,wm.link_url,cm.ord_id,wm.type,cm.nomen';
        $table = array('fx_wechat_menus'=>'wm','fx_sys_icon'=>'si','fx_comp_menus'=>'cm');
        $where = "cm.cm_id=%d AND si.type=1 AND cm.icon_id=si.id AND cm.menu_id=wm.id";
        $result= $this->table($table)->field($field)->where($where,$compid)->order('cm.ord_id asc')->select();
        return $result;
    }

    /**查询企业所有微网首页图标
     * @param int $compid         企业id
     */
    public function selectMenus($compid){
        $field = 'wm.id,wm.icon_id,wm.title,si.url_address as image_url,wm.link_url,wm.type';
        $table = array('fx_wechat_menus'=>'wm','fx_sys_icon'=>'si');
        $where = "si.type=1 AND wm.icon_id=si.id ";
        $result= $this->table($table)->field($field)->where($where)->select();
        return $result;
    }
    /**查询企业所有微网首页图标重命名
     * @param int $mid         菜单id
     * @param int $appid       企业Appid
     */
    public function selectMenuRename($mid, $appid){
        $field = 'cm.nomen';
        $table = array('fx_publicno'=>'pn','fx_comp_menus'=>'cm');
        $where = "pn.appid='%s' AND cm.menu_id =%d AND pn.cm_id=cm.cm_id AND pn.isCancel=-1";
        $result= $this->table($table)->field($field)->where($where,$appid,$mid)->find();
        return $result;
    }



    public function selectMenusByCompId($compid){
        $field = 'wm.id,cm.cm_id,cm.icon_id,wm.title,wm.image,si.url_address as image_url,wm.link_url,wm.type,cm.nomen';
        $table = array('fx_wechat_menus'=>'wm','fx_sys_icon'=>'si','fx_comp_menus'=>'cm');
        $where = "si.type=1 AND cm.icon_id=si.id AND cm.cm_id =%d AND cm.menu_id = wm.id";
        $result= $this->table($table)->field($field)->where($where,$compid)->order('cm.ord_id')->select();
        return $result;
    }

    public function selectMenusByAppId($appid){
        $field = 'wm.id,cm.cm_id,cm.icon_id,wm.title,wm.image,si.url_address as image_url,wm.link_url,wm.type,cm.nomen';
        $table = array('fx_wechat_menus'=>'wm','fx_sys_icon'=>'si','fx_comp_menus'=>'cm', 'fx_publicno'=>'pn');
        $where = "si.type=1 AND cm.icon_id=si.id AND cm.cm_id = pn.cm_id AND cm.menu_id = wm.id AND pn.appid = '%s' AND pn.isCancel=-1";
        $result= $this->table($table)->field($field)->where($where,$appid)->order('wm.type,cm.ord_id')->select();
        return $result;
    }

    public function selectSlideByAppId($appid){
        $field = 'sl.cm_id, sl.url, sl.order';
        $table = array('fx_slide'=>'sl', 'fx_publicno'=>'pn');
        $where = "sl.cm_id = pn.cm_id";
        $map = array('pn.appid'=>$appid, 'pn.isCancel'=>-1);
        $result= $this->table($table)->field($field)->where($map)->where($where)->order('sl.order')->select();
        return $result;
    }
    /**查询企业所有微服务图标
  
     */

    public function selectServe(){
        $field = 'wm.id,wm.icon_id,wm.title,si.url_address as image_url,wm.link_url,wm.type';
        $table = array('fx_wechat_menus'=>'wm','fx_sys_icon'=>'si');
        $where = "si.type=1 AND wm.type=3 AND wm.icon_id=si.id ";
        $result= $this->table($table)->field($field)->where($where)->select();
        return $result;
    }
    
    /*appid查出出企业ID
     * @param int $appid       企业Appid
     */
    public function selectCompId($appid){
        $table = array('fx_publicno'=>'fn');
        $map = "fn.appid=%d AND fn.isCancel=-1";
        $result= $this->table($table)
                      ->where($map, $appid)
                      ->find();
        return $result;
    }

    public function getWorkstationId($appid){
        $table = array('fx_publicno'=>'pn', 'fx_comp_manage'=>'cm');
        $map = array('pn.appid'=>$appid, 'pn.isCancel'=>-1);
        $result= $this->field('associate')
                      ->table($table)
                      ->where($map)
                      ->where('cm.id = pn.cm_id')
                      ->find();
        return $result['associate'];
    }
    
}
