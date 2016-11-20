<?php
/*************************************************
 * 文件名：PaytypeModel.class.php
 * 功能：     付款方式模型
 * 日期：     2016.1.6
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class PaytypeModel extends Model{

    protected $tableName='pay_type_temp';

    /**
     * 添加付款方式
     * @param array $datas
     * @return bool
     */
    public function addPayType(array $datas)
    {
        if(isset($datas[0])){
            $result=$this->addAll($datas);
            if(!$result) return false;return true;
        }
        $result=$this->add($datas);
        if(!$result) return false;return true;
    }
}