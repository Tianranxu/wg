<?php
/*************************************************
 * 文件名：WXClientController.class.php
 * 功能：     微信控制器
 * 日期：     2015.9.27
 * 作者：     L
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
use Org\Util\RabbitMQ;

class WXClientController extends ComponentauthorizeController{
    protected $menuModel;
    protected $menuRename;
    protected $templet;
    protected $companyName;
    protected $compName;
    protected $wsid;
    protected $publicName;
    protected $templateMsgQueue = 'template_msg_queue';
    /**
     * 初始化
     */
    public function _initialize(){
        header("Content-Type:text/html;charset=utf-8");
        parent::_initialize();
        $this->menuModel = D('homecompose');
        $tempMod = D('template');
        $mid = I('get.mid');
        $this->openid=session('openid');
        /*$this->appid='wxd49ce6f71c0a2d23';
        $this->openid='oQhSLs35-0BEpfQdbV_e5Avp440c';
        $this->compid=20;*/
        //菜单用户修改名
        $this->menuRename = $this->menuModel->selectMenuRename($mid, $this->appid)['nomen'];
        //查出企业可用模板
        $templet = $tempMod->selectTemplByAppid($this->appid);
        $this->publicName = $templet['pu_name'];
        $this->templet = $templet['style'];
        $this->compName = $templet['shortname'];
        $this->companyName = $templet['shortname'] ? $templet['shortname'] : $templet['cname'];
        $this->wsid = $templet['wsid'];
        $this->assign('templ',$this->templet);
    }
    //首页
    public function index() {
        $menus = $this->menuModel->selectMenusByCompId($this->compid);
        foreach ($menus as &$menu) {
            if($menu['id'] == 22){
                //社区地图
                $menu['link_url'] .= $this->companyName.'/';
                continue;
            }
            if(substr($menu['link_url'],0,4) == 'http'){
            }else{
                $menu['link_url'] .= '/umid/'.$this->umid . '/mid/'.$menu['id'];
            }
            if($menu['type'] == '2'){
                $menu['link_url'] .= '/ws/'.$this->wsid.'/';
            }
        }
        $slides = $this->menuModel->selectSlideByAppId($this->appid);
        $this->assign('menus', $menus);
        $this->assign('slides', $slides);
        $this->assign('templet', $this->templet);
        $this->assign('puName', $this->publicName);
        $this->assign('umid',$this->umid);
        $this->display();
    }

    private function showCategory($cateid,$type) {
        $content = I('get.content','');
        $compid = I('get.ws') ? $this->wsid : $this->compid;
        $this->assign('articles', D('Imgtxt')->getImgtxtsByCategory($cateid, '' ,$content));
        $this->assign('menuRename', $this->menuRename);
        $this->assign('templet', $this->templet);
        $this->assign('content', $content);
        $this->assign('type',$type);
        $this->assign('compid',$compid);
        $this->assign('umid',$this->umid);
        $this->display('article_list');
    }

    private function displayCategoryByCompType($compid, $type) {
        $cateId = D('category')->getCategoryByCompType($compid, $type); //联系我们type类型为5
        $this->showCategory($cateId,$type);
    }

    //公告
    public function notice() {
        $this->displayCategoryByCompType($this->compid, 3); //公告type类型为3
    }
    //联系我们
    public function connectus(){
        $this->displayCategoryByCompType($this->compid, 5); //联系我们type类型为5
    }

    //帮助
    public function help(){
        $this->displayCategoryByCompType($this->compid, 6); //帮助type类型为6
    }

    //图文列表
    public function articles() {
        $type = I('get.type','');
        $this->showCategory(I('get.cateid'),$type);
    }

    //办事
    public function onlineoffice() {
        $feedbackModel = D('feedback');
        $this->displayCategoryByCompType($this->wsid, 7); //办事type类型为7
    }

    //资讯和社区资讯
    public function infos() {
        $cateModel = D('category');
        $compid = I('get.ws') ? $this->wsid : $this->compid;
        $type = I('get.ws') ? 1 : 2;
        $categories = $cateModel->getCateListByCompId($compid, 1, 1); //资讯类型是1
        $this->assign('type',$type);
        $this->assign('categories', $categories);
        $this->assign('menuRename', $this->menuRename);
        $this->assign('templet', $this->templet);
        $this->assign('umid',$this->umid);
        $this->assign('compid',$compid);
        $this->display('category_list');
    }

    //图文详情
    public function detail() {
        $cateModel = D('category');
        $media_id = I('get.mid');
        $imgtxtModel = D('Imgtxt');
        $type = I('get.type','');
        $article = $imgtxtModel->getImgtxtByMediaId($media_id);
        if ($article['form_id']) {
            //若该图文有绑定表单
            $form = D('Form')->getFormInfo($article['form_id'], 2);
            $this->assign('form', $form);   
            if ($type != 7) {
                //若type类型不是办事，显示报名详情
                $applicants = D('Form')->getFormData($article['form_id']);
                foreach ($applicants as $key => $applicant) {
                    $data[$applicant['openid']] = $applicant;
                }
            }
        }
        $imgtxtModel->incViews($media_id);
        $this->assign('applicants',$data);
        $this->assign('liked', D('imgtxttemp')->is_liked($_SESSION['openid'], $media_id));
        $this->assign('article', $article);
        $this->assign('mid', $media_id);
        $this->assign('menuRename', $this->menuRename);
        $this->assign('templet', $this->templet);
        $this->assign('umid',$this->umid);
        $this->display('detail');
    }

    //点赞
    public function like() {
       echo D('imgtxttemp')->like($_SESSION['openid'], I('get.mid'));
    }

    //微服务
    public function micserve() {
        $serveMod = D('serve');
        $serves = $serveMod->selectIsServe($this->compid);
        foreach ($serves as $key=>$serve) {
            if($serve['id'] == 22){
                //社区地图
                $serve['link_url'] = $serve['link_url'].$this->companyName;
                $serves[$key] = $serve;
            }
        }
        $this->assign('serves', $serves);
        $this->assign('menuRename', $this->menuRename);
        $this->assign('templet', $this->templet);
        $this->display();
    }

    //投诉建议、留言和系统反馈
    public function feedback(){
        $isSystem = I('get.isSystem',-1);
        $feedbackModel = D('feedback');
        $compid = I('get.ws') ? $this->wsid : $this->compid;
        $type = I('get.ws') ? 1 : 2;
        $advices = $feedbackModel->getFeedback($compid,$_SESSION['openid'],$isSystem);
        foreach ($advices as $k => $v) {
            $data = $feedbackModel->getPictureAndResByFid($v['id']);
            $advices[$k]['picture'] = $data['picture'];
            $advices[$k]['response'] = $data['response'];
        }
        $this->assign('isSystem',$isSystem);
        $this->assign('type',$type);
        $this->assign('compid',$compid);
        $this->assign('feedback',$advices);
        $this->assign('menuRename', $this->menuRename);
        $this->assign('templet', $this->templet);
        $this->assign('umid',$this->umid);
        $this->display();
    }

    public function submit(){  
        $this->checkInfo();
        $cateModel = D('category');
        $feedbackModel = D('feedback');
        $isSystem = I('get.isSystem');
        $compid = I('get.compid','');
        $data['appid'] = $this->appid;
        $data['openid'] = $_SESSION['openid'];
        $data['content'] = I('post.content','');
        $data['create_time'] = date("Y-m-d H:i:s");
        $data['cm_id'] = $compid;
        $data['isSystem'] = $isSystem;
        $picture = I('post.image','');
        $flag = I('post.flag','');
        $type = I('get.type','');
        if ($flag) {
            $fid = $feedbackModel->add($data);
            $picData = array_filter(explode(',',$picture));
            if ($fid && ($isSystem==-1)) {
                if ($feedbackModel->addPicture($fid,$picData)) {
                    //若反馈添加成功，则开始发送模板消息
                    $users = ($compid == $this->compid) ? D('WXuser')->getUserByType($compid, C('WXUSER_TYPE.FEEDBACK_MG')) : D('WXuser')->getUserByType($compid, C('WXUSER_TYPE.FEEDBACK_MG'), $this->compid);    //找出该公司的反馈管理员
                    if ($users) {
                        $long_id = D('Weixin')->getTemplateId($this->compid, C('TEMPLATE_MSG.FEEDBACK'));
                        foreach ($users as $key => $user) {
                            $temp_data['content'][$key] = [
                                'touser' => $user['openid'],
                                'template_id' => $long_id,
                                'data' => [
                                    'first' => ['value' => '您好，您有1条新的待处理反馈', 'color' => '#173177'],
                                    'keyword1' => ['value' => '意见反馈', 'color' => '#173177'],
                                    'keyword2' => ['value' => date('Y-m-d H:i:s'), 'color' => '#173177'],
                                    'remark' => ['value' => '请您及时前往后台处理用户的意见反馈，谢谢！', 'color' => '#173177'],
                                ],
                            ];
                        }
                        $temp_data['compid'] = $this->compid;    //必须为公众号的compid，用于获取access_token
                        $temp_data['msgtype'] = 'property';
                        RabbitMQ::publish($this->templateMsgQueue, json_encode($temp_data));    
                    }
                }
                $noticeResult = A('pushmsg')->sendNoticesToPcAdministors($compid, C('NOTICE_SEND_TYPE')[C('NOTICE_TYPE')[1]['type']]['name'],$fid);
                retMessage(true,1,'','',2000);
            }else{
                retMessage(false,null,'','',4001);
            }
        }
        $this->assign('isSystem',$isSystem);
        $this->assign('type',$type);
        $this->assign('compid',$compid);
        $this->assign('templet', $this->templet);
        $this->assign('umid',$this->umid);
        $this->display();
    }

    public function submited(){
        $type = I('get.type','');
        $compid = I('get.compid','');
        $isSystem = I('get.isSystem');
        $this->assign('isSystem',$isSystem);
        $this->assign('compid',$compid);
        $this->assign('type',$type);
        $this->assign('templet', $this->templet);
        $this->assign('umid',$this->umid);
        $this->display();
    }

    public function developing(){
        $this->appid;
        $this->assign('templet', $this->templet);
        $this->assign('umid',$this->umid);
        $this->display();
    }

    //网上办事
    public function fillOut(){
        //查看是否有填写用户信息，若没有则跳转到信息填写页面
        $feedbackModel = D('feedback');
        $this->checkInfo();
        $ws_id = I('get.ws','');
        $form_id = I('get.form_id','');
        $formModel = D('form');
        $form_data = $formModel->getFormByFormid($form_id);
        $widgetController = A('Wechatwidget');
        foreach ($form_data['field'] as $field) {
            $field['cm_id'] = $form_data['cm_id'];
            $html = '<li>'.htmlspecialchars_decode($widgetController->processHtml($field,$field['type'],$field['default_value'])).'</li>';
            $form_info[] =$html;
        }
        $this->assign('uploadCompid',explode('_',$form_id)[0]);
        $this->assign('form_html',$form_info);
        $this->assign('form_id',$form_id);
        $this->assign('templet', $this->templet);
        $this->assign('umid',$this->umid);
        $this->display();
    }
    //保存表单(下一步)
    public function keep(){ 
        $WXuserMod = D('person');
        $formMod = D('form');
        $completeMod = D('complete');
        $date = date('Y-m-d H:i:s',time());
        $openid = $_SESSION['openid'];
        $form_id = I('post.form_id');
        //计算流水号
        $forms = $completeMod->selectFormCount();   
        $NO = str_pad($forms,6,"0",STR_PAD_LEFT);
        $NO = substr((string)$NO,0,6);
        $DA = date('Y',time());
        $serial = 'BD'.$DA.$NO;
        //微信用户信息
        $userInfo = $WXuserMod->getUserInfo($openid,$this->compid);
        //查询表单结构
        $f_structure = $formMod->selectFormByFormid($form_id);
        $data = I('post.');
        //删除无用数据
        unset($data['form_id']);
        unset($data['__hash__']);
        //把POST数据中的上传图片分离出来
        $imgURL = array();
        foreach($data as $index=>$var){
            $img = explode('_',$index)[0];
            if($img == 'image'){
                $imgURL[] = $var;
                unset($data[$index]);
            }
        }
        //填入对应数据（mongo）
        $formData = array(
            "openid" => $openid,
            "form_id" => $form_id,
            "form_name" => $f_structure['name'],
            "submitter" => $userInfo['nickname'],
            "submit_mobile" => $userInfo['mobile'],
            "submit_time" =>  $date,
            "submit_head_img" => '',
            "submit_wechat_img" => $userInfo['headimgurl'],
            "attachment" => $imgURL,
            "approval_comment" => "",
            "approval_status" => -1,
            "serial" => $serial
        );

        //保存网页提交过来的数据
        $i=0;
        foreach($f_structure['field'] as $form){
            if($form['type']=='hr'){
                $formData['fields'][$i]['input_name'] = 'hr';
                $formData['fields'][$i]['value'] = '';
                $formData['fields'][$i]['name'] = '分割线';
                $i++;
            }elseif($form['type']=='label'){
                $formData['fields'][$i]['input_name'] = 'label';
                $formData['fields'][$i]['value'] = $form['default_value'];
                $formData['fields'][$i]['name'] = '标签';
                $i++;
            }else{
                foreach($data as $ky=>$va){
                    if($ky==$form['input_name']){
                        $formData['fields'][$i]['input_name'] = $ky;
                        $formData['fields'][$i]['value'] = I('post.'.$ky);
                        $formData['fields'][$i]['name'] = $form['name'];
                    }
                    $i++;
                }
            }           
        }       
        
        //保存记录到数据库（mysql）
        $cm_id = explode('_', $form_id)[0];
        $form_record = array(
            'openid' => $openid,
            'form_id' => $form_id,
            'form_name' => $f_structure['name'],
            'committer' => $userInfo['nickname'],
            'serial' => $serial,
            'cm_id' => $cm_id,
            'create_time' => $date
        );
        $complMod = D('complete');
        $recordID = $complMod->storeFormRecord($form_record);
        //保存记录详情到数据库（mongo）
        $mongo = $formMod->storeForm($formData);
        $users = ($cm_id == $this->compid) ? D('WXuser')->getUserByType($cm_id, C('WXUSER_TYPE.FORM_MG')) : D('WXuser')->getUserByType($cm_id, C('WXUSER_TYPE.FORM_MG'), $this->compid);    //找出该公司的表单管理员
        if ($users) {
            $long_id = D('Weixin')->getTemplateId($this->compid, C('TEMPLATE_MSG.SUBMITED_FORM'));
            //微信端消息通知
            foreach ($users as $key => $user) {
                $temp_data['content'][$key] = [
                    'touser' => $user['openid'],
                    'template_id' => $long_id,
                    'data' => [
                        'first' => ['value' => '您好，您有1条新的待处理表单', 'color' => '#173177'],
                        'keyword1' => ['value' => $f_structure['name'], 'color' => '#173177'],
                        'keyword2' => ['value' => date('Y-m-d H:i:s'), 'color' => '#173177'],
                        'remark' => ['value' => '请您及时前往后台处理用户的表单申请，谢谢！', 'color' => '#173177'],
                    ],
                ];
            }
            $temp_data['compid'] = $this->compid;
            $temp_data['msgtype'] = 'property';
            RabbitMQ::publish($this->templateMsgQueue, json_encode($temp_data));
            //PC端消息通知
            $otherid = $form_id.','.$serial;
            $noticeResult = A('pushmsg')->sendNoticesToPcAdministors($cm_id, C('NOTICE_SEND_TYPE')[3]['name'], $otherid);
        }
        $this->redirect('WXClient/prompt', array('name' => $f_structure['name'], 'umid' => $this->umid), 0, '处理中请稍后......');
    }

    //提示页面
    public function prompt(){
        $form_name = I('get.name');
        $this->assign('name',$form_name);
        $this->assign('templet', $this->templet);
        $this->assign('umid',$this->umid);
        $this->display('prompt');
    }
    //已办事项
    public function complete(){
        $openid = $_SESSION['openid'];
        $cm_id = I('get.compid', '');
        $completeMod = D('complete');
        
        $matters = $completeMod->selectMatterByOpenid($openid, $cm_id);
        foreach($matters as $key=>$var){
            $matters[$key]['create_time'] = substr($var['create_time'],5,5);
        }
        $this->assign('matter',$matters);
        $this->assign('templet', $this->templet);
        $this->assign('umid',$this->umid);
        $this->display();
    }
    //已办详情
    public function particulars(){
        $serial = I('get.serial_number');
        $form_id = I('get.form_id');
        //TODO 查询mongo fx_data_$compid
        $formMod = D('form');
        //查询已填表单详情
        $details = $formMod->selectFormDetails($form_id, $serial);
        //提取附件
        $image = $details[0]['attachment'];
        $images = array();
        foreach($image as $src){ 
            $fileName = basename($src);
            $fileDir = dirname($src);
            $images[] = array(
                'original' => $src,
                'thumbnail' => $fileDir.'/thumbnail/'.$fileName
            );
        }

        $this->assign('details', $details[0]);
        $this->assign('images', $images);
        $this->assign('templet', $this->templet);
        $this->assign('umid',$this->umid);
        $this->display();
    }

    public function faultList(){
        $WXFaultController = A('WXFault');
        $this->assign('umid',$this->umid);
        $WXFaultController->faultList(1,$this->openid);
    }

    //检查用户信息,若没有相关信息则跳转填写
    public function checkInfo(){
        $check = D('feedback')->getUser($this->openid)['mobile'];
        if (!$check && ACTION_NAME != 'info') {
            $url = "/person/info/umid/" . $this->umid;
            $this->assign('url', $url);
        }
    }
}

