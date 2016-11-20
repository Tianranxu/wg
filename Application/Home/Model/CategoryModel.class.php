<?php
/*************************************************
 * 文件名：CategoryModel.class.php
 * 功能：     分类管理模型
 * 日期：     2015.9.22
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

class CategoryModel extends BaseModel
{

    protected $tableName = 'sys_category';

    /**
     * 获取图文分类列表
     *
     * @param string $cm_id
     *            企业ID
     * @param string $type
     *            分类类型
     * @return \Think\mixed
     */
    public function getNewsist($cm_id, $type = '', $status = '')
    {
        $map = array(
            'cm_id' => $cm_id,
            'type' => array(
                'lt',
                100
            )
        );
        if ($type) {
            $temp['type'] = $type;
            $map['_complex'] = $temp;
        }
        if ($status)
            $map['status'] = $status;
        $result = $this->where($map)->getField('id,name,type,cm_id,sequence,icon_id', true);
        return $result;
    }

    /**
     * 获取分组列表
     *
     * @param string $cmId
     *            企业ID
     * @param string $type
     *            分组类型
     * @param string $status
     *            分组状态
     * @return \Think\mixed
     */
    public function getCategoryList($cmId, $type = '', $status = '')
    {
        $field = array(
            'id',
            'name',
            'type',
            'cm_id',
            'sequence',
            'icon_id'
        );
        $where = array(
            'cm_id' => $cmId
        );
        if ($type)
            $where['type'] = $type;
        if ($status)
            $where['status'] = $status;
        $result = $this->field($field)
            ->where($where)
            ->select();
        return $result;
    }

    /**
     * 修改分类名称
     *
     * @param string $cm_id
     *            企业ID
     * @param string $id
     *            分类ID
     * @param string $name
     *            分类名称
     * @return boolean
     */
    public function edit_category_name($cm_id, $id, $name)
    {
        $map = array(
            'id' => $id,
            'cm_id' => $cm_id
        );
        $data = array(
            'name' => $name
        );
        $this->startTrans();
        $result = $this->where($map)->save($data);
        if (! $result) {
            $this->rollback();
            return false;
        } else {
            $this->commit();
            return true;
        }
    }

    /**
     * 执行分类
     *
     * @param string $cmId
     *            企业ID
     * @param string $name
     *            分类名称
     * @param integer $type
     *            分类类型
     * @param integer $doType
     *            执行类型
     * @param string $id
     *            分类ID
     * @return mixed
     */
    public function doCategory($cmId, $name = '', $type, $doType, $id = '')
    {
        $function = new \ReflectionMethod(__CLASS__, $doType . 'Category');
        if ($doType == 'add')
            $result = $function->invoke($this, $cmId, $name, $type);
        if ($doType == 'edit')
            $result = $function->invoke($this, $cmId, $name, $type, $id);
        if ($doType == 'del')
            $result = $function->invoke($this, $cmId, $type, $id);
        return $result;
    }

    /**
     * 添加分类
     *
     * @param string $cmId
     *            企业ID
     * @param string $name
     *            分类名称
     * @param integer $type
     *            分类类别
     * @return boolean
     */
    public function addCategory($cmId, $name, $type)
    {
        $data = array(
            'cm_id' => $cmId,
            'name' => $name,
            'type' => intval($type)
        );
        $this->startTrans();
        $result = $this->add($data);
        if (! $result) {
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    /**
     * 编辑分类
     *
     * @param string $cmId
     *            企业ID
     * @param string $name
     *            分类名称
     * @param integer $type
     *            分类类型
     * @param string $id
     *            分类ID
     * @return boolean
     */
    public function editCategory($cmId, $name, $type, $id)
    {
        $data = array(
            'name' => $name,
            'modify_time' => date('Y-m-d H:i:s')
        );
        $where = array(
            'id' => $id,
            'cm_id' => $cmId,
            'type' => intval($type)
        );
        $this->startTrans();
        $result = $this->where($where)->save($data);
        if (! $result) {
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    /**
     * 删除分类
     * 
     * @param string $cmId
     *            企业ID
     * @param integer $type
     *            分类类型
     * @param string $id
     *            分类ID
     * @return boolean
     */
    public function delCategory($cmId, $type, $id)
    {
        $where = array(
            'id' => $id,
            'cm_id' => $cmId,
            'type' => $type
        );
        $this->startTrans();
        $result = $this->where($where)->delete();
        if (! $result) {
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    public function getCateListByCompId($compid, $type = '', $status = '')
    {
        $map = array(
            'sc.cm_id' => $compid
        );
        if ($type)
            $map['type'] = $type;
        if ($status)
            $map['status'] = $status;
        $result = $this->table(array(
            'fx_sys_category' => 'sc',
        ))
            ->where($map)
            ->field('sc.id,sc.name,sc.type,sc.cm_id,sc.sequence')
            ->order('sc.sequence')
            ->select();
        return $result;
    }

    public function getCategoryByCompType($compid, $type)
    {
        $map = array(
            'sc.type' => $type,
            'sc.cm_id' => $compid
        );
        $results = $this->table(array(
            'fx_sys_category' => 'sc',
        ))
            ->field('sc.id')
            ->where($map)
            ->find();
        return $results['id'];
    }


    public function getCompidByAppid($appid)
    {
        $table = 'fx_publicno';
        $where = array(
            'appid' => $appid
        );
        $field = 'cm_id';
        $compid = $this->table($table)
            ->field($field)
            ->where($where)
            ->find();
        return $compid['cm_id'];
    }

    public function getMediaidByCompidAndType($compid, $type)
    {
        $whereCate = array(
            'type' => $type,
            'cm_id' => $compid
        );
        $category_id = $this->table('fx_sys_category')
            ->field(array(
            'id'
        ))
            ->where($whereCate)
            ->find();
        $whereMedia = array(
            'category_id' => $category_id['id']
        );
        $media_id = $this->table('fx_imgtxt_manage')
            ->field(array(
            'media_id'
        ))
            ->where($whereMedia)
            ->find();
        return $media_id['media_id'];
    }

    public function getIdByTypeAndCompid($compid,$type){
        $where = array(
            'cm_id' => $compid,
            'type' => $type
        );
        return $this->where($where)->field('id')->find();
    }
}


