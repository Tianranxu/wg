<?php
/*************************************************
 * 文件名：FormController.class.php
 * 功能：     表单控制器
 * 日期：     2015.10.20
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;

use Zh2Py\CUtf8_PY;
use Org\Util\RabbitMQ;
class FormController extends AccessController
{

    protected $appid;

    protected $openid;

    protected $widgetModel;

    protected $formModel;

    protected $groupModel;

    protected $templateMsgQueue = 'template_msg_queue';
    /**
     * 初始化
     */
    public function _initialize()
    {
        parent::_initialize();
        header("Content-Type:text/html;charset=utf-8");
        $this->appid = session('appid');
        $this->openid = session('openid');
        $this->widgetModel = D('widget');
        $this->formModel = D('form');
    }

    /**
     * 表单管理界面
     */
    public function index()
    {
        $compid = I('get.compid', '');
        $type = D('company')->selectCompanyAll($compid);
        $formModel = D('form');
        $formtemModel = D('Formtemp');
        $status = I('post.status','');
        $content = I('post.content','');
        $flag = I('post.flag','');
        $user_id = $this->userID;
        $data = $this->formModel->getAllForm($compid,$this->userID,$status,$content);
        if ($flag) {
            if ($data) {
                retMessage(true,$data);
            }else{
                retMessage(false,null,"无相关数据","无相关数据",4001);
            }
        }
        foreach ($data as $k => $v) {
            if ($v['status'] < 0) {
                $forbForms[] = $v;
            }else{
                $forms[] = $v;
            }
        }
        $groups = $formtemModel->getAllGroup($user_id,$compid);
        foreach ($groups as $k => $v) {
            $group_temp[$v['id']] = $v;
        }
        foreach ($forms as $k => $v) {
            $form_ids[] = $v['form_id'];    
            $allForm[$v['form_id']] = $v;
        }
        $form_group = $formtemModel->getFormGroup($form_ids,$user_id);
        foreach ($form_group as $k => $v) {
            $group_form[$v['group_id']][] = $v;
            unset($group_temp[$v['group_id']]);
        }
        //添加表单元素为空的分组
        foreach ($group_temp as $k => $v) {
            $v['group_id'] = $v['id'];
            $group_form[$v['id']][] = $v;
        }
        foreach ($group_form as  $key => $value) {
            foreach ($value as $k => $v) {
                $group_form[$key][$k]['form_info'] = $allForm[$v['form_id']]; 
                unset($allForm[$v['form_id']]);
            }
        }
        $this->assign('type', $type['cm_type']);
        $this->assign('g_form',$group_form);
        $this->assign('groups',$groups);
        $this->assign('forms', $allForm);
        $this->assign('forbForms',$forbForms);
        $this->assign('compid', $compid);
        $this->display();
    }

    /**
    * 表单发布函数
    */
    public function publish_banpick(){
        $form_id = I('post.form_id','');
        $status = I('post.status','');
        $status = -$status;
        $result = $this->formModel->publish_banpick($form_id,$status);
        if ($result) {
            retMessage(true,$result);
        }else{
            retMessage(false,null,"失败","失败",4001);
        }
    }

    /*
    * 表单分组添加函数
    */
    public function add_group(){
        $data['cm_id'] = I('post.compid','');
        $data['title'] = I('post.title','');
        $formtemModel = D('Formtemp');
        if (!$data['cm_id'] || !$data['title']) {
            retMessage(false,null,"未接到数据","未接到数据",4001);
        }
        $data['type'] = 4;
        $data['create_time'] = date("Y-m-d H:i:s");
        $data['user_id'] = $this->userID;
        $data['description'] = '表单自定义分组';
        $result = $formtemModel->addGroup($data);
        if ($result) {
             retMessage(true,$result);
         }else{
            retMessage(false,null,"添加失败","添加失败",4002);
         } 
    }

    /*
    * 表单分组修改函数
    */
    public function change_group(){
        $group_id = I('post.group_id','');
        $form_id = I('post.form_id','');
        $user_id = $this->userID;
        $formtemModel = D('Formtemp');
        if (!$group_id || !$form_id) {
            retMessage(false,null,"未接到数据","未接到数据",4001);
        }
        $result = $formtemModel->changeGroup($group_id,$form_id,$user_id);
        if ($result) {
            retMessage(true,$result);
        }else{
            retMessage(false,null,"修改失败","修改失败",4002);
        }
    }

    /*
    * 表单分组删除函数
    */
    public function del_group(){
        $group_id = I('post.group_id','');
        $formtemModel = D('Formtemp');
        if (!$group_id) {
            retMessage(false,null,"未接到数据","未接到数据",4001);
        }
        $result = $formtemModel->deleteGroup($group_id);
        if ($result) {
            retMessage(true,$result);
        }else{
            retMessage(false,null,"删除失败","删除失败",4002);
        }
    }

    /*
    * 表单分组编辑函数
    */
    public function edit_group(){
        $data['id'] = I('post.group_id','');
        $data['title'] = I('post.title','');
        if (!$data['id'] || !$data['title']) {
            retMessage(false,null,"未接到数据","未接到数据",4001);
        }
        $formtemModel = D('Formtemp');
        $result = $formtemModel->editGroup($data);
        if ($result) {
            retMessage(true,$result);
        }else{
            retMessage(false,null,"编辑失败","编辑失败",4002);
        }
    }

    /*
    * 表单统计函数
    */
    public function statistics(){
        $form_id = I('get.id','');
        $formModel = D('form');
        $userModel = D('user');
        $compid = I('get.compid','');
        $page = I('post.page',1);
        $flag = I('post.flag','');
        $conditions['submitter'] = I('post.submitter','');
        $conditions['submit_time'] = I('post.formDate','');
        $conditions['approval_comment'] = I('post.approval_comment','');
        $conditions['approver'] = I('post.approver','');
        $conditions['approval_status'] = I('post.approval_status','');
        $approver = $userModel->find_user_info($this->userID);
        $data = $formModel->getFormDetail($form_id,($page-1)*10,$conditions);
        if ($flag) {
            if ($data['result']) {
                retMessage(true,$data);
            }else{
                retMessage(false,null,"加载失败","加载失败",4001);
            }
        }
        $this->assign('approver',$approver);
        $this->assign('compid',$compid);
        $this->assign('data',$data);
        $this->assign('form_id',$form_id);
        $this->display();
    }

    /*
    * 表单统计未审核页面
    */
    public function check(){
        $serial = I('request.data','');
        $comment = I('post.comment',''); 
        $compid = I('get.compid','');
        $form_id = I('request.id','');
        $flag = I('post.flag','');
        $status = I('post.status','');
        $formModel = D('form');
        $userModel = D('user');
        $data = $formModel->getOneFormDetail($serial,$form_id);
        $approver = $userModel->find_user_info($this->userID);
        if ($flag) {
            if ($formModel->check($serial,$form_id,$status,$comment,$approver)) {
                //若审核信息保存成功，发送模板消息给用户
                $cm_id = explode('_', $form_id)[0];
                $propertyId = D('WXuser')->getPropertyId($data['openid'])['cm_id'];
                $long_id = D('Weixin')->getTemplateId($propertyId, C('TEMPLATE_MSG.CHECKED_FORM'));
                $umid = D('Publicno')->getPublicnoInfo($propertyId)['um_id'];
                $url = "http://{$_SERVER['HTTP_HOST']}/WXClient/particulars/umid/{$umid}/serial_number/{$data['serial']}/form_id/{$form_id}";
                $temp_data = [
                    'compid' => $propertyId,
                    'msgtype' => 'property',
                    'content' => [
                        [
                            'touser' => $data['openid'],
                            'template_id' => $long_id,
                            'url' => $url,
                            'data' => [
                                'first' => ['value' => '您好，您提交的申请有1条新的反馈', 'color' => '#173177'],
                                'keyword1' => ['value' => $data['form_name'], 'color' => '#173177'],
                                'keyword2' => ['value' => date('Y-m-d H:i:s'), 'color' => '#173177'],
                            ],
                        ],
                    ],
                ];
                RabbitMQ::publish($this->templateMsgQueue, json_encode($temp_data));
                //标息通知为已读
                $otherid = $form_id.','.$serial;
                $noticeResult = A('pushmsg')->readNotices($cm_id, C('NOTICE_TYPE')[2]['type'], $otherid);
                retMessage(true,1);
            }else{
                retMessage(false,null,"存储失败","存储失败",4001);
            }
        }
        $this->assign('compid',$compid);
        $this->assign('data',$data);
        $this->assign('form_id',$form_id);
        $this->display();
    }

    /*
    * 表单已审核页面
    */
    public function checked(){
        $serial = I('get.data','');
        $compid = I('get.compid','');
        $form_id = I('get.id','');
        $formModel = D('form');
        $data = $formModel->getOneFormDetail($serial,$form_id);
        $this->assign('data',$data);
        $this->assign('compid',$compid);
        $this->assign('form_id',$form_id);
        $this->display();
    }

    /**
     * 新建表单页面
     */
    public function form()
    {
        // 接收数据
        $type = I('get.type', 'add');
        
        // 查询控件列表数据
        $datas = $this->widgetModel->getWidget();
        // 实例化控件渲染控制器
        $widgetController = A('Widget');
        // 获取控件列表渲染
        $htmls = $widgetController->processHtml($datas);

        foreach ($datas as $k => $data) {
            $widgetList[$k]['_id'] = $data['_id'];
            $widgetList[$k]['name'] = $data['name'];
            $widgetList[$k]['type'] = $data['type'];
            $widgetList[$k]['input_name'] = $data['input_name'];
            $widgetList[$k]['html'] = htmlspecialchars('<li id="' . $data['_id'] . '">') . $htmls[$k] . htmlspecialchars('<i class="fa fa-times-circle-o right" onclick="removeWidget(this)"></i></li>');
        }
        
        // TODO 编辑表单逻辑
        if ($type == 'edit') {
            // 接收数据
            $formId = I('get.id', '');
            
            // 查询表单的信息
            $formInfo = $this->formModel->getFormInfo($formId);
            // 判断该表单是否已发布
            if ($formInfo['status'] != 1) {
                $this->error('该表单已发布或被禁用，无法编辑！', U('index',array('compid'=>$this->companyID)));
                exit();
            }
            foreach ($formInfo['field'] as $f => $field) {
                $formInfo['widgets'][] = '<li id="' . $field['_id'] . '">' . htmlspecialchars_decode($widgetController->processHtml($field,$field['type'],'',$field['default_value'])) . '<i class="fa fa-times-circle-o right" onclick="removeWidget(this)"></i></li>';
                unset($formInfo['field']);
            }
            $this->assign('formInfo', $formInfo);
        }

        $this->assign('compid', $this->companyID);
        $this->assign('type', $type);
        $this->assign('widgetList', $widgetList);
        $this->display();
    }

    /**
     * 处理表单
     */
    public function doForm()
    {
        // 接收数据
        $getData = array(
            'compid' => I('post.compid', ''),
            'type' => I('post.type', ''),
            'currentFormId' => I('post.currentFormId', ''),
            'field' => I('post.arrs', ''),
            'name' => I('post.name', ''),
            'description' => I('post.description', ''),
            'formType' => I('post.formType', '')
        );
        if (! $getData['compid'] || ! $getData['type'] || ! $getData['field'] || ! $getData['name'] || ! $getData['formType']) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
        
        // 查询控件列表数据
        $widgets = $this->widgetModel->getWidget();
        //获取选中的控件的数据

        foreach ($getData['field'] as $f=>$field){
            foreach ($widgets as $widget){
                if ($field['name']==$widget['_id']){
                    $fields[$f]=$widget;
                    if ($field['value']){
                        $fields[$f]['default_value']=$field['value'];
                    }
                    if ($field['input_name']){
                        $fields[$f]['input_name']=$field['input_name'];
                    }
                    continue;
                }
            }
        }

        // 组装数据
        vendor('Zh2py.CUtf8_PY');
        $Zh2py = new CUtf8_PY();
        $data = array(
            'form_id' => $getData['compid'] . '_' . $Zh2py->encode($getData['name']),
            'name' => $getData['name'],
            'field' => $fields,
            'cm_id' => $getData['compid'],
            'create_time' => date('Y-m-d H:i:s'),
            'creator_id' => $this->userID,
            'status' => 1,
            'type' => $getData['formType'],
            'description' => $getData['description']
        );
        
        // 处理表单
        $result = $this->formModel->doForm($data, $getData['type'], $getData['currentFormId']);
        if (! $result) {
            retMessage(false, null, '表单处理失败', '表单处理失败', 4002);
            exit();
        }
        retMessage(true, null);
        exit();
    }
}

