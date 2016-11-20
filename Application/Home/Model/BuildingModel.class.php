<?php
/*************************************************
 * 文件名：BuildingModel.class.php
 * 功能：     楼栋模型
 * 日期：     2015.7.23
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;
use Think\Model;

class BuildingModel extends Model{

    protected $trueTableName = 'fx_building_manage';
    
    /**
     * 查询出所有楼栋
     * @param int $proid      楼盘id
     * @param int $star       开始编号
     * @param int $end        结束编号
     */
    public function selectAllBuild($proid,$star=0,$end=0,$status='',$isSortByNumber=false){
        $field  = 'id,name,remark,number,cc_id,create_time as ctime,modify_time as mtime,status';
        $where=array(
            'cc_id'=>$proid
        );
        if ($status) $where['status']=$status;
        $order = 'status desc,modify_time desc,number asc';
        if ($isSortByNumber) $order = 'number asc,modify_time desc,number asc';
        if(!$end){
            $result = $this->field($field)->where($where)->order('status desc ,modify_time desc, number asc')->select();
        }else{
            $result = $this->field($field)->where($where)->order('status desc ,modify_time desc, number asc')->limit($star,$end)->select();
        }

        return $result;
    }
    
    /**
     * 查询楼栋最后一个编号
     * @param int $proid      楼盘id
     * 
     */
    
    public function selectLastNumber($proid){
        $field  = 'id';
        $lastID = $this->field($field)->where("cc_id=%d",$proid)->order('id desc')->limit(1)->find();
        $result = $this->field('number')->where("id=%d",$lastID['id'])->find();
        return $result;
    }
    /**
     * 查询出某个楼盘
     * @param string $id       楼盘id
     */
    
    public function selectBuild($id){
        $field  = 'id,name,number,remark,create_time as ctime,cc_id as pid,status';
        $result = $this->field($field)->where("id=%d",$id)->find();
        return $result;
    }
    /**
     * 模糊查询楼盘
     * @param string $pid          楼盘id
     * @param string $number       楼宇编号
     * @param string $name         楼宇名称
     * @param string $status       楼宇状态
     */
    
    public function searchBuild($pid,$number,$name,$status,$star,$end){
        $field = 'id,name,remark,number,cc_id,create_time as ctime,modify_time as mtime,status';
        $map['name']   = array('like',"%{$name}%");
        $map['number'] = array('like',"%{$number}%");
        if($status==2){
            $result = $this->field($field)->where($map)->where("cc_id=%d",$pid)->order('status desc ,modify_time desc, number asc')->limit($star,$end)->select();
        }else{
            $result = $this->field($field)->where($map)->where("status=%d AND cc_id=%d",$status,$pid)->order('status desc ,modify_time desc, number asc')->limit($star,$end)->select();
        }
        return $result;
    }

    /**
     * 根据楼盘ID（或者楼盘ID集）获取楼栋列表
     * @param array $ccIds  楼盘ID或楼盘ID集
     * @return mixed
     */
    public function getBuildingListsByCommunityIds(array $ccIds)
    {
        $where=[
            'cc_id'=>['in',$ccIds],
        ];
        $result=$this->where($where)->select();
        return $result;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
    
