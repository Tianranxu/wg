<?php
/**
 * 文件名：CarModel.class.php
 * 功能：车辆管理模型
 * 作者：XU
 * 时间：2015-08-03
 * 版权：Copyright ? @2015 风馨科技 All Rights Reserved
 */

namespace Home\Model;
use Think\Model;

class CarModel extends Model{
    protected $tableName = 'car_manage';
    
      /**
       * 根据楼盘id获取楼盘名称
       */
     public function getCommunityName($cid){
        $where = array(
            'cc.id' => $cid,
            'cc.status' => 1
         );
        $result         = $this->table(array('fx_community_comp'=>'cc'))->where($where)->find();
        return $result;
     }
      
      /**
       * 根据企业id获取企业下所有的楼盘信息（按添加时间倒序排序）
       */
      public function getBuilding($cid){
          $where = array(
              'cc.cm_id' => $cid,
              'cc.status' => 1
           );
          $result       = $this->table(array('fx_community_comp'=>'cc'))->field(array('id','name'))->where($where)->order(array('modify_time'=>'desc','create_time'=>'desc'))->select();
          return $result;
      }
      
      /**
       * 根据车辆id获取所有的车辆信息
       */
      public function getCar($cid){
          $where = array(
              'cm.id' => $cid
            );
          $result       = $this->table(array('fx_car_manage'=>'cm'))->where($where)->find();
          return $result;
      }
      
      /**
       * 检测同一楼盘下的卡号是否重复
       */
      public function checkCardNumber($cardNum,$comm_id){
          $where = array(
              'cm.cc_id' => $comm_id,
              'cm.card_number' => $cardNum
           );
          $result       = $this->table(array('fx_car_manage'=>'cm'))->where($where)->find();
          if($result){
              return $result;
          }else{
              return false;
          }
      }
      
      /**
       * 根据楼盘id获取物业公司id
       */
      public function getCompanyId($comm_id){
          $where = array(
              'cc.id' => $comm_id
           );
          $result       = $this->table(array('fx_community_comp'=>'cc'))->field('cm_id')->where($where)->find();
          return $result;
      }
      
      /**
       * 根据楼盘id获取物业公司名称
       */
      public function getCompanyName($comm_id){
          $whereCom = array('cc.id' => $comm_id);
          $compid       = $this->table(array('fx_community_comp'=>'cc'))->field('cm_id')->where($whereCom)->find();

          $where = array('cm.id' => $compid['cm_id']);
          $result       = $this->table(array('fx_comp_manage'=>'cm'))->field(array('name','id'))->where($where)->find();
          return $result;
      }
      /**
       * 获取导入人员名称
       */
      public function getImportName($code){
          $where = array('su.code' => $code);
          $result       = $this->table(array('fx_sys_user'=>'su'))->field('name')->where($where)->find();
          return $result['name'];
      }
      /**
       * 根据导入日志id获取错误信息
       */
      public function getWrongInfo($id){
          $where = array('il.id' => $id);
          $result       = $this->table(array('fx_import_log'=>'il'))->where($where)->find();
          return $result;
      }
      /**
       * 将上传的excel数据导入数据库中，若有一次失败，则回滚并提示信息，否则正常导入
       */
      public function import($userId,array $data,$uploadName,$comp_id){
          //开始导入事务
          $this->startTrans();
          //实例化车辆表的模型
          $carModel      = M('car_manage','fx_'); 
          foreach ($data as $k=>$v){
              $isImport  = $carModel->add($v);
              if($isImport){
                  //若有一条记录添加成功,将其添加到数组中
                  $success[]    = $k+1;  
              }else{
                  $fail[]       = $k+1;
              }
          }
          
          //统计导入正确和错误的条数
          $successCount         = count($success);
          $failCount            = count($fail);
          $error_no             = implode(',',$failCount);
          
          //查询用户信息
          $userModel            = D('user');
          $userInfo             = $userModel->find_user_info($userId);
          if($userInfo){
              $logModel                 = M('import_log','fx_');
              //组装导入日志的数据
              $logData['name']          = $uploadName;
              $logData['code']          = $userInfo['code'];
              $logData['import_time']   = date("Y-m-d H:i:s",time());
              $logData['success']       = $successCount;
              $logData['failures']      = $failCount;
              $logData['error_no']      = $error_no;
              $logData['il_type']       = 2;
              $logData['cm_id']         = $comp_id;
              $logData['user_name']     = $userInfo['name'];
              $logData['create_time']   =date('Y-m-d H:i:s',time());
          }
          if($fail){
              //若添加过程中有任意的错误，事务回滚，并返回相应的结果数值
              $this->rollback();
              //添加导入日志信息
              $isLog        = $logModel->add($logData);
              $result       = array(4,array('success'=>$success,'fail'=>$fail,'logid'=>$isLog));
              return $result;
          }else{
              //若全部添加成功，则提交事务，并返回相应的结果数值
              $this->commit();
              //添加导入日志
              $isLog        = $logModel->add($logData);
              $result       = array(5,array('success'=>$success,'fail'=>$fail,'logid'=>$isLog));
              return $result;
          }
      }

    /**
     * 检查该楼盘下是否存在该车辆
     * 
     * @param string $ccId
     *            楼盘ID
     * @param string $carNumber
     *            车牌号
     * @param string $user
     *            车主
     * @return \Think\mixed
     */
    public function checkCar($ccId, $carNumber, $user)
    {
        $field = array(
            'id',
            'car_number',
            'user',
            'cc_id'
        );
        $where = array(
            'cc_id' => $ccId,
            'car_number' => $carNumber,
            'user' => $user
        );
        $result = $this->field($field)
            ->where($where)
            ->find();
        return $result;
    }

    /**
     * 根据车辆ID查找车辆所属信息
     * 
     * @param array $ids            
     * @return \Think\mixed
     */
    public function getCarBelong(array $ids)
    {
        $field = array(
            'c.id',
            'c.card_number',
            'c.car_number',
            'cc.id' => 'cc_id',
            'cc.name' => 'community_name'
        );
        $where = array(
            'c.id' => array(
                'in',
                $ids
            ),
            'c.cc_id=cc.id'
        );
        $table = array(
            'fx_car_manage' => 'c',
            'fx_community_comp' => 'cc'
        );
        $result = $this->table($table)
            ->field($field)
            ->where($where)
            ->select();
        return $result;
    }
    /**
     * 根据楼盘ID查出所有停车卡
     *
     * @param  $ccid 楼盘ID
     */
    public function getParkForProperty($ccid){
        $field = [
            'id',
            'card_number' => 'number',
            'car_number' => 'plate '
        ];
        return $this->field($field)
            ->where('cc_id=%d', $ccid)
            ->select();
    }
}