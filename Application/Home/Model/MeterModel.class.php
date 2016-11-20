<?php
/***
 * 文件名：MeterModel.class.php
 * 功能：仪表模型
 * 作者：XU
 * 日期：2015-08-17
 * 版权：Copyright 2015 @ 风馨科技 All Rights Reserved
 */

namespace Home\Model;
use Think\Model;

class MeterModel extends Model{
    /**
     * 根据搜索条件获取仪表读数的信息，每次显示10条，按修改时间排序
     */
    public function getAllMeter($param,$page=''){
        $table     = "fx_meter_degree";
        if ($param['year']){//若有接收到年份信息
            $where['year'] = $param['year'];
        }
        if ($param['month']){//若有接收到月份信息
            $where['month'] = $param['month'];
        }
        if ($param['ms_id']){//若有接收到仪表信息
            $where['ms_id'] = $param['ms_id'];
        }
        //若有将房间号加入搜索条件中
        if($param['house']){
            if ($param['bm_id']) {
                $whereHouse = array(
                    'hm.number' => ['like', '%'.$param['house'].'%'],
                    'hm.bm_id' => $param['bm_id']
                );
                $house  = $this->table(array('fx_house_manage'=>'hm'))->field('id')->where($whereHouse)->find();
                $where['hm_id']  = $house['id'];    
            }else{
                //若没有选择楼栋id
                $whereHouse = [
                    'hm.number' => ['like', '%'.$param['house'].'%'],
                    'hm.bm_id = bm.id',
                    'bm.cc_id = cc.id',
                    'cc.id' => $param['cm_id'],
                ];
                $table_temp = [
                    'fx_community_comp' => 'cc',
                    'fx_house_manage' => 'hm',
                    'fx_building_manage' => 'bm',
                ];
                $field = ['hm.id' => 'id'];
                $room_ids = $this->table($table_temp)->where($whereHouse)->field($field)->select();
                foreach ($room_ids as $key => $roomid) {
                    $ids[] = $roomid['id'];    
                }
                $where['hm_id'] = array('IN',$ids);
            }
        }elseif ($param['bm_id']){
            //若只有楼宇信息加入搜索条件中
            $whereRoom = array(
                'bm_id' => $param['bm_id']
            );
            $room_ids = $this->table('fx_house_manage')->where($whereRoom)->getField('id',true);
            $room_ids = implode(',',$room_ids);
            $where['hm_id'] = array('IN',$room_ids);
        }elseif ($param['cm_id']){
            //若只有楼盘信息加入搜索条件中
            $whereBui = array('cc_id' => $param['cm_id']);
            $building_ids = $this->table('fx_building_manage')->where($whereBui)->getField('id',true);
            $building_ids = implode(',',$building_ids);
            $whereRoom = array('bm_id' => array('IN',$building_ids));
            $room_ids = $this->table('fx_house_manage')->where($whereRoom)->getField('id',true);
            $room_ids = implode(',',$room_ids);
           
            $where['hm_id'] = array('IN',$room_ids);
        }
        //计算信息的页数
        $count      = $this->table($table)->where($where)->count();
        $total      = ceil(($count/10)); 
        $result     = $this->table($table)->where($where)->limit($page*10,10)->order(array('modify_time'=>'desc', 'id' => 'asc'))->select();
        $data['data']   = $result;
        $data['total']  = $total;
        return $data;
    }
    /**
     * 根据楼盘id获取楼盘名称和公司id
     */
     public function getCommunity($cid){
        $where = array(
            'cc.id' => $cid,
            'cc.status' => 1
        );
        $result = $this->table(array('fx_community_comp'=>'cc'))->field(array('cc.name','cc.cm_id'))->where($where)->find();
        return $result;
     }
     
     /**
      * 根据房间id获取房间名称和楼栋名称
      */
     public function getProperty($hid){
         $whereHouse = array('hm.id' => $hid);
         $house     = $this->table(array('fx_house_manage'=>'hm'))->field(array('number','bm_id'))->where($whereHouse)->find();
         $whereBui = array('bm.id' => $house['bm_id']);
         $building  = $this->table(array('fx_building_manage'=>'bm'))->field('name')->where($whereBui)->find();
         $result['number'] = $house['number'];
         $result['name']   = $building['name'];
         return $result;
     }
     
     /**
      * 根据读数表的仪表id获取设置的仪表名称和单位
      */
     public function getMeterSet($mid){
         $where = array('ms.id' => $mid);
         $result = $this->table(array('fx_meter_setting_category'=>'ms'))->where($where)->find();
         return $result;
     }
     
