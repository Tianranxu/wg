<?php
/*
* 文件名：FeedbackController.class.php
* 功能：反馈管理控制器
* 作者：XU
* 日期：2015-10-22
* 版权：Copy Right @ 2015 风馨科技 All Rights Reserved
*/

namespace Home\Controller;
use Think\Controller;
use Org\Util\RabbitMQ;

class FeedbackController extends AccessController{
    protected $templateMsgQueue = 'template_msg_queue';
    /*
    * 初始化函数
    */
    public function _initialize(){
        header("Content-Type:text/html;charset=utf-8");
        parent::_initialize();
        $this->feedbackModel = D('feedback');
    }

    /*
    * 反馈管理界面函数
    */
    public function index(){
        $isSystem = I('get.isSystem',-1);
        $compid = I('get.compid','');
        $page = I('get.page',1);
        $content = I('post.content','');
        $status = I('post.status','');
        $flag = I('post.flag','');
        //工作站获取其绑定的物业公司id
        if ($compid)
            $cm_ids = D('Company')->getPropertyCompanyIds($compid);
        $appid = $this->feedbackModel->getAppid($compid);
        $feedbackInfo = $this->feedbackModel->getFeedbackByCompid($compid,$content,$status,($page-1)*10,10,$isSystem,$cm_ids);
        $page_count = ceil(($this->feedbackModel->getFeedbackCount($appid))/10);
        $comp_type = $this->feedbackModel->getCompanyType($compid);
        if ($flag) {
            if ($feedbackInfo) {
                retMessage(true,$feedbackInfo,'','',$page_count);
            }else{
                retMessage(false,null,'无相关数据','无相关数据',4001);
            }
        }
        if (empty($feedbackInfo)&&$page>1) {
            header("HTTP/1.0 404 Not Found");
        }
        $this->assign('isSystem',$isSystem);
        $this->assign('type',$comp_type);
        $this->assign('total',$page_count);
        $this->assign('data',$feedbackInfo);
        $this->assign('compid',$compid);
        $this->display();
    }

    /*
    * 反馈回复函数
    */
    public function response(){
        $compid=I('get.compid','');
        $data['user_id'] = $this->userID;
        $data['fid'] = I('post.fid','');
        $data['content'] = I('post.content','');
        $data['create_time'] = date('Y-m-d H:i:s');
        if ($data['fid']) $result = $this->feedbackModel->addResponse($data);
        if (!$result) retMessage(false,null,'添加失败','添加失败',4001);
        //回复成功后，发送模板消息给用户
        $user = D('feedback')->getUserById($data['fid']);
        $long_id = D('Weixin')->getTemplateId($user['cm_id'], C('TEMPLATE_MSG.FEEDBACK'));
        $url = "http://{$_SERVER['HTTP_HOST']}/WXClient/feedback/umid/{$user['um_id']}/isSystem/{$user['isSystem']}";
        if ($user['cm_type'] == C('COMPANY_TYPE.WORKSTATION')) $url .= "/ws/{$user['ori_cmid']}";
        $temp_data = [
            'compid' => $user['cm_id'],
            'msgtype' => 'property',
            'content' => [
                [
                    'touser' => $user['openid'],
                    'template_id' => $long_id,
                    'url' => $url,
                    'data' => [
                        'first' => ['value' => '您好，您提交的意见反馈有1条新的回复', 'color' => '#173177'],
                        'keyword1' => ['value' => '意见反馈', 'color' => '#173177'],
                        'keyword2' => ['value' => date('Y-m-d H:i:s'), 'color' => '#173177'],
                    ],
                ],
            ],
        ];
        RabbitMQ::publish($this->templateMsgQueue, json_encode($temp_data));
        $updateNoticesResult = A('pushmsg')->readNotices($compid, C('NOTICE_TYPE')[1]['type'], $data['fid']);
        retMessage(true,1);
    }

}
