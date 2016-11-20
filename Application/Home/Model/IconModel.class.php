<?php
/*************************************************
 * 文件名：IconModel.class.php
 * 功能：    图标模型
 * 日期：     2015.9.1
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

class IconModel extends WeixinModel
{
    protected $trueTableName = 'fx_sys_icon';
    /**
     * 查询出所有系统图标
     * @param int $type       图标类型
     * @param int $iconid     图标id
     */
    
    public function selectIcon($type, $iconid=''){
       
        if(empty($iconid)){
            $result = $this->where("type=%d",1)->select();
        }else{
            $result = $this->where("id=%d",$iconid)->find();
        }
        return $result;
    }

}
    