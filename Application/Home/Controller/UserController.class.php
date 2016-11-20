<?php
/*************************************************
 * 文件名：UserController.class.php
 * 功能：     用户管理控制器
 * 日期：     2015.7.23
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Controller;
use Think\Controller;
use TemplateSMS\CCPRestSmsSDK;

class UserController extends Controller{
    protected $_userModel;
    protected $userId;
    
    /**
     * 初始化
     */
    public function _initialize(){
        //判断是否有登陆状态
        $is_login=session('user_id');
        if ($is_login){
            //TODO 已登录，跳转到企业管理
            //获取用户ID
            $this->userId=$is_login;
            //判断当前是否为登陆界面
            if (stristr(__ACTION__, 'login')){
                //自动跳转到企业管理
                $this->redirect('company/index');
            }
        }else {
            //判断当前是否为登陆、注册/忘记密码、注册\重置密码成功界面
            if (stristr(__ACTION__, 'user_setting') || stristr(__ACTION__, 'user_info') || stristr(__ACTION__, 'change_pass') || stristr(__ACTION__, 'change_face')){
                //跳回登陆页面
                $this->redirect('login');
            }
        }
        
        $this->_userModel=D('user');
    }
    
    /**
     * 验证码
     */
    public function verify(){
        //生成验证码
        $config=array(
            'fontttf'=>'4.ttf',
            'length'=>4     //验证码位数
        );
        $verify=new \Think\Verify($config);
        $verify->entry();
    }
    
    /**
     * 登陆页面
     */
    public function login(){
        $domain = I('get.d', 'www');
        $logo = D('invite_code')->logo($domain);
        $this->assign('logo', $logo);
        $this->display();
    }
    
    /**
     * 注册页面或重置临时密码页面
     */
    public function reg_rest(){
        $this->display();
    }
    
    /**
     * 服务条款页面
     */
    public function service_term(){
        $this->display();
    }
    
    /**
     * 注册或重置密码成功页面
     */
    public function success(){
        //接收数据
        $id=isset($_GET['id'])?$_GET['id']:'';
        $type=isset($_GET['type'])?$_GET['type']:'';
        
        //根据用户ID查询该用户的基础信息
        $result=$this->_userModel->find_user_info($id);
        if ($result && $result['code']){
            $this->assign('mobile',$result['code']);
        }
    
        if ($type==1 && $id){
            //TODO 注册成功
            $this->assign('title','注册成功');
        }elseif ($type==2 && $id){
            //TODO 重置密码成功
            $this->assign('title','密码发送成功');
        }
        
        $this->assign('id',$id);
        $this->display();
    }
    
    /**
     * 用户设置页面
     */
    public function user_setting(){
        //根据用户ID查询用户资料
        $userInfo=$this->_userModel->find_user_info($this->userId);
        $userPhotoUrl=$this->_userModel->find_user_photo($userInfo['photo'])['url_address'];
        
        //查询所有用户头像
        $faceList=$this->_userModel->get_user_face_list();
        
        $this->assign('id',$this->userId);
        $this->assign('name',$userInfo['name']);
        $this->assign('photo',$userPhotoUrl);
        $this->assign('faceList',$faceList);
        $this->display();
    }
    
    /**
     * 修改密码页面
     */
    public function change_pass(){
        $this->assign('id',$this->userId);
        $this->display();
    }
    
    /**
     * 修改头像页面
     */
    public function change_face(){
        //查询用户当前头像
        $photoId=$this->_userModel->find_user_info($this->userId)['photo'];
        if ($photoId){
            $userPhotoUrl=$this->_userModel->find_user_photo($photoId)['url_address'];
            $userPhoto=array($photoId,$userPhotoUrl);
        }
        
        //查询所有用户头像
        $faceList=$this->_userModel->get_user_face_list();
        
        $this->assign('userFace',$userPhoto);
        $this->assign('faceList',$faceList);
        $this->display();
    }
    
    /**
     * 用户资料页面
     */
    public function user_info(){
        //根据用户ID查询用户资料
        $userInfo=$this->_userModel->find_user_info($this->userId);
        //判断用户是否有头像
        if ($userInfo['photo']){
            $userPhotoUrl=$this->_userModel->find_user_photo($userInfo['photo'])['url_address'];
            $userPhoto=array($userInfo['photo'],$userPhotoUrl);
        }
        
        //查询所有用户头像
        $faceList=$this->_userModel->get_user_face_list();
        
        $this->assign('userInfo',$userInfo);
        $this->assign('userFace',$userPhoto);
        $this->assign('faceList',$faceList);
        $this->display();
    }
    
    /**
     * 执行登陆
     */
    public function do_login(){
        //接收数据
        $phone=isset($_POST['phone'])?$_POST['phone']:'';
        $pw=isset($_POST['pw'])?md5($_POST['pw']):'';
        $auto=isset($_POST['auto'])?$_POST['auto']:'';

        if ($phone!='' && $pw!=''){
            //检查手机号码和密码是否正确
            $result=$this->_userModel->check_login($phone,$pw);
            if ($result){
                //写入最后登录的ip
                $ip=$_SERVER['REMOTE_ADDR'];
                $ipResult=$this->_userModel->upLastLoginIp($phone,$ip);
                if ($ipResult!=C('OPERATION_SUCCESS')){
                    retMessage(false,null,'最后登录ip地址和时间更新失败','最后登录ip地址和时间更新失败',4003);
                    exit;
                }
                
                //获取当前PHPSESSID
                $sessionId=session_id();
                //将PHPSESSID写入用户表
                $sessionResult=$this->_userModel->updateSession($result['id'],$sessionId);
                if (!$sessionResult){
                    retMessage(false,null,'PHPSESSID写入失败','PHPSESSID写入失败',4004);
                    exit;
                }
                //将userID写入session
                session('user_id',$result['id']);
                
                //登陆成功
                //TODO 判断是否勾选自动登陆
                if ($auto==1){
                    //将sessionID存入，生命周期1年
                    cookie('PHPSESSID',session_id(),30758400);
                }
                retMessage(true,null);
                exit;
            }else {
                //TODO 手机号码或者密码不正确
                retMessage(false,null,'手机号码或密码不正确','手机号码或密码不正确',4002);
                exit;
            }
        }
    }
    
    /**
     * 用户登出
     */
    public function logout(){
        //接收数据
        $id=isset($_POST['id'])?$_POST['id']:'';

        if(!$id){
            retMessage(false,null,'缺少用户ID','缺少用户ID',4001);
            exit;
        }
        //清除登录状态
        $cleanSessionId=$this->_userModel->cleanSessionId($id);
        if(!$cleanSessionId){
            retMessage(false,null,'登出失败','登出失败',4002);
            exit;
        }
        cookie('PHPSESSID',null);
        session(null);
        retMessage(true,null);
        exit;
    }
    
    /**
     * 验证码检测
     */
    public function checkVerify(){
        //接收数据
        $verifyCode=isset($_POST['verifyCode'])?$_POST['verifyCode']:'';
        
        if ($verifyCode){
            //实例化验证码类
            $verify=new \Think\Verify();
            $result=$verify->check($verifyCode);
            if (!$result){
                retMessage(false,null,'验证码错误','验证码错误',4002);
                exit;
            }
            retMessage(true,null);
            exit;
        }else {
            retMessage(false,null,'接收不到数据','接收不到数据',4001);
            exit;
        }
    }
    
    /**
     * 检查手机号码是否重复
     */
    public function check_mobile(){
        //接收数据
        $phone=isset($_POST['mobile'])?$_POST['mobile']:'';
        
        if ($phone!=''){
            //检查手机号码是否存在
            $result=$this->_userModel->check_mobile($phone);
            if (!$result){
                //TODO 手机号码没有重复
                retMessage(true,null);
                exit;
            }else {
                //TODO 手机号码重复
                retMessage(false,$result['id']);
                exit;
            }
        }else {
            exit;
        }
    }
    
    /**
     * 写入注册信息
     */
    public function doReg(){
        //接收数据
        $phone=isset($_POST['mobile'])?$_POST['mobile']:'';
        
        if ($phone){
            //生成临时密码
            $temp_password=mt_rand(0, 9).mt_rand(0, 9).mt_rand(0, 9).mt_rand(0, 9).mt_rand(0, 9).mt_rand(0, 9);
            //获取用户IP
            $ip=$_SERVER['REMOTE_ADDR'];
            
            if ($temp_password && $ip){
                //将注册信息写入用户表
                $result=$this->_userModel->addReg($phone,md5($temp_password),$ip);
                if ($result[0]==5){
                    $return=array($result[1],$temp_password);
                    //TODO 注册信息写入成功
                    retMessage(true,$return);
                    exit;
                }else {
                    //TODO 注册信息写入失败
                    retMessage(false,null);
                    exit;
                }
            }
        }else {
            exit;
        }
    }
    
    /**
     * 查询用户的固定密码或临时密码是否正确
     */
    public function checkNowPass(){
        //接收数据
        $id=isset($_POST['id'])?$_POST['id']:'';
        $pass=isset($_POST['pass'])?$_POST['pass']:'';
        
        if ($id && $pass){
            //检查密码
            $result=$this->_userModel->check_now_pass($id,$pass);
            //TODO 密码不正确
            if ($result!=2){
                retMessage(false,null,'密码不正确','密码不正确',4003);
                exit;
            }
            //TODO 密码正确
            retMessage(true,null);
            exit;
        }else {
            retMessage(false,null,'未接收到数据','未接收到数据',4002);
            exit;
        }
    }
    
    /**
     * 修改用户密码
     */
    public function doChangePass(){
        //接收数据
        $id=isset($_POST['id'])?$_POST['id']:'';
        $pass=isset($_POST['pass'])?$_POST['pass']:'';
    
        if ($id && $pass){
            //修改密码
            $result=$this->_userModel->change_user_pass($id,$pass);
            //TODO 修改密码失败
            if ($result!=2){
                retMessage(false,null,'修改密码失败','修改密码失败',4003);
                exit;
            }
            //TODO 修改密码成功
            //清空登陆状态
            cookie('login_status',null);
            retMessage(true,null);
            exit;
        }else {
            retMessage(false,null,'未接收到数据','未接收到数据',4002);
            exit;
        }
    }
    
    /**
     * 更新用户资料
     */
    public function doEdit(){
        //接收数据
        $id=isset($_POST['id'])?$_POST['id']:'';
        $name=isset($_POST['name'])?$_POST['name']:'';
        $contact_number=isset($_POST['contact_number'])?$_POST['contact_number']:'';
        $sex=isset($_POST['sex'])?$_POST['sex']:'';
        $mail=isset($_POST['mail'])?$_POST['mail']:'';
        $qq=isset($_POST['qq'])?$_POST['qq']:'';
        $address=isset($_POST['address'])?$_POST['address']:'';
        $remark=isset($_POST['remark'])?$_POST['remark']:'';
        
        if ($id){
            //组装更新数据
            $data=array(
                'id'=>$id,
                'name'=>$name,
                'Contact_number'=>$contact_number,
                'sex'=>$sex,
                'mail'=>$mail,
                'QQ'=>$qq,
                'address'=>$address,
                'remark'=>$remark
            );
            //TODO 更新用户资料
            $result=$this->_userModel->edit_user_info($id,$data);
            if ($result!=3){
                //TODO 更新失败
                retMessage(false,null,'更新失败','更新失败',4003);
                exit;
            }
            //TODO 更新成功
            retMessage(true,null);
            exit;
        }else {
            retMessage(false,null,'缺少用户ID','缺少用户ID',4002);
            exit;
        }
    }
    
    /**
     * 发送临时密码短信
     */
    public function sendTempPass(){
        //接收数据
        $id=isset($_POST['id'])?$_POST['id']:'';
        $phone=isset($_POST['mobile'])?$_POST['mobile']:'';
        $temp_pass=isset($_POST['temp_pass'])?$_POST['temp_pass']:'';
        $type=isset($_POST['type'])?$_POST['type']:'';
        
        //判断该用户是否超过发送短信次数
        $timesResult=$this->_userModel->check_sms_times($id);
        if ($timesResult[0]!=3){
            retMessage(false,null,'该用户当天超过短信发送次数限制','该用户当天超过短信发送次数限制',4004);
            exit;
        }
        
        //设置临时密码生效时间
        $time=5;
        //加载短信平台插件
        vendor("TemplateSMS.CCPRestSmsSDK");
        //实例化REST
        $SendTemplateSMS=new CCPRestSmsSDK(ACCOUNT_SID, AUTH_TOKEN, SMS_CHUYUN_APPID);
        
        if ($type==1 && $id && $phone && $temp_pass){
            //注册或重置密码时发送的短信
            $temp_pass=$_POST['temp_pass'];
        }elseif ($type==2 && $phone && $id) {
            //重新发送
            $temp_pass=mt_rand(0, 9).mt_rand(0, 9).mt_rand(0, 9).mt_rand(0, 9).mt_rand(0, 9).mt_rand(0, 9);
            //将新的临时密码写入用户表
            $editResult=$this->_userModel->resetTempPass($id,$phone,md5($temp_pass));
            if ($editResult!=2){
                retMessage(false,null,'临时密码更新失败','临时密码更新失败',4003);
                exit;
            }
        }
        
        //开始发送临时密码短信
        if (($phone && $temp_pass && $type) || $id){
            $result=$SendTemplateSMS->sendTemplateSMS($phone, array($temp_pass), SMS_TEMPLETE);
            //$result->statusCode=0;
            if ($result->statusCode!=0){
                //TODO 临时密码短信发送失败
                retMessage(false,null,'临时密码短信发送失败','临时密码短信发送失败',4002);
                exit;
            }else {
                //TODO 临时密码发送成功
                //该用户发送短信次数递增一
                $addTimesResult=$this->_userModel->increase_sms_times($id);
                if ($addTimesResult!=3){
                    retMessage(false,null,'短信发送次数递增失败','短信发送次数递增失败',4005);
                    exit;
                }
                retMessage(true,null);
                exit;
            }
        }else {
            retMessage(false,null,'未接收到数据','未接收到数据',4002);
            exit;
        }
    }
    
    /**
     * 更新用户头像
     */
    public function changeUserFace(){
        //接收数据
        $photo=isset($_POST['photo'])?$_POST['photo']:'';
        
        if ($photo){
            //查询用户头像ID
            $userPhotoId=$this->_userModel->find_user_info($this->userId);
            //TODO 如果用户头像没有改变，更新成功
            if ($userPhotoId['photo']==$photo){
                //TODO 更新成功
                retMessage(true,null);
                exit;
            }
            
            //更新用户头像
            $result=$this->_userModel->change_user_face($this->userId,$photo);
            if ($result!=2){
                //TODO 更新失败
                retMessage(false,null,'更新头像失败','更新头像失败',4003);
                exit;
            }
            //TODO 更新成功
            retMessage(true,null);
            exit;
        }else {
            retMessage(false,null,'未接收到数据','未接收到数据',4002);
            exit;
        }
    }
    
}


