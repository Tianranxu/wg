<?php
/*************************************************
 * 文件名：ImgtxtModel.class.php
 * 功能：     图文信息Mongo模型
 * 日期：     2015.10.27
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model\MongoModel;

class ImgtxtModel extends MongoModel
{

    protected $connection = 'DB_MONGO';

    protected $tableName = 'imgtxt';

    /**
     * 获取图文信息列表
     * 
     * @param string $cmId
     *            企业ID
     * @param number $page
     *            查询起始位置
     * @param number $limit
     *            限制记录数
     * @return multitype:unknown
     */
    public function getImgtxtList($cmId, $page = 0, $limit = 10, $category = '', $keywords = '', $_id = '', $is_publish='')
    {
        // 连接MongoDB，连接wg库下的fx_imgtxt集合
        //$collection = $this->db->getCollection();
        $mongo = new \MongoClient();
        $collection = $mongo->wg->fx_imgtxt;
        $return=array();
        // 查询分类,并不真正关心cmId,没有category的情况,才用cmid查询
        if ($category) {
            $return[] = array('category_id'=>$category);
        } else {
            $return[] = array('cm_id'=>$cmId);
        }
        // 查询关键字
        if ($keywords) {
            $keywordsArr=array('$or'=>array(
                array(
                    'title'=>array('$regex'=>$keywords)
                ),
                array(
                    'author'=>array('$regex'=>$keywords)
                ),
                array(
                    'digest'=>array('$regex'=>$keywords)
                )
            ));
            $return[] = $keywordsArr;
        }
        if ($is_publish) {
            $return[] = ['is_publish' => intval($is_publish)];
        }
        // 查询单个图文信息
        if ($_id) {
            $return = [];
            $return[] = array('_id'=>new \MongoId($_id));
        }
        // 获取游标
        $cursor = $collection->find(array('$and'=>$return));
        // 获取列表数据
        $list = iterator_to_array($cursor->sort(array(
            'update_time' => - 1
        ))
            ->limit($limit)
            ->skip($page));
        // 获取总数
        $total = $cursor->count();
        //关闭连接
        $mongo->close();
        return array(
            'list' => $list,
            'total' => $total
        );
    }

    /**
     * 处理图文信息
     *
     * @param string $cmId
     *            企业ID
     * @param array $data
     *            处理的数据
     * @param string $type
     *            处理类型，默认为add，add-添加 edit-编辑
     * @return mixed
     */
    public function doImgtxt($cmId, array $data, $type = 'add', $id = '')
    {
        $function = new \ReflectionMethod(__CLASS__, $type . 'Imgtxt');
        $result = $function->invoke($this, $data, $id);
        return $result;
    }

    /**
     * 新建图文信息
     *
     * @param array $data
     *            添加数据
     * @return boolean
     */
    public function addImgtxt(array $data)
    {
        // TODO 添加图文信息到MongoDB
        $dataResult = $this->add($data);
        if (! $dataResult)
            return false;
            
        // TODO 为图文信息添加分类
        $imgtxtTempModel = D('imgtxttemp');
        $categoryResult = $imgtxtTempModel->doNewsCategory(json_decode(json_encode($dataResult), true)['$id'], $data['category_id'], 'add');
        return json_decode(json_encode($dataResult), true)['$id'];
    }

    /**
     * 编辑图文信息
     * 
     * @param array $data
     *            编辑的数据
     * @param string $id
     *            图文信息媒体ID
     * @return boolean
     */
    public function editImgtxt(array $data, $id)
    {
        // TODO 编辑图文信息到MongoDB
        $where = array(
            '_id' => $id
        );
        $dataResult = $this->where($where)->save($data);
        if (! $dataResult['ok'])
            return false;
            
        // TODO 为图文信息编辑分类
        $imgtxttempModel = D('imgtxttemp');
        $categoryResult = $imgtxttempModel->doNewsCategory($id, $data['category_id'], 'edit');
        return true;
    }

    /**
     * 删除图文信息
     * 
     * @param array $data
     *            删除的数据
     * @return boolean
     */
    public function delImgtxt(array $data)
    {
        // TODO 从MongoDB删除图文信息
        $where = array(
            '_id' => $data['_id']
        );
        $result = $this->where($where)->delete();
        if (! $result)
            return false;
            
            // TODO 从Mysql删除图文信息分类
        $imgtxtTempModel = D('imgtxttemp');
        $categoryResult = $imgtxtTempModel->doNewsCategory(array($data['_id']), '', 'del');
        return true;
    }

    public function getImgtxtByMediaId($media_id) {
        return $this->getImgtxtsByCategory('', $media_id)[$media_id];
    }

    public function getImgtxtsByCategory($category_id, $media_id, $keyword){
        if ($media_id) {
            $articles = $this->getImgtxtList('', 0, 1, '', '', $media_id, 1)['list'];
        }else {
            $articles = $this->getImgtxtList('', 0, 100, $category_id, $keyword, '', 1)['list'];
        }
        $article_ids = array_keys($articles);
        $articles_info = D('imgtxttemp')->getNewsTempList($article_ids);
        foreach ($articles_info as $info) {
            $articles[$info['media_id']] = array_merge($articles[$info['media_id']], $info);
        }
        return $articles;
    }

    public function incViews($media_id) {
        return D('imgtxttemp')->where(array(
            'media_id' => $media_id
        ))->setInc('views'); // 文章阅读数加1
    }

    /**
     * 旧图文数据更新为已发布临时脚本
     * @param array $datas
     * @return bool
     */
    public function imgtxtPublishScript(array $datas)
    {
        $compid = array_map(function ($query) {
            return $query['id'];
        }, M('comp_manage')->field('id')->select());
        $result = $this->where(['cm_id' => ['in', $compid]])->save($datas);
        return $result ? true : false;
    }

    /**
     * 图文发布方法
     */
    public function publish($id){
        return $this->where(['_id' => $id])->save(['is_publish' => 1]);
    }
}


