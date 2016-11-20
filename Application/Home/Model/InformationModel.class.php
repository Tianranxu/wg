<?php
/*************************************************
 * 文件名：InformationModel.class.php
 * 功能：    资讯排版模型
 * 日期：     2015.9.1
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

class InformationModel extends WeixinModel
{
    protected $trueTableName = 'fx_sys_category';
    
    /**查询企业所有资讯分类
     * @param int $compid         企业id
     */
    public function selectCategory($compid){
        $field = 'sc.id,sc.name,sc.type,sc.cm_id,sc.status,sc.icon_id,sc.sequence,si.url_address as url,si.name as alt';
        $where = "cm_id=%d AND sc.icon_id=si.id AND sc.type=1";
        $table = array('fx_sys_category'=>'sc','fx_sys_icon'=>'si');
        $result = $this->table($table)->field($field)->where($where,$compid)->order('status desc,sequence asc')->select();
        return $result;
        
    }
    /**查询企业所有可用资讯分类
     * @param int $compid         企业id
     */
    public function selectUsableCategory($compid){
        $field = 'sc.id,sc.name,sc.type,sc.cm_id,sc.status,sc.icon_id,sc.sequence,si.url_address as url,si.name as alt';
        $where = "cm_id=%d AND sc.icon_id=si.id AND sc.type=1 AND sc.status=1";
        $table = array('fx_sys_category'=>'sc','fx_sys_icon'=>'si');
        $result = $this->table($table)->field($field)->where($where,$compid)->order('status desc,sequence asc')->limit('0,10')->select();
        return $result;
    
    }
    /**给分类写入排序号
     * @param int $orderArr         排序id和序号数组
     * @param string $order            要排序的id
     */
    public function sequence($orderArr, $order){
        $sql_1 = 'UPDATE fx_sys_category SET sequence = CASE id';
        $sql_2 = '';
        $sql_3 = ' END WHERE id IN (';
        foreach($orderArr as $key=>$val){
            $sql_2 .= " WHEN {$val} THEN {$key}";
        }
        $sql = $sql_1.$sql_2.$sql_3.$order.')';
        $result = $this->execute($sql);
        $result = $result===0?true:$result;
        return $result;
        
    }
    /**更新分类图标
     * @param int $iconArr         图标数组
     */
    public function updateIcon($iconArr){
        $sql_1 = 'UPDATE fx_sys_category SET icon_id = CASE id';
        $sql_2 = '';
        $ids = '';
        $sql_3 = ' END WHERE id IN (';
        foreach($iconArr as $key=>$val){
            $sql_2 .= " WHEN {$key} THEN {$val}";
            $ids .= $key.',';
        }
        $ids = rtrim($ids, ',');
        $sql = $sql_1.$sql_2.$sql_3.$ids.')';
        $result = $this->execute($sql);
        $result = $result===0?true:$result;
        return $result;

    }
    /*分类更新状态
     * @param int $ids         禁用分类id
     * @param int $order       正常分类id
     */
    public function condition($ids, $order){
        $this->startTrans();
        $map = array(
            'id' => array('in', $order),
        );
        $normal = $this->where($map)->setField('status',1);
        $map = array(
            'id' => array('in', $ids),
        );
        $forbidden  = $this->where($map)->setField('status',-1);
        
        $normal = $normal==0?true:$normal;
        $forbidden = $forbidden==0?true:$forbidden;
        if($normal && $forbidden){
            $this->commit();
            return true;
        }else{
            $this->rollback();
            return false;
        }
    
    }
    
    
    
    
    
    
    
    
    
}