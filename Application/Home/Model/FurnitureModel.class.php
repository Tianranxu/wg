<?php
/*************************************************
 * 文件名：FurnitureModel.class.php
 * 功能：     配套设置模型
 * 日期：     2016.01.19
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class FurnitureModel extends Model
{

    protected $tableName = 'furniture';

    /**
     * 获取配套设施列表
     * @return mixed
     */
    public function getFurnitureLists()
    {
        $result = $this->where(['status' => 1])->select();
        return $result;
    }
}