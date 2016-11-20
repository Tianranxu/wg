<?php
/*************************************************
 * 文件名：PiclibraryModel.class.php
 * 功能：     图片库模型
 * 日期：     2015.10.27
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model\MongoModel;

class PicLibraryModel extends MongoModel
{

    protected $connection = 'DB_MONGO';

    protected $tableName = 'piclibrary';

    /**
     * 获取图片库列表
     *
     * @param string $cmId
     *            企业ID
     * @return \Think\mixed
     */
    public function getPiclibraryList($cmId, $option = '', array $mediaIds = array())
    {
        if (!$mediaIds){
            $mediaIds=array();
        }
        // 连接wg库下的fx_piclibrary集合
        $collection = $this->db->getCollection();
        $return = array();
        $cmIdArr = array(
            'cm_id' => $cmId
        );
        array_push($return, $cmIdArr);
        // 未分组
        if ($option == 'nin') {
            $mediaIdArr = array(
                'media_id' => array(
                    '$nin' => $mediaIds
                )
            );
            array_push($return, $mediaIdArr);
        }
        // 已分组
        if ($option == 'in') {
            $mediaIdArr = array(
                'media_id' => array(
                    '$in' => $mediaIds
                )
            );
            array_push($return, $mediaIdArr);
        }
        // 获取游标
        $cursor = $collection->find(array(
            '$and' => $return
        ));
        // 获取列表数据
        $list = iterator_to_array($cursor->sort(array(
            'update_time' => - 1
        )));
        return $list;
    }

    /**
     * 添加图片
     *
     * @param string $cmId
     *            企业ID
     * @param string $picName
     *            图片名称
     * @param string $picPath
     *            图片路径
     * @param string $thunmbPicPath
     *            图片压缩路径
     * @return boolean|multitype:unknown \Think\Model\mixed
     */
    public function addPicture($cmId, $picName, $picPath, $thunmbPicPath)
    {
        $data = array(
            'cm_id' => $cmId,
            'pic_name' => $picName,
            'pic_path' => $picPath,
            'thunmb_pic_path' => $thunmbPicPath,
            'update_time' => date('Y-m-d H:i:s')
        );
        $result = $this->add($data);
        $mediaId = $this->getLastInsID();
        if (! $result)
            return false;
        $editResult = $this->where(array(
            '_id' => $mediaId
        ))->save(array(
            'media_id' => $mediaId
        ));
        return array(
            'id' => $mediaId,
            'thunmb_pic_path' => $thunmbPicPath
        );
    }

    /**
     * 删除图片
     * 
     * @param array $mediaIds
     *            媒体ID
     * @return boolean
     */
    public function delPicture(array $mediaIds)
    {
        $where = array(
            'media_id' => array(
                'in',
                $mediaIds
            )
        );
        $result = $this->where($where)->delete();
        if (! $result)
            return false;
            // 删除图片所属分类
        $imgtxttempModel = D('imgtxttemp');
        $tempResult = $imgtxttempModel->doNewsCategory($mediaIds, '', 'del');
        return true;
    }

    /**
     * 根据媒体ID获取图片库列表
     *
     * @param array $thunmbMediaIds
     *            媒体ID
     * @return \Think\mixed
     */
    public function getPicsByMediaIds(array $thunmbMediaIds)
    {
        $where = array(
            'media_id' => array(
                'in',
                $thunmbMediaIds
            )
        );
        $result = $this->where($where)->select();
        return $result;
    }
}