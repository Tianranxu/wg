<?php
/*************************************************
 * 文件名：TemplatemsgModel.class.php
 * 功能：     模板消息模型
 * 日期：     2016.02.24
 * 作者：     XU
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/

namespace Home\Model;
use Think\Model;

class TemplatemsgModel extends Model{
    protected $tableName = 'weixin_msg_template';

    /**
    * 检查物业号消息模板是否存在
    * @param $compid  公司id 
    * @param $short_id 模板短id
    */
    public function checkTemplate($compid, $short_id){
        return $this->where(['cm_id' => $compid, 'short_id' => $short_id])->find();
    }

    /**
    * 检查维修号消息模板是否存在
    * @param $short_id
    */
    public function checkRepairTemplate($short_id){
        return $this->where(['short_id' => $short_id, 'type' => 2])->find();
    }

    /**
    * 添加消息模板
    * @param array $data 模板表所需数据，包括cm_id,short_id,long_id
    */
    public function addTemplate($data){
        return $this->add($data);
    }
}