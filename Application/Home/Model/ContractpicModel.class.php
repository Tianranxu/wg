<?php
/*************************************************
 * 文件名：ContractpicModel.class.php
 * 功能：     合约附件管理模型
 * 日期：     2016.01.27
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class ContractpicModel extends Model{

    protected $tableName='contract_picture';

    /**
     * 添加合约附件
     * @param array $datas  附件数据
     * @return bool
     */
    public function addPictures(array $datas)
    {
        $result = $this->addAll($datas);
        if (!$result) return false;
        return true;
    }
}
