<?php
/*************************************************
 * 文件名：ServeModel.class.php
 * 功能：    微服务模型
 * 日期：     2015.9.1
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

class ServeModel extends WeixinModel
{
    protected $trueTableName = 'fx_comp_serve';


    /*存入修改数据
     * @param int $compid         企业id
     * @param   $ord_arr                  排序数组
     *
     */
    public function availableServeOrd($compid, $ord_arr)
    {
        $this->startTrans();
        $del = $this->where("cm_id=%d",$compid)->delete();//删除先前存取的排序，以防没添加 的服务存在
        $del = $del==0?true:$del;
        $add = $this->addAll($ord_arr);
        if($del && $add){
            $this->commit();
            return true;
        }else{
            $this->rollback();
            return false;
        }

    }
    //企业选择的微服务
    public function selectIsServe($compid){
        $field = 'wm.id,wm.icon_id as sys_icon,cs.icon_id as user_icon,wm.title,si.url_address as image_url,wm.link_url,cs.ord_id,wm.type';
        $table = array('fx_comp_serve'=>'cs','fx_sys_icon'=>'si','fx_wechat_menus'=>'wm');
        $where = "cs.cm_id=%d AND si.type=1 AND cs.icon_id=si.id AND cs.serve_id=wm.id AND wm.type=3";
        $result= $this->table($table)->field($field)->where($where,$compid)->order('cs.ord_id asc')->select();
        return $result;
    }
    
    
}