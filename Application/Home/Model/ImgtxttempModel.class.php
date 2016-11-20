<?php
/*************************************************
 * 文件名：ImgtxttempModel.class.php
 * 功能：     图文信息/图片库Mysql模型
 * 日期：     2015.10.27
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model;

class ImgtxttempModel extends BaseModel
{

    protected $tableName = 'imgtxt_manage';

    /**
     * 获取图文信息所属的信息
     *
     * @param array $mediaIds
     *            媒体ID
     * @return \Think\mixed
     */
    public function getNewsTempList(array $mediaIds)
    {
        $field = array(
            'i.id',
            'i.media_id',
            'c.id' => 'category_id',
            'c.name' => 'category_name',
            'c.type' => 'category_type',
            'i.views',
            'i.likes'
        );
        $where = array(
            'i.media_id' => array(
                'in',
                $mediaIds
            ),
            'i.category_id=c.id'
        );
        $table = array(
            'fx_imgtxt_manage' => 'i',
            'fx_sys_category' => 'c'
        );
        $result = $this->table($table)
            ->field($field)
            ->where($where)
            ->select();
        return $result;
    }

    /**
     * 处理图文信息分类
     *
     * @param string $mediaId
     *            企业ID
     * @param string $categoryId
     *            分类ID
     * @param string $type
     *            处理分类，默认为add，add-添加 edit-编辑 del-删除
     * @return \Think\mixed
     */
    public function doNewsCategory($mediaId, $categoryId = '', $type = 'add')
    {
        $data = array(
            'category_id' => $categoryId,
            'modify_time' => date('Y-m-d H:i:s'),
        );
        $function = new \ReflectionMethod(__CLASS__, $type . 'NewsCategory');
        $result = $function->invoke($this, $mediaId, $data);
        return $result;
    }

    /**
     * 新增图文信息分类
     *
     * @param array $data
     *            添加的数据
     * @param string $mediaId
     *            媒体ID
     * @return \Think\mixed
     */
    public function addNewsCategory($mediaId, array $data)
    {
        $data['media_id'] = $mediaId;
        $result = $this->add($data);
        return $result;
    }

    /**
     * 编辑图文信息分类
     *
     * @param array $data
     *            编辑的数据
     * @param string $mediaId
     *            媒体ID
     * @return Ambigous <boolean, unknown>
     */
    public function editNewsCategory($mediaId, array $data)
    {
        $where = array(
            'media_id' => $mediaId
        );
        $result = $this->where($where)->save($data);
        return $result;
    }

    /**
     * 删除图文信息分类
     *
     * @param string $mediaId
     *            图文信息媒体ID
     * @return \Think\mixed
     */
    public function delNewsCategory(array $mediaIds)
    {
        $where = array(
            'media_id' => array(
                'in',
                $mediaIds
            )
        );
        $result = $this->where($where)->delete();
        return $result;
    }

    /**
     * 移动分组
     *
     * @param string $categoryId
     *            分类ID
     * @param array $mediaIds
     *            媒体ID
     * @return boolean
     */
    public function moveGroup($categoryId, array $mediaIds)
    {
        // 删除原来的分组
        $where = array(
            'media_id' => array(
                'in',
                $mediaIds
            )
        );
        $delResult = $this->where($where)->delete();
        
        // 移到未分组
        if (! $categoryId) {
            return true;
        }
        
        // 组装批量数据
        foreach ($mediaIds as $mediaId) {
            $dataList[] = array(
                'category_id' => $categoryId,
                'media_id' => $mediaId,
                'modify_time' => date('Y-m-d H:i:s')
            );
        }
        $this->startTrans();
        $addResult = $this->addAll($dataList);
        if (! $addResult) {
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    /**
     * 删除分组
     * 
     * @param string $cmId
     *            企业ID
     * @param string $categoryId
     *            媒体ID
     * @return boolean
     */
    public function delGroup($cmId, $categoryId)
    {
        $field=array(
            'i.id'
        );
        $where = array(
            'c.cm_id'=>$cmId,
            'i.category_id' => $categoryId,
            'i.category_id=c.id'
        );
        $table=array(
            'fx_imgtxt_manage'=>'i',
            'fx_sys_category'=>'c'
        );
        $selectResult = $this->table($table)->field($field)->where($where)->select();
        foreach ($selectResult as $id){
            $ids[]=$id['id'];
        }
        $this->startTrans();
        $delResult=$this->where(array('id'=>array('in',$ids)))->delete();
        if (!$delResult){
            $this->rollback();
        }
        $this->commit();
        
        $categoryModel = D('category');
        $categoryResult = $categoryModel->doCategory($cmId, '', 101, 'del', $categoryId);
        return true;
    }

    /**
     * 获取图片库已分类信息
     *
     * @param string $cmId
     *            企业ID
     * @return \Think\mixed
     */
    public function getPicTempList($cmId)
    {
        $field = array(
            'i.media_id',
            'c.id' => 'category_id',
            'c.name' => 'category_name'
        );
        $where = array(
            'c.cm_id' => $cmId,
            'c.id=i.category_id',
            'c.type' => 101
        );
        $table = array(
            'fx_imgtxt_manage' => 'i',
            'fx_sys_category' => 'c'
        );
        $result = $this->table($table)
            ->field($field)
            ->where($where)
            ->select();
        return $result;
    }

    /**
     * 查询图片库分组以及其总数
     *
     * @param string $cmId
     *            企业ID
     * @return unknown
     */
    public function countAllPiclibrary($cmId)
    {
        $field = array(
            'c.id',
            'c.name',
            'i.media_id',
            'COUNT("i.media_id")' => 'total'
        );
        $where = array(
            'c.type' => 101,
            'c.cm_id' => $cmId
        );
        $table = array(
            'fx_sys_category' => 'c'
        );
        $result = $this->table($table)
            ->field($field)
            ->join('`fx_imgtxt_manage` `i` ON i.category_id=c.id', 'LEFT')
            ->where($where)
            ->group('c.id')
            ->select();
        return $result;
    }

    public function like($openid, $media_id)
    {
        $redis = $this->connectRedis();
        if (empty($redis)) {
            return false;
        }
        if (! $redis->sismember('likes:' . $media_id, $openid)) {
            $redis->sadd('likes:' . $media_id, $openid);
            return D('Material')->where(array(
                'media_id' => $media_id
            ))->setInc('likes');
        }
        $this->disConnectRedis();
        return false;
    }

    public function is_liked($openid, $media_id)
    {
        $redis = $this->connectRedis();
        if (empty($redis)) {
            return false;
        }
        $liked = $redis->sismember('likes:' . $media_id, $openid);
        $this->disConnectRedis();
        return $liked;
    }
}


