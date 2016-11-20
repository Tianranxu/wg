<?php
/*
 * 文件名：FormModel.class.php
 * 功能：表单模型
 * 作者：XU
 * 日期：2015-10-21
 * 版权：CopyRight @ 2015 风馨科技 All Rights Reserved
 */
namespace Home\Model;

use Think\Model\MongoModel;
use Zh2Py\CUtf8_PY;

class FormModel extends MongoModel
{

    protected $connection = 'DB_MONGO';

    protected $tableName = 'form';

    /*
     * 根据公司id获取公司下所有的保存的表单
     */
    public function getAllForm($compid, $user_id, $status = '', $content = '')
    {
        $collection = $this->db->getCollection();
        $query = array(
            '$or' => array(
                array(
                    'cm_id' => $compid,
                    'type' => '-1'
                ),
                array(
                    'creator_id' => $user_id,
                    'type' => '1',
                    'cm_id' => $compid
                )
            )
        );
        if ($status) {
            if ($status == - 1) {
                $where['$and'][] = array(
                    'status' => array(
                        '$lt' => 0
                    )
                );
            } else {
                $where['$and'][] = array(
                    'status' => array(
                        '$gt' => 0
                    )
                );
            }
        }
        if ($content) {
            $where['$and'][] = array(
                'name' => array(
                    '$regex' => $content
                )
            );
        }
        $where['$and'][] = $query;
        $result = iterator_to_array($collection->find($where), false);
        foreach ($result as $k => $v) {
            $time[$k] = $v['creat_time'];
        }
        array_multisort($time, SORT_DESC, $result);
        return $result;
    }

    /*
     * 根据form_id发布,禁用和启用表单
     */
    public function publish_banpick($form_id, $status)
    {
        $collection = $this->db->getCollection();
        if ($form_id && $status) {
            if ($status == - 3) {
                return $collection->update(array(
                    'form_id' => $form_id
                ), array(
                    '$set' => array(
                        'status' => 2,
                        'create_time' => date("Y-m-d H:i:s")
                    )
                ));
            }
            return $collection->update(array(
                'form_id' => $form_id
            ), array(
                '$set' => array(
                    'status' => $status,
                    'create_time' => date("Y-m-d H:i:s")
                )
            ));
        } else {
            return false;
        }
    }

    /*
    *  获取表单的填写详情
    */
    public function getFormDetail($form_id,$offset,$conditions){
        $mongo = new \MongoClient();
        $collection_name = 'fx_data_'.$form_id;
        $db_name = C('DB_MONGO')['DB_NAME'];
        $collection = $mongo->$db_name->$collection_name;
        $where = array();
        if ($conditions['submitter']) {
            $where['$and'][] = array(
                'submitter' => array(
                    '$regex' => $conditions['submitter'],
                ),
             );
        }
        if ($conditions['submit_time']) {
            $where['$and'][] = array(
                'submit_time' => array(
                    '$regex' => $conditions['submit_time'],
                ),
            );  
        }
        if ($conditions['approval_comment']) {
            $where['$and'][] = array(
                'approval_comment' => array(
                    '$regex' => $conditions['approval_comment'],
                ),
            );  
        }
        if ($conditions['approver']) {
            $where['$and'][] = array(
                'approver' => array(
                    '$regex' => $conditions['approver'],
                ),
            );  
        }
        if ($conditions['approval_status']) {
            $where['$and'][] = array(
                'approval_status' => $conditions['approval_status'],
            );  
        }
        $result = iterator_to_array($collection
            ->find($where)
            ->limit(10)
            ->skip($offset)
            ->sort(
                array(
                    'submit_time' => -1    
                )),false);
        $count = $collection->count($where);
        $mongo->close();
        return array(
            'result' => $result,
            'count' => $count,
            'total' => ceil($count/10));
    }

    /*
    * 获取某一个表单的填写详情
    */
    public function getOneFormDetail($serial,$form_id){
        $mongo = new \MongoClient();
        $collection_name = 'fx_data_'.$form_id;
        $db_name = C('DB_MONGO')['DB_NAME'];
        $collection = $mongo->$db_name->$collection_name;
        $result =  iterator_to_array($collection->find(array('serial'=>$serial)),false);
        $mongo->close();
        return $result[0];
    }

    /*
    * 审核某一个表单的提交情况    
    */
    public function check($serial,$form_id,$status,$comment,$approver){
        $mongo = new \MongoClient();
        $collection_name = 'fx_data_'.$form_id;
        $db_name = C('DB_MONGO')['DB_NAME'];
        $collection = $mongo->$db_name->$collection_name;
        $update = array(
            '$set' => array(
                'approval_status' => intval($status),
                'approver' => $approver['name'],
                'approval_time' => date('Y-m-d H:i:s'),
                'approval_account' => $approver['code']
            )
        );
        if($comment)
            $update['$set']['approval_comment'] = $comment;
        $result = $collection->update(array(
                'serial' => $serial
            ),$update);
        $mongo->close();
        return $result;
    }

    /**
     * 查询表单信息
     * @param string $formId
     *            表单ID
     * @param string $status
     *            表单状态 -1-禁用 1-未发布 2-已发布，默认为空，即查询全部状态
     * @return \Think\mixed
     */
    public function getFormInfo($formId, $status = '')
    {
        $where = array(
            'form_id' => $formId,
        );
        if ($status)
            $where['status'] = $status;
        $result = $this->where($where)->find();
        return $result;
    }

