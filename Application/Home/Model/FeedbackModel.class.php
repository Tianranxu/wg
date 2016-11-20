<?php
/**
 * 文件名：FeedbackModel.class.php  
 * 功能：意见反馈模型
 * 作者：XU    
 * 日期：2015/10-12
 * 版权：Copyright @2015 风馨科技 All Right Reserved
 */

namespace Home\Model;
use Think\Model;

class FeedbackModel extends Model{
    /*
    * 通过compid和openid获取意见反馈
    */
    public function getFeedback($compid,$openid,$isSystem){
        $where = array(
            'cm_id' => $compid,
            'openid' => $openid,
            'isSystem' => $isSystem
         );
        return $this->table('fx_feedback')->field(array('id','content','create_time'))->order('create_time desc')->where($where)->select();
    }

    /*
    * 通过意见反馈id获取所有图片的url
    */
    public function getPictureAndResByFid($fid){
        $where = array('fid' => $fid);
        $result['picture'] = $this->table('fx_feedback_picture')->field('id','pic_url')->where($where)->select();
        $result['response'] = $this->table('fx_feedback_response')->field('id','content','create_time')->where($where)->select();
        return $result;
    }

    /*
    * 添加意见反馈的图片
    */
    public function addPicture($fid,$picData){
        $pictureModel = M('feedback_picture');
        foreach ($picData as $k => $v) {
            $data['fid'] = $fid;
            $data['pic_url'] = $v;
            if (!$pictureModel->add($data)) {
                $flag = -1;
            }
        }
        if ($flag == -1) {
            return false;
        }else{
            return 1;
        }
    }

    /*
    * 通过公司id获取appid
    */
    public function getAppid($compid){
        $result = $this->table('fx_publicno')->field(array('appid'))->where(array('cm_id'=>$compid))->find();
        return $result['appid'];
    }

    /*
    * 通过appid获取反馈信息列表数据（包括图片信息、用户信息和信息的回复）
    */
    public function getFeedbackByCompid($compid,$content='',$status='',$offset,$count,$isSystem,$cm_ids=''){
        //获取反馈信息和用户基本信息
        $table = array(
            'fx_feedback' => 'f',
            'fx_weixin_user' => 'wu'
        );
        $field = array(
            'f.id','f.openid','f.content','f.create_time','wu.mobile','wu.nickname'
        );
        if ($compid && (!$cm_ids)) {
            $where = array(
                'f.cm_id' => $compid,
                'wu.cm_id' => $compid,
                'isSystem' => $isSystem,
                'wu.openid=f.openid',
            );
        }elseif ($compid && $cm_ids){
            $where = array(
                'f.cm_id' => $compid,
                'wu.cm_id' => array('in',$cm_ids),
                'isSystem' => $isSystem,
                'wu.openid=f.openid',
            );
        }else{
            $where = array(
                'isSystem' => $isSystem,
                'wu.openid=f.openid',
            );
        }
        //若有搜索条件则添加
        if ($content) {
            $where['content'] = array('LIKE',"%{$content}%"); 
        }
        if ($status) {
            $where['status'] = $status;
        }
        $fbdata = $this->table($table)->where($where)->field($field)->limit($offset,$count)->order('create_time desc')->select();
        //获取图片信息和回复信息
        foreach ($fbdata as $k => $v) {
            $ids[] = $v['id'];
            $fbdata[$k]['picture'] = '';
        }
        $fbpicture = $this->table('fx_feedback_picture')->field(array('pic_url','fid'))->where(array('fid'=>array('in',$ids)))->select();
        $re_table = array(
            'fx_feedback_response'=>'fr',
            'fx_sys_user' => 'su'
        );
        $re_field = array(
            'fr.user_id','fr.content','fr.create_time','su.name','fr.fid'
        );
        $re_where = array(
            'fid'=>array('in',$ids),
            'fr.user_id=su.id'
        );
        $fbresponse = $this->table($re_table)->field($re_field)->where($re_where)->order('create_time desc')->select();
        //将图片信息和回复信息添加到对应的反馈信息中
        foreach ($fbpicture as $k => $v) {
            $picData[$v['fid']][] = $v;
        }
        foreach ($fbresponse as $k => $v) {
            $resData[$v['fid']][] = $v;
        }
        foreach ($fbdata as $k => $v) {
            $fbdata[$k]['picture'] = $picData[$v['id']];
            $fbdata[$k]['response'] = $resData[$v['id']];
        }
        return $fbdata;
    }

    /*
    * 通过appid获取反馈信息的数量
    */
    public function getFeedbackCount($appid){
        return $this->table('fx_feedback')->where(array('appid'=>$appid))->count();
    }

    /*
    * 添加回复信息
    */
    public function addResponse($data){
        $this->table('fx_feedback')->where(array('id' => $data['fid']))->save(array('status'=>1));
        $responseModel = M('feedback_response');
        return $responseModel->add($data);
    }

    /*
    * 查看用户的个人信息是否存在
    */
    public function getUser($openid){
        return $this->table('fx_weixin_user')->field(array('mobile'))->where(array('openid'=>$openid))->find();
    }

    /*
    * 获取公司类型
    */
    public function getCompanyType($compid){
        $result = $this->table('fx_comp_manage')->field('cm_type')->where(array('id'=>$compid))->find();
        return $result['cm_type'];
    }

    /**
    * 通过id获取反馈信息的提交人及其所在的物业公司
    * @param int $fid 反馈信息id
    */
    public function getUserById($fid){
        $table = [
            'fx_feedback' => 'ff',
            'fx_publicno' => 'fp',
            'fx_comp_manage' => 'cm'
        ];
        $where = [
            'ff.id' => $fid, 
            'ff.appid = fp.appid', 
            'ff.cm_id = cm.id'
        ];
        $field = [
            'ff.openid', 'fp.cm_id', 'ff.isSystem', 'ff.cm_id' => 'ori_cmid', 'cm.cm_type', 'fp.um_id', 
        ];
        return $this->table($table)->where($where)->field($field)->find();
    }
}