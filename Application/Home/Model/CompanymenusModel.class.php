<?php
/*************************************************
 * 文件名：CompanymenusModel.class.php
 * 功能：    菜单模型
 * 日期：     2015.9.1
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

class CompanymenusModel extends WeixinModel
{
    protected $trueTableName = 'fx_comp_menus';
    
    
    /*把更新的数据写入到数据库
     * @param int $compid         企业id
     * @param   $ord_arr          系统菜单排序数组
     */
    public function availableMenusOrd($compid, $ord_arr)
    {
        $this->startTrans();
        $del = $this->where("cm_id=%d",$compid)->delete();//删除先前存取的排序，以防没添加 的菜单存在
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
    //解绑工作站后删除表中工作站图标
    public function delWorkMenus($compid, $type=''){
        if($type==''){
            $where = 'cm_id=%d';
        }else{
            $where = 'cm_id=%d and type=%d';
        }         
        $result = $this->where($where, $compid, $type)->delete();
        return $result;
    }
    
    
    
    
}