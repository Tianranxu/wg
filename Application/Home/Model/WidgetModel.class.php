<?php
/*************************************************
 * 文件名：WidgetModel.class.php
 * 功能：     控件模型
 * 日期：     2015.10.21
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;

use Think\Model\MongoModel;

class WidgetModel extends MongoModel
{

    protected $connection = 'DB_MONGO';

    protected $tableName = 'widget';

    /**
     * 查询控件
     * 
     * @param string $type
     *            控件类型
     * @param string $name
     *            控件名称
     * @return \Think\mixed
     */
    public function getWidget($type = '', $name = '')
    {
        if ($type)
            $where['type'] = $type;
        if ($name)
            $where['name'] = $name;
        $result = array_values($this->where($where)->select());
        $sql = $this->getLastSql();
        return $result;
    }

    /**
     * 添加控件
     *
     * @param string $type
     *            控件类型
     * @param array $data
     *            控件数据
     * @return boolean|\Think\Model\mixed
     */
    public function addWidget($type, array $data)
    {
        if (! $type || ! $data)
            return false;
        
        $function = new \ReflectionMethod(__CLASS__, $type . '_data');
        $addData = $function->invoke($this, $type, $data);
        $result = $this->add($data);
        return $result;
    }

    /**
     * 编辑控件
     *
     * @param string $type
     *            控件类型
     * @param array $data
     *            控件数据
     * @return boolean|Ambigous <boolean, unknown>
     */
    public function editWidget($type, array $data)
    {
        if (! $type || ! $data)
            return false;
        
        $where = array(
            'type' => $type
        );
        $function = new \ReflectionMethod(__CLASS__, $type . '_data');
        $editData = $function->invoke($this, $type, $data);
        $result = $this->where($where)->save($editData);
        return $result;
    }

    /**
     * 删除控件
     *
     * @param string $type
     *            控件类型
     * @return boolean
     */
    public function delWidget($type)
    {
        $where = array(
            'type' => $type
        );
        $result = $this->where($where)->delete();
        if (! $result['n'])
            return false;
        return true;
    }

    /**
     * 文本输入框返回的值
     *
     * @param string $type            
     * @param array $data            
     * @return multitype:unknown
     */
    public function text_data($type, array $data)
    {
        return $data = array(
            'type' => $type,
            'name' => $data['name'],
            'input_name' => $data['input_name'],
            'values' => $data['values'],
            'default_value' => $data['default_value'],
            'placeholder' => $data['placeholder']
        );
    }

    /**
     * 单选框返回的值
     *
     * @param string $type            
     * @param array $data            
     * @return multitype:unknown
     */
    public function radio_data($type, array $data)
    {
        return $data = array(
            'type' => $type,
            'name' => $data['name'],
            'input_name' => $data['input_name'],
            'values' => $data['values'],
            'default_value' => $data['default_value']
        );
    }

    /**
     * 多选框返回的值
     *
     * @param string $type            
     * @param array $values            
     * @return multitype:unknown
     */
    public function checkbox_data($type, array $data)
    {
        return $data = array(
            'type' => $type,
            'name' => $data['name'],
            'input_name' => $data['input_name'],
            'values' => $data['values'],
            'default_value' => $data['default_value']
        );
    }

    /**
     * 下拉框返回的值
     *
     * @param string $type            
     * @param array $values            
     * @return multitype:unknown
     */
    public function select_data($type, array $data)
    {
        return $data = array(
            'type' => $type,
            'name' => $data['name'],
            'input_name' => $data['input_name'],
            'values' => $data['values'],
            'default_value' => $data['default_value']
        );
    }

    /**
     * 多行文本框返回的值
     *
     * @param string $type            
     * @param array $data            
     * @return multitype:unknown
     */
    public function textarea_data($type, array $data)
    {
        return $data = array(
            'type' => $type,
            'name' => $data['name'],
            'input_name' => $data['input_name'],
            'values' => $data['values'],
            'default_value' => $data['default_value'],
            'placeholder' => $data['placeholder']
        );
    }

    /**
     * 标签返回的值
     *
     * @param string $type            
     * @param array $data            
     * @return multitype:unknown
     */
    public function label_data($type, array $data)
    {
        return $data = array(
            'type' => $type,
            'name' => $data['name'],
            'input_name' => $data['input_name']
        );
    }

    /**
     * 分割线返回的值
     *
     * @param string $type            
     * @param array $data            
     * @return multitype:unknown
     */
    public function hr_data($type, array $data)
    {
        return $data = array(
            'type' => $type,
            'name' => $data['name'],
            'input_name' => $data['input_name']
        );
    }

    /**
     * 日期控件返回的值
     *
     * @param string $type            
     * @param array $data            
     * @return multitype:unknown
     */
    public function date_data($type, array $data)
    {
        return $data = array(
            'type' => $type,
            'name' => $data['name'],
            'input_name' => $data['input_name'],
            'values' => $data['values'],
            'default_value' => $data['default_value']
        );
    }
}


