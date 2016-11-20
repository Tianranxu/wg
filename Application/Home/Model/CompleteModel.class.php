<?php
/*************************************************
 * 文件名：CompleteModel.class.php
* 功能：     已办事项模型
* 日期：     2015.7.23
* 作者：     fei
* 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
***********************************************/
namespace Home\Model;
use Think\Model;
class CompleteModel extends Model{
    protected $trueTableName = 'fx_completed_work';
    
    /**
     * 根据openid查出所有已办事项
     * @param int $openid       微信用户openid
     * @param int $cm_id        公司id
     */
    public function selectMatterByOpenid($openid, $cm_id){
        
        $result = $this->where('openid="%s" AND cm_id=%d AND status=1',$openid, $cm_id)
            ->order('create_time desc')
            ->select();
        return $result;
    }
    /*存储表单记录
     * @param array $data   表单数据
     */
    public function storeFormRecord($data){
        $result = $this->add($data);
        return $result;
    }
    /*查询表单数据总数
     */
    public function selectFormCount(){
        $result = $this->count();
        return $result;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
}
