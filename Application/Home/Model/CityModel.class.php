<?php
/*************************************************
 * 文件名：CityModel.class.php
 * 功能：     省市区管理模型
 * 日期：     2015.8.5
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;
use Think\Model;

class CityModel extends Model{
    protected $tableName ='city';
    
    /**
     * 查询所有省
     * @return array    数据数组
     */
    public function select_province_list(){
        $field='id,name,pid';
        $where=array('pid'=>0);
        $result=$this->field($field)->where($where)->select();
        return $result;
    }
    
    /**
     * 根据省ID（城市父级ID）查询城市列表
     * @param string $pid   城市父级ID
     * @return array            数据数组
     */
    public function find_city_list($pid){
        $field='id,name,pid';
        $where=array('pid'=>$pid);
        $result=$this->field($field)->where($where)->select();
        return $result;
    }
    
    /**
     * 根据城市ID（区父级ID）查询区列表
     * @param string $pid   区父级ID
     * @return array            数据数组
     */
    public function find_area_list($pid){
        $field='id,name,pid';
        $where=array('pid'=>$pid);
        $result=$this->field($field)->where($where)->select();
        return $result;
    }

    /**
     * 根据父级ID查看所属
     * @param int $pid
     * @return mixed
     */
    public function getParentByPid($pid)
    {
        $where=['pid'=>$pid];
        $result=$this->where($where)->find();
        return $result;
    }
    
    //根据ID查询名称
    public function get_city_name($id){
        $field='id,name,pid';
        $where=array('id'=>$id);
        $result=$this->field($field)->where($where)->find();
        return $result;
    }
}


