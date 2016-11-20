<?php
/*************************************************
 * 文件名：WidgetController.class.php
 * 功能：     控件控制器
 * 日期：     2015.10.20
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;

use Think\Controller;
use Org\Util\RabbitMQ;

class WidgetController extends Controller
{
    // 控件模型
    protected $widgetModel;

    /**
     * 初始化
     */
    protected function _initialize()
    {
        // 实例化控件模型
        $this->widgetModel = D('widget');
    }

    /**
     * 根据控件类型渲染HTML
     *
     * @param array $datas
     *            控件数据
     * @param string $type
     *            控件类型
     * @param boolean $isEdit
     *            是否编辑中
     * @return mixed
     */
    public function processHtml(array $datas, $type = '', $name = '', $insertValue='')
    {
        foreach ($datas as $data) {
            if (! is_array($data)) {
                $function = new \ReflectionMethod(get_called_class(), $datas['type'] . 'Html');
                $result = $function->invoke($this, $datas, $name, $insertValue);
                break;
            }
            $function = new \ReflectionMethod(get_called_class(), $data['type'] . 'Html');
            $result[] = $function->invoke($this, $data, $name, $insertValue);
        }
        return $result;
    }

    /**
     * 渲染文本输入框控件
     *
     * @param array $datas            
     * @param string $name            
     * @return unknown
     */
    public function textHtml(array $datas, $name = '')
    {
        $html = <<<HTML
        <span>{$datas['name']}</span>
        <input type="text" name="{$datas['input_name']}" value="{$datas['default_value']}" placeholder="{$datas['placeholder']}">
HTML;
        $html = htmlspecialchars($html);
        return $html;
    }

    /**
     * 渲染单选框控件
     *
     * @param array $datas            
     * @param string $name            
     * @return string
     */
    public function radioHtml(array $datas, $name = '')
    {
        $html = '<span>' . $datas['name'] . '</span>';
        foreach ($datas['values'] as $k => $value) {
            ;
            $checked = $value == $datas['default_value'] ? 'checked' : '';
            if (! $checked) {
                $html .= '<label class="check_bg"><input type="radio" name="' . $datas['input_name'] . '" value="' . $value . '">' . $value . '</label>';
            } else {
                $html .= '<label><input type="radio" name="' . $datas['input_name'] . '" value="' . $value . '" checked="checked">' . $value . '</label>';
            }
        }
        $html = htmlspecialchars($html);
        return $html;
    }

    /**
     * 分割线控件渲染
     *
     * @param array $datas            
     * @param string $name            
     * @return string
     */
    public function hrHtml(array $datas, $name = '')
    {
        $html = '<hr/>';
        $html = htmlspecialchars($html);
        return $html;
    }

    /**
     * 标签控件渲染
     *
     * @param array $datas            
     * @param string $name            
     * @return string
     */
    public function labelHtml(array $datas, $name = '', $insertValue='')
    {
        if (!$insertValue){
            $html = '<input type="text" name="' . $datas['input_name'] . '" value="' . $datas['default_value'] . '" placeholder="' . $datas['placeholder'] . '" class="input_' . $datas['type'] . '">';
        }else {
            $html = '<input type="text" name="' . $datas['input_name'] . '" value="' . $insertValue . '" placeholder="' . $datas['placeholder'] . '" class="input_' . $datas['type'] . '">';
        }
        $html = htmlspecialchars($html);
        return $html;
    }

    /**
     * 日期控件渲染
     *
     * @param array $datas            
     * @param string $name            
     * @return string
     */
    public function dateHtml(array $datas, $name = '')
    {
        $html = <<<HTML
        <span>{$datas['name']}</span>
        <input type="text" name="{$datas['input_name']}" value="{$datas['default_value']}" class="formDate" placeholder="{$datas['placeholder']}">
HTML;
        $html = htmlspecialchars($html);
        return $html;
    }

    /**
     * 上传控件渲染
     *
     * @param array $datas            
     * @param string $name            
     * @return string
     */
    public function uploadHtml(array $datas, $name = '')
    {
        $html = <<<HTML
        <span>{$datas['name']}</span>
        <i class="fa fa-plus-square-o"></i>
        <input type="hidden"  name="{$datas['input_name']}">
HTML;
        $html = htmlspecialchars($html);
        return $html;
    }

    /**
     * 多行文本框控件渲染
     *
     * @param array $datas            
     * @param string $name            
     * @return boolean
     */
    public function textarea(array $datas, $name = '')
    {
        return false;
    }

    /**
     * 下拉框控件渲染
     *
     * @param array $datas            
     * @param string $name            
     * @return boolean
     */
    public function selectHtml(array $datas, $name = '')
    {
        return false;
    }

    /**
     * 多选框控件渲染
     *
     * @param array $datas            
     * @param string $name            
     * @return boolean
     */
    public function checkboxHtml(array $datas, $name = '')
    {
        return false;
    }
    
    protected $distribute_queue = 'distribute_order_queue';
    protected $catch_queue = 'catch_order_queue';
    protected $restore_queue = 'restore_order_queue';
    protected $shift_queue = 'shift_order_queue';
    protected $evaluate_queue = 'evaluate_order_queue';
    public function testDistributeOrder(){
        //查询报障单
        $faultModel=D('fault');
        $orderInfo=$faultModel->getFaultById(2);
        //派单
        //RabbitMQ::publish($this->distribute_queue, json_encode($orderInfo));
        //接单
        //RabbitMQ::publish($this->catch_queue, json_encode($orderInfo));
        //回单
        //RabbitMQ::publish($this->restore_queue, json_encode($orderInfo));
        //转单
        //RabbitMQ::publish($this->shift_queue, json_encode($orderInfo));
        //评价
        //RabbitMQ::publish($this->evaluate_queue, json_encode($orderInfo));
        dump($orderInfo);exit;
    }

    public function runImgtxtPublishScript()
    {
        dd(D('imgtxt')->imgtxtPublishScript(['is_publish'=>1]));
    }
}