    /**
     * 处理表单
     *
     * @param array $data
     *            表单数据
     * @param string $type
     *            处理类型，add-新建表单，edit-编辑表单，默认add
     * @param string $currentFormId
     *            当前表单form_id
     * @return mixed
     */
    public function doForm(array $data, $type = 'add', $currentFormId = '')
    {
        $function = new \ReflectionMethod(__CLASS__, $type . 'Form');
        $result = $function->invoke($this, $data, $currentFormId);
        return $result;
    }

    /**
     * 新建表单
     *
     * @param array $data
     *            表单数据
     * @return \Think\Model\mixed
     */
    public function addForm(array $data)
    {
        // 查询表单是否存在
        $isExists = $this->isFormExists($data['cm_id'], $data['name']);
        if ($isExists) {
            vendor('Zh2py.CUtf8_PY');
            $Zh2py = new CUtf8_PY();
            $data['name'] = $data['name'] . '_' . ($isExists + 1);
            $data['form_id'] = $data['cm_id'] . '_' . $Zh2py->encode($data['name']);
        }
        $result = $this->add($data);
        return $result;
    }

    /**
     * 编辑表单
     *
     * @param array $data
     *            表单数据
     * @param unknown $formId
     *            当前表单form_id
     * @return boolean
     */
    public function editForm(array $data, $formId)
    {
        if ($formId != $data['form_id']) {
            // TODO 如果表单名称改变，查询改变名称后的表单是否存在
            $isExists = $this->isFormExists($data['cm_id'], $data['name']);
            if ($isExists) {
                vendor('Zh2py.CUtf8_PY');
                $Zh2py = new CUtf8_PY();
                $data['name'] = $data['name'] . '_' . ($isExists + 1);
                $data['form_id'] = $data['cm_id'] . '_' . $Zh2py->encode($data['name']);
            }
            
            // TODO 查询表单与分组中间表的记录
            $formTempModel = D('formtemp');
            $groupTemp = $formTempModel->getTempByFormId($formId);
            foreach ($groupTemp as $temp) {
                $formIds[] = $temp['form_id'];
            }
            // 如果存在记录，则更新所有记录
            if ($groupTemp) {
                $formTempModel->updateFormIdsByFormId($formIds, $formId);
            }
        }
        
        $where = array(
            'form_id' => $formId
        );
        $result = $this->where($where)->save($data);
        if (! $result['ok'])
            return false;
        return true;
    }

    /**
     * 检查同个企业下同个名称的表单是否存在
     *
     * @param string $cmId
     *            企业ID
     * @param string $name
     *            表单名称
     * @return unknown
     */
    public function isFormExists($cmId, $name)
    {
        $where = array(
            'name' => $name,
            'cm_id' => $cmId
        );
        $result = $this->where($where)->count();
        return $result;
    }

    /**
     * 获取该工作站下所有已发布的公共表单和该用户的个人表单
     * 
     * @param string $cmId
     *            企业ID
     * @param string $creatorId
     *            创建者ID（即用户ID）
     * @return multitype:
     */
    public function getAllPublishForms($cmId, $creatorId)
    {
        $field=[
            '_id',
            'form_id',
            'name',
            'cm_id',
            'create_time',
            'creator_id',
            'status'
        ];
        // TODO 查询已发布的个人表单
        $personWhere = array(
            'cm_id' => $cmId,
            'creator_id' => $creatorId,
            'status' => 2,
            'type' => "1"
        );
        $personResult = $this->field($field)->where($personWhere)->select();
        // TODO 查询已发布的公共表单
        $publicWhere = array(
            'cm_id' => $cmId,
            'status' => 2,
            'type' => '-1'
        );
        $publicResult = $this->field($field)->where($publicWhere)->select();

        $personResult = $personResult? $personResult : [];
        $publicResult = $publicResult? $publicResult : [];
        $result = array_merge($personResult, $publicResult);
        return $result;
    }
    /*查找表单
     * @param $formID   表单ID
     */
    public function selectFormByFormid($formID){
        $where = array(
            'form_id' => $formID
        );
        $result = $this->where($where)->find();
        return $result;
    }

    /*存储表单
     * @param array $formData   表单数据
     */
    public function storeForm($formData){
        $mongo = new \MongoClient();
        $collection_name = 'fx_data_'.$formData['form_id'];
        $db_name = C('DB_MONGO')['DB_NAME'];
        $collection = $mongo->$db_name->$collection_name;
        $result = $collection->insert($formData);
        $mongo->close();
        return $result;
    } 
    //查询表单详情
    /*
     * @param $form_id  表单ID
     * @param $serial   表单流水号  
     */
    public function selectFormDetails($form_id, $serial){
        $mongo = new \MongoClient();
        $collection_name = 'fx_data_'.$form_id;
        $db_name = C('DB_MONGO')['DB_NAME'];
        $collection = $mongo->$db_name->$collection_name;
        $result = iterator_to_array($collection->find(array('serial'=>$serial)),false);
        $mongo->close();
        return $result;
    }
    
    public function getFormByFormid($form_id){
        $mongo = new \MongoClient();
        $db = C('DB_MONGO')['DB_NAME'];
        $collection = $mongo->$db->fx_form;
        $result = iterator_to_array($collection->find(array('form_id' => $form_id),array('field','cm_id')),false);
        $mongo->close();
        return $result[0];
    }

    //获取某个表单的全部填写数据
    public function getFormData($form_id){
        $mongo = new \MongoClient();
        $collection_name = 'fx_data_'.$form_id;
        $db_name = C('DB_MONGO')['DB_NAME'];
        $collection = $mongo->$db_name->$collection_name;
        //$result = iterator_to_array($collection->find(array(),array('submit_head_img','submit_wechat_img','submitter'))->sort(array('submit_time' =>-1)));
        $result = iterator_to_array($collection->find(array())->sort(array('submit_time' =>-1)));
        $mongo->close();
        return $result;
    }

}

