<?php
/*************************************************
 * 文件名：WarningController.class.php
 * 功能：     预警控制器
 * 日期：     2016.1.22
 * 作者：     fei
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
use Think\Model;
use Org\Util\RabbitMQ;
class WarningController extends AccessController
{
    //报障的消息队列
    private $_set_warning_query = 'set_warning_queue';
    public function _initialize()
    {
        parent::_initialize();
    }

    public function index(){

        //查询合约 账单 房源 预警设置
        $warningMod = M('sys_warning');
        $warning = $warningMod->where(['cm_id'=>$this->companyID])
            ->getField('type,days',true);

        $this->assign('days', $warning);
        $this->display();
    }
    public function set(){

        $type = I('post.type', '');
        $value = I('post.value', '');
        $warningMod = M('sys_warning');
        $warningInCompany = $warningMod->where(['cm_id'=>$this->companyID,'type'=>$type])->find();
        if($warningInCompany){
            $result = $warningMod->where(['cm_id'=>$this->companyID,'type'=>$type])
                ->setField('days', $value);
        }else{
            $result = $warningMod->add([
                'cm_id' => $this->companyID,
                'type' => $type,
                'days' => $value,
                'create_time' => date('Y-m-d H:i:s')
            ]);
        }

        $saveStstus = $result ? 'success': 'fail';
        $saveStstus = $result === 0? 'empty': $saveStstus;
        //推送消息到预警设置队列
        $body = [
            'compid' => $this->companyID,
            'type' => $type
        ];
        RabbitMQ::publish($this->_set_warning_query, json_encode($body));
        exit($saveStstus);
    }
}