     /**
      * 获取楼盘下所有的楼栋信息
      */
     public function getAllBuilding($cm_id){
         $where = array('bm.cc_id' => $cm_id);
         $result = $this->table(array('fx_building_manage'=>'bm'))->field(array('id','name'))->where($where)->select();
         return $result;
     }
     
     /**
      * 获取所有设置好状态（status）正常的仪表信息
      */
     public function getAllSetMeter(){
         $where = array('status' => 1);
         $result = $this->table('fx_meter_setting_category')->field(array('id','name','unit','description','status'))->where($where)->select();
         return $result;
     }
     
     /***
      * 获取所有的仪表信息（正常和禁用的）,按修改时间排序
      */
     public function getSetMeter($page='',$param=''){
         if($param){
             if($param['meter']){
                 $where['ms.name'] = array('LIKE',"%{$param['meter']}%");
             }
             if($param['status']){
                 $where['ms.status'] = $param['status'];
             }
             $result = $this->table(array('fx_meter_setting_category'=>'ms'))->where($where)->limit($page*10,10)->order('modify_time desc')->select();
             $count  = $this->table(array('fx_meter_setting_category'=>'ms'))->where($where)->count();
             $total  = ceil(($count/10));        //计算消息的页面数
             $data['data']  = $result;
             $data['page']  = $total;
         }else{
             $count  = $this->table('fx_meter_setting_category')->count();
             $total  = ceil(($count/10));        //计算消息的页面数
             $result = $this->table(array('fx_meter_setting_category'=>'ms'))->limit($page*10,10)->order('modify_time desc')->select();
             $data['data']  = $result;
             $data['page']  = $total;
         }
         return $data;
     }
       
     /**
      * 查看是否有对应的房间号，若有则添加数据并返回对应值，若没有，返回相应的值
      */
     public function checkAndAdd($param,$house){
         $whereRoom = array(
            'hm.bm_id' => $param['bm_id'],
            'hm.number' => $house
         );
         $room = $this->table(array('fx_house_manage'=>'hm'))->where($whereRoom)->find();
         $whereRepeat = array(
            'hm_id' => $room['id'],
            'year' => $param['year'],
            'month' => $param['month'],
            'ms_id' => $param['ms_id']
         );
         $repeat = $this->table('fx_meter_degree')->field('id')->where($whereRepeat)->find();
         $MD    = M('meter_degree','fx_');
         if($repeat){
            //若有重复数据，则覆盖前面的数据
            $data['id'] = $repeat['id'];
            $data['degree'] = $param['degree'];
            $data['modify_time']  = date("Y-m-d H:i:s",time()); 
            if ($MD->save($data)) {
                return 1;
            }else{
                return 2;
            }
         }
         if($room){
             //若有对应的房间号
             $data['hm_id']        = $room['id'];         //添加房间信息
             $data['year']         = $param['year'];
             $data['month']        = $param['month'];
             $data['ms_id']        = $param['ms_id'];
             $data['degree']       = $param['degree'];
             $data['create_time']  = date("Y-m-d H:i:s",time()); //添加时间
             $data['modify_time']  = date("Y-m-d H:i:s",time()); //由于需求同时添加修改时间
             if($MD->add($data)){
                 //添加成功
                 return 1;
             }else{
                 //添加失败
                 return 2;
             }
         }else{
             //若无对应房间号
             return -1;
         }
     }
     /**
      * 查看仪表名称是否重复，并添加仪表数据
      */
     public function checkAndAddMeter($param){
         $where = array(
            'ms.name' => $param['name'],
            'ms.status' => 1
          );
         $check     = $this->table(array('fx_meter_setting_category'=>'ms'))->field('ms.id')->where($where)->find();
         if($check){
             //若仪表名称重复
             return 2;
         }else{
             //初始化模型
             $MS     = M('meter_setting_category','fx_');
             if($MS->add($param)){
                 return 1;
             }else{
                 return 3;
             }
         }
     }
     
     /**
      * 查看仪表名称是否重复，并修改仪表数据
      */
     public function checkAndEditMeter($param){
         $where = array(
            'ms.name' => $param['name'],
            'ms.status' => 1
         );
         $check     = $this->table(array('fx_meter_setting_category'=>'ms'))->field('ms.id')->where($where)->count();
         if($check>=2){
             //若仪表名称重复
             return 2;
         }else{
             //初始化模型
             $MS     = M('meter_setting_category','fx_');
             if($MS->save($param)){
                 return 1;
             }else{
                 return 3;
             }
         }
     }
}