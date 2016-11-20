<?php
/*************************************************
 * 文件名：PublicnoModel.class.php
 * 功能：     微信公众号信息模型
 * 日期：     2015.11.11
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class PublicnoModel extends Model
{

    protected $tableName = 'publicno';

    /**
     * 根据企业ID获取公众号信息
     *
     * @param string $cmId
     *            企业ID
     * @param integer $isCancel
     *            是否取消授权 -1-否 1-是
     * @return \Think\mixed
     */
    public function getPublicnoInfo($cmId, $isCancel = -1)
    {
        $where = array(
            'cm_id' => $cmId,
            'isCancel' => $isCancel
        );
        $result = $this->where($where)->find();
        return $result;
    }

    /**
     * 保存微信支付设置
     * 
     * @param string $id
     *            主键ID
     * @param string $mchId
     *            商户ID
     * @param string $apiKey
     *            API秘钥
     * @return boolean
     */
    public function savePayInfo($id, $mchId, $apiKey)
    {
        $data = array(
            'mch_id' => $mchId,
            'api_key' => $apiKey,
            'update_time' => date('Y-m-d H:i:s')
        );
        $where = array(
            'id' => intval($id)
        );
        $result = $this->where($where)->save($data);
        if (! $result)
            return false;
        return true;
    }

    public function getPublicnoByAppid($appid){
        return $this->where(array('appid' => $appid))->find();
    }

    public function getPublicnoByUmid($umid){
        return $this->where(array('um_id' => $umid))->find();    
    }

    public function getAllPublicno(){
        return $this->where(array('isCancel' => -1))->select();
    }

    public function addUmid(){
         $datas = $this->getAllPublicno();
         foreach ($datas as $key => $data) {
             if (!$data['um_id']) {
                 continue;
             }
             $data['um_id'] = uniqid();
             usleep(1);
             $results[] = $this->save($data);
         }
         return $results;
    }
}