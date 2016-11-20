<?php
/*************************************************
 * 文件名：RoompicModel.class.php
 * 功能：     房源附件管理模型
 * 日期：     2016.01.21
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class RoompicModel extends Model
{

    protected $tableName = 'room_picture';

    /**
     * 添加房源附件
     * @param array $datas 附件数据
     * @return bool
     */
    public function addPictures(array $datas)
    {
        $result = $this->addAll($datas);
        if (!$result) return false;
        return true;
    }

    /**
     * 获取房源上传的附件
     * @param int $rsId 房源ID
     * @return mixed
     */
    public function getPictureByRoomId($rsId)
    {
        $lists = $this->where(['rs_id' => $rsId])->select();
        return $lists;
    }
}