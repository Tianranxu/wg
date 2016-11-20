<?php
/*************************************************
 * 文件名：RepairgroupModel.class.php
 * 功能：     维修分组模型
 * 日期：     2015.10.14
 * 作者：     DA fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class RepairgroupModel extends Model{

    protected $trueTableName = 'fx_repair_group';
    
    //默认分组和待审核分组ID
    public function defaultANDexamine($type=1){
        $result = $this->where('type=%d',$type)->find();
        return $result;
    } 
    //查出企业所有维修分组
    public function get_repair_group($compid){
        $result = $this->where('cm_id=%d', $compid)->select();
        return $result;
    }























}