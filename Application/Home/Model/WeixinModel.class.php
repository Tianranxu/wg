<?php
/*************************************************
 * 文件名：WeixinModel.class.php
 * 功能：     微信接口模型
 * 日期：     2015.9.1
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;
use DOMDocument;
use ReflectionMethod;

class WeixinFeedback {

    //目录的图文信息模板
    protected $headTpl="<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[%s]]></MsgType><ArticleCount>%d</ArticleCount><Articles>";
    protected $bodyTpl="<item><Title><![CDATA[%s]]></Title><Description><![CDATA[%s]]></Description><PicUrl><![CDATA[%s]]></PicUrl><Url><![CDATA[%s]]></Url></item>";
    protected $tailTpl="</Articles></xml>";
    //文本信息的模板
    protected $textTpl="<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[%s]]></MsgType><Content><![CDATA[%s]]></Content></xml>";
    

    public function text($appId, $postObj) {
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $keyword = trim($postObj->Content);
        switch ($keyword) {
            case 'TESTCOMPONENT_MSG_TYPE_TEXT':
                $msgType = "text";
                $msg = 'TESTCOMPONENT_MSG_TYPE_TEXT_callback';
                $resultStr = sprintf($this->textTpl,$fromUsername,$toUsername,time(),$msgType,$msg);
                return $resultStr;
         }
        if (strpos($keyword, 'QUERY_AUTH_CODE:') === 0) {
            $auth_code = str_replace('QUERY_AUTH_CODE:', '', $keyword);
            $msg = $auth_code.'_from_api';
            $weixinModel = new WeixinModel();
            $component_access_token = $weixinModel->get_component_access_token(C('CHUYUN_APPID'),C('CHUYUN_APPSECRET'));
            $authorization_info = $weixinModel->get_authorization_info($component_access_token,C('CHUYUN_APPID'),$auth_code);
            $access_token = $authorization_info->authorizer_access_token;
            $send = $weixinModel ->customer_service($access_token,$msg,$fromUsername,'text');
            return '';
        }
    }

    public function video($appId, $postObj) {
    }

    public function image($appId, $postObj) {
    }

    public function shortvideo($appId, $postObj) {
    }

    public function voice($appId, $postObj) {
    }

    public function location($appId, $postObj) {
    }

    public function event_click($appId, $postObj) {
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $eventKey=$postObj->EventKey;
        switch ($eventKey) {
            case 'V1001_SERVICE_HALL':
                $msgType = "news";
                $publicnoModel = D('publicno');
                $umid = $publicnoModel->getPublicnoByAppid($appId)['um_id']; 
                $menus = array(
                    array(
                        'title' => '点击进入服务大厅',
                        'description' => '',
                        'picUrl' => 'https://mmbiz.qlogo.cn/mmbiz/OObAtNqedBMdibRq7luQmy41Qya5UVzL2vicEicvPoJ9N8jKwsib8uSWbqBiagvwFaGBc5dwiaPDz6WpIEQ0VaHsOfJA/0?wx_fmt=jpeg',
                        'url' => 'http://' . $_SERVER['HTTP_HOST'] . "/WXClient/index/umid/$umid" 
                    )
                );
                $articleCount = count($menus);
                $resultStr = sprintf($this->headTpl,$fromUsername,$toUsername,time(),$msgType,$articleCount);
                foreach ($menus as $menu) {
                    $resultStr .= sprintf($this->bodyTpl,$menu['title'],$menu['description'],$menu['picUrl'],$menu['url']);
                }
                $resultStr .= $this->tailTpl;
                return $resultStr;

                case 'V1001_SIGN':
                    $msgType = "news";
                    $menu = array(
                        'title' => '点击进行签到',
                        'description' => '',
                        'picUrl' => 'https://mmbiz.qlogo.cn/mmbiz/OObAtNqedBOh1GNwp3jzuYasG4zOXYSc57QneCib8nYiaBya6iajR5Bop0STtXUz22ROF9jHMGhHicDkvibmpMEEeQg/0?wx_fmt=jpeg',
                        'url' => 'http://' . $_SERVER['HTTP_HOST'] . "/WXSign/sign" 
                    );
                    $articleCount = 1;
                    $resultStr = sprintf($this->headTpl,$fromUsername,$toUsername,time(),$msgType,$articleCount);
                    $resultStr .= sprintf($this->bodyTpl,$menu['title'],$menu['description'],$menu['picUrl'],$menu['url']);
                    $resultStr .= $this->tailTpl;
                    return $resultStr;

                case 'V1001_FEEDBACK':
                    $msgType = "text";
                    $content = "请将您的建议直接通过微信发送至后台，我们将会有工作人员跟进处理，谢谢！";
                    $resultStr = sprintf($this->textTpl,$fromUsername,$toUsername,time(),$msgType,$content);
                    return $resultStr;
        }
    }

    public function event_subscribe($appId, $postObj) {
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $msgType = "text";
        $msg = "感谢您的关注~
我们竭诚为广大用户提供便捷、周到的优质服务。
请点击底部【服务大厅】，使用相关功能。 ";
        $resultStr = sprintf($this->textTpl,$fromUsername,$toUsername,time(),$msgType,$msg);
        return $resultStr;
    }

    public function event_unsubscribe($appId, $postObj) {
    }

    public function event_view($appId, $postObj) {
    }

    public function event_location($appId, $postObj) {
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $msgType = "text";
        $msg = $postObj->Event.'from_callback';
        $resultStr = sprintf($this->textTpl,$fromUsername,$toUsername,time(),$msgType,$msg);
        return $resultStr;
    }

    public function event_scan($appId, $postObj) {
    }

    public function event($appId, $postObj) {
        $event=strtolower($postObj->Event);
        $function = new ReflectionMethod(__CLASS__, 'event_'.$event);
        $resultStr = $function->invoke($this, $appId, $postObj);
        return $resultStr;
    }

    public function process($appId, $postObj, $data) {
        //提取后的数据
        $msgType=strtolower($postObj->MsgType);

        $function = new ReflectionMethod(__CLASS__, $msgType);
        return $function->invoke($this, $appId, $postObj);
    }
}
class WeixinModel extends BaseModel{
    
    public function testTicket(){
        $redis=$this->connectRedis();
        $ticket=$redis->get('weixin:ticket');
        if (!$ticket){
            $ticket=file_get_contents('http://wg.szfxhl.com/weixin/index');
            $redis->set('weixin:ticket', $ticket);
        }
        $redis->disconnect();
        return $ticket;
    }
    
    /**
     * https的GET请求
     * @param string $url   url请求地址
     */
    public function http_get($url){
        $oCurl = curl_init ();
        if (stripos ( $url, "https://" ) !== FALSE) {
            curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
            curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, FALSE );
        }
        curl_setopt ( $oCurl, CURLOPT_URL, $url );
        curl_setopt ( $oCurl, CURLOPT_RETURNTRANSFER, 1 );
        $sContent = curl_exec ( $oCurl );
        $aStatus = curl_getinfo ( $oCurl );
        curl_close ( $oCurl );
        if (intval ( $aStatus ["http_code"] ) == 200) {
            $sContent=json_decode($sContent);
            return $sContent;
        } else {
            return false;
        }
    }
    
    /**
     * https的POST请求
     * @param string $url   url请求
     * @param array $parameter  url参数
     */
    public function http_post($url,$parameter){
        $oCurl = curl_init ();
        if (stripos ( $url, "https://" ) !== FALSE) {
            curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
            curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, false );
        }
        curl_setopt ( $oCurl, CURLOPT_URL, $url );
        curl_setopt ( $oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $oCurl, CURLOPT_POST, true );
        curl_setopt ( $oCurl, CURLOPT_POSTFIELDS, $parameter );
        $sContent = curl_exec ( $oCurl );
        $aStatus = curl_getinfo ( $oCurl );
        curl_close ( $oCurl );
        if (intval ( $aStatus ["http_code"] ) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }
    
    /**
     * 接受POST推送加密后的XML消息
     * 1. verify_ticket
     * 2. 取消授权通知
     * @return unknown
     */
    public function decrypt($postStr, $data){
        /*libxml_disable_entity_loader XML是防止外部实体注入,最好的方法是检查自己的xml的有效性*/
        libxml_disable_entity_loader(true);
        //解密消息体
        vendor('WXBizMsgCrypt.wxBizMsgCrypt');
        //实例化微信消息加密解密类
        $wxBizMsgCrypt=new \WXBizMsgCrypt(C('CHUYUN_TOKEN'), C('CHUYUN_ENCODINGAESKEY'), C('CHUYUN_APPID'));
        //TODO 解密消息体
        $decryptMsg = "";
        $errCode = $wxBizMsgCrypt->decryptMsg($data['msg_signature'], $data['timestamp'], $data['nonce'], $postStr, $decryptMsg);
        $redis=$this->connectRedis();
        if ($errCode == 0) {
            $xml_tree = new DOMDocument();
            $xml_tree->loadXML($decryptMsg);
            $infoType = $xml_tree->getElementsByTagName('InfoType')->item(0)->nodeValue;
            if($infoType == "component_verify_ticket"){
                //微信服务器推送component_verify_ticket
                $ticket = $xml_tree->getElementsByTagName('ComponentVerifyTicket')->item(0)->nodeValue;
                $redis->set('weixin:ticket', $ticket);
                return true;
            }else if($infoType == "unauthorized"){
                //微信服务器推送取消授权通知,AuthorizerAppid为公众号ID
                $appid = $xml_tree->getElementsByTagName('AuthorizerAppid')->item(0)->nodeValue;
                //记录取消授权的信息
                $publicno_model = M('publicno');
                $where = array('appid' => $appid);
                $publicno = $publicno_model->field('id')->where($where)->find();
                if ($publicno_model->delete($publicno['id'])) {
                    return true;
                }
            }
        } else {
            $redis->set('weixin:error', $errCode);
        }
        $redis->disConnectRedis();

    }

    /**
     * 验证服务器地址的有效性（维修号使用）
     * @param string $token     Token
     * @param array $data   I('get.')的所有参数 
     */
    public function valid($token,$data){
        $tmpArr = array($token, $data['timestamp'], $data['nonce']);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if($tmpStr == $data['signature']){
            echo $data['echostr'];
            exit;
        }
    }

    /**
     * 公众号被动回复信息
     * @param string $appId     公众号APPID
     * @return boolean
     */
    public function responseWeixinMsg($appId, $data){
        //post数据,可能是由于不同的环境
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        /*libxml_disable_entity_loader XML是防止外部实体注入,最好的方法是检查自己的xml的有效性*/
        libxml_disable_entity_loader(true);
        //解密消息体
        vendor('WXBizMsgCrypt.wxBizMsgCrypt');
        //实例化微信消息加密解密类
        $wxBizMsgCrypt=new \WXBizMsgCrypt(C('CHUYUN_TOKEN'), C('CHUYUN_ENCODINGAESKEY'), C('CHUYUN_APPID'));
        //TODO 解密消息体
        $decryptMsg = "";
        $errCode = $wxBizMsgCrypt->decryptMsg($data['msg_signature'], $data['timestamp'], $data['nonce'], $postStr, $decryptMsg);

        if ($errCode == 0) {
            $postObj = simplexml_load_string($decryptMsg, 'SimpleXMLElement', LIBXML_NOCDATA);
            $feedback = new WeixinFeedback();
            $resultStr = $feedback->process($appId, $postObj, $data);
            $encryptMsg = '';
            $errCode = $wxBizMsgCrypt->encryptMsg($resultStr, $data['timestamp'], $data['nonce'], $encryptMsg);
            if ($errCode == 0) {
                echo $encryptMsg;
            }
        }
    }

    /*
    * 公众号被动回复维修号消息
    */
    public function responseRepairMsg($data){
        //post数据,可能是由于不同的环境
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        /*libxml_disable_entity_loader XML是防止外部实体注入,最好的方法是检查自己的xml的有效性*/
        libxml_disable_entity_loader(true);
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $feedback = new WeixinFeedback();
        $resultStr = $feedback->process($data['appid'], $postObj, $data);
        echo $resultStr;
    }

    /**
    *  客服接口
    *
    */
    public function customer_service($access_token,$content,$touser,$msgType){
        //请求url
        $url='https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$access_token;
        //传递参数
        $parameter='{
            "touser":"'.$touser.'",
            "msgtype":"'.$msgType.'",
            "'.$msgType.'":
            {
                 "content":"'.$content.'"
            }
        }';
        
        //发送http请求
        $result = $this->http_post($url,$parameter);
        return json_decode($result);
    }
    
    /**
     * 请求CODE
     * @param string $appid                         公众号的appid
     * @param string $redirect_uri                重定向地址，需要urlencode，这里填写的应是服务开发方的回调地址
     * @param string $state                          重定向后会带上state参数，开发者可以填写任意参数值，最多128字节
     * @param string $component_appid     服务方的appid，在申请创建公众号服务成功后，可在公众号服务详情页找到
     * @param string $scope                        授权作用域，拥有多个作用域用逗号（,）分隔，默认'snsapi_base'，填'snsapi_base'或'snsapi_userinfo'或'snsapi_base,snsapi_userinfo'
     * @return Ambigous <boolean, mixed>|boolean
     * 1、用户允许授权后，将会重定向到redirect_uri的网址上，并且带上code, state以及appid
     * redirect_uri?code=CODE&state=STATE&appid=APPID
     * 2、若用户禁止授权，则重定向后不会带上code参数，仅会带上state参数
     * redirect_uri?state=STATE
     */
    public function request_code($appid,$redirect_uri,$state,$component_appid,$scope='snsapi_base'){
        //请求url
        $url='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$redirect_uri.'&response_type=code&scope='.$scope.'&state='.$state.'&component_appid='.$component_appid.'#wechat_redirect';
        
        //GET请求接口
        $result=$this->http_get($url);
        if($result){
            //TODO 调用成功
            return $result;
        }else {
            return false;
        }
    }
    
    /**
     * 通过code换取access_token
     * @param string $appid                                     公众号的appid
     * @param string $code                                      填写第一步获取的code参数
     * @param string $component_appid                 服务开发方的appid
     * @param string $component_access_token      服务开发方的access_token
     * @return Ambigous string                               JSON数据
     * access_token     接口调用凭证
     * expires_in          access_token接口调用凭证超时时间，单位（秒）
     * refresh_token    用户刷新access_token
     * openid              授权用户唯一标识
     * scope                用户授权的作用域，使用逗号（,）分隔
     */
    public function exchange_access_token($appid,$code,$component_appid,$component_access_token){
        //请求url
        $url='https://api.weixin.qq.com/sns/oauth2/component/access_token?appid='.$appid.'&code='.$code.'&grant_type=authorization_code&component_appid='.$component_appid.'&component_access_token='.$component_access_token;
        
        //GET请求接口
        $result=$this->http_get($url);
        if ($result){
            //TODO 请求成功
            return $result;
        }else {
            //TODO 请求失败
            return false;
        }
    }
    
    /**
     * 刷新access_token（如果需要）
     * @param string $appid                                     公众号的appid
     * @param string $refresh_token                         填写通过access_token获取到的refresh_token参数
     * @param string $component_appid                  服务开发商的appid
     * @param string $component_access_token       服务开发方的access_token
     * @return Ambigous string                                 JSON数据
     * access_token     接口调用凭证
     * expires_in          access_token接口调用凭证超时时间，单位（秒）
     * refresh_token    用户刷新access_token
     * openid              授权用户唯一标识
     * scope                用户授权的作用域，使用逗号（,）分隔
     */
    public function refresh_access_token($appid,$refresh_token,$component_appid,$component_access_token){
        //请求url
        $url='https://api.weixin.qq.com/sns/oauth2/component/refresh_token?appid='.$appid.'&grant_type=refresh_token&component_appid='.$component_appid.'&component_access_token='.$component_access_token.'&refresh_token='.$refresh_token;
        
        //GET请求
        $result=$this->http_get($url);
        if ($result){
            //TODO 请求成功
            return $result;
        }else {
            //TODO 请求失败
            return false;
        }
    }
    
    /**
     * 通过网页授权access_token获取用户基本信息（需授权作用域为snsapi_userinfo）
     * @param string $access_token      网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
     * @param string $openid                用户的唯一标识
     * @return Ambigous string                JSON数据
     * openid           用户的唯一标识
     * nickname       用户昵称
     * sex                 用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
     * province        普通用户个人资料填写的城市
     * country          国家，如中国为CN
     * headimgurl    用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空。若用户更换头像，原有头像URL将失效。
     * privilege        用户特权信息，json 数组，如微信沃卡用户为（chinaunicom）
     * unionid          只有在用户将公众号绑定到微信开放平台帐号后，才会出现该字段。
     */
    public function get_user_info($access_token,$openid){
        //请求url
        $url="https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$openid}&lang=zh_CN";
        //GET请求
        $result=$this->http_get($url);
        if ($result){
            //TODO 请求成功
            return $result;
        }else {
            //TODO 请求失败
            return false;
        }
    }
    
    /**
     * 获取第三方平台access_token
     * @param string $component_appid                   第三方平台appid
     * @param string $component_appsecret            第三方平台appsecret
     * @param string $component_verify_ticket         微信后台推送的ticket，此ticket会定时推送
     * @return Ambigous string                                  JSON数据
     * component_access_token     第三方平台access_token
     * expires_in                             有效期
     */
    public function get_component_access_token($component_appid,$component_appsecret){
        //连接redis服务器
        $redis = $this->connectRedis();
        //获取redis中的ticket值
        //$component_verify_ticket = $redis->get('weixinTicket'); 
        $tokenFromRedis = $redis->get('component_access_token');
        //暂时使用的ticket取值，后面应该从redis中取出
        $component_verify_ticket = $redis->get('weixin:ticket');
        if (!$tokenFromRedis) {
            //请求url
            $url='https://api.weixin.qq.com/cgi-bin/component/api_component_token';
            //传递参数
            $parameter = array(
                "component_appid" => $component_appid,
                "component_appsecret" => $component_appsecret,
                "component_verify_ticket" => $component_verify_ticket
            );
            
            //POST请求接口
           
            $result = json_decode($this->http_post($url, json_encode($parameter)));
            $redis->setex('component_access_token',7200,$result->component_access_token);
           
        }
        
        //断开连接redis服务器
        $this->disConnectRedis();
        $result = $tokenFromRedis ? $tokenFromRedis : $result->component_access_token;
        if ($result){

            return $result;
        }else {
            return false;
        }
    }
    
    /**
     * 获取预授权码（预授权码用于公众号授权时的第三方平台方安全验证）
     * @param string $component_access_token    第三方平台access_token
     * @param string $component_appid               第三方平台方appid
     * @return Ambigous string                              JSON数据
     * pre_auth_code        预授权码
     * expires_in               有效期，为20分钟
     */
    public function get_pre_auth_code($component_access_token,$component_appid){
        //请求url
        $url='https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token='.$component_access_token;
        //传递参数
        $parameter='{
            "component_appid":"'.$component_appid.'"
        }';
        
        //POST请求接口
        $result = $this->http_post($url, $parameter);
        $result = json_decode($result);
        if($result->pre_auth_code){
            return $result;
        }else{
            return false;
        }
        
    }
    
    /**
     * 使用授权码换取公众号的授权信息
     * @param string $component_access_token    第三方平台access_token
     * @param string $component_appid                第三方平台appid
     * @param string $authorization_code              授权code,会在授权成功时返回给第三方平台
     * @return Ambigous string                              JSON数据
     * authorization_info:              授权信息
     * authorizer_appid                 授权方appid
     * authorizer_access_token     授权方令牌（在授权的公众号具备API权限时，才有此返回值）
     * expires_in                           有效期（在授权的公众号具备API权限时，才有此返回值）
     * authorizer_refresh_token     刷新令牌（在授权的公众号具备API权限时，才有此返回值），刷新令牌主要用于公众号第三方平台获取和刷新已授权用户的access_token，只会在授权时刻提供，请妥善保存。 一旦丢失，只能让用户重新授权，才能再次拿到新的刷新令牌
     * func_info:                           公众号授权给开发者的权限集列表
     */
    public function get_authorization_info($component_access_token,$component_appid,$authorization_code){
        //请求url
        $url='https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token='.$component_access_token;
        
        //传递参数
        $parameter='{
            "component_appid":"'.$component_appid.'",
            "authorization_code":"'.$authorization_code.'"
        }';
        
        //POST请求接口
        $result = json_decode($this->http_post($url, $parameter));
        if ($result->authorization_info){
            return $result->authorization_info;
        }else {
            return false;
        }
    }
    
    /**
     * 刷新授权公众号的令牌
     * @param string $component_access_token        第三方平台access_token
     * @param string $component_appid                    第三方平台appid
     * @param string $authorizer_appid                      授权方appid
     * @param string $authorizer_refresh_token          授权方的刷新令牌，刷新令牌主要用于公众号第三方平台获取和刷新已授权用户的access_token，只会在授权时刻提供，请妥善保存。 一旦丢失，只能让用户重新授权，才能再次拿到新的刷新令牌
     * @return Ambigous string                                  JSON数据
     * authorizer_access_token  授权方令牌
     * expires_in                        有效期，为2小时
     * authorizer_refresh_token  刷新令牌
     */
    public function refresh_authorizer_access_token($component_access_token,$component_appid,$authorizer_appid,$authorizer_refresh_token){
        //请求url
        $url='https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token='.$component_access_token;
        
        //传递参数
        $parameter='{
            "component_appid":"'.$component_appid.'",
            "authorizer_appid":"'.$authorizer_appid.'",
            "authorizer_refresh_token":"'.$authorizer_refresh_token.'"
        }';
        //POST请求接口
        $result=json_decode($this->http_post($url,$parameter));
        if ($result->authorizer_access_token){
            return $result;
        }else if ($result->errcode == 42002) { //若该公众号的refresh_token已过期
            return -1;
        }else{
            return false;
        }
    }
    /**
     * 获取授权公众号的令牌
     *@param string $compid     公司id
     *authorizer_access_token 授权方令牌
     *若返回值为-1，则表示refresh_access_token过期，需要重新授权
     */
    public function get_authorizer_access_token($compid){
        $redis = $this->connectRedis();
        $authorizer_access_token = $redis->get("access_token:authorizer_access_token:$compid");
        //检查授权公众号是否已经超时，若超时则重新获取，否则直接返回
        if (!$authorizer_access_token) {
            $publicno_model = M('publicno');
            $where = array('cm_id' => $compid);
            $authorizer_info = $publicno_model->field(array('id','appid','refresh_token'))->where($where)->find();
            $component_access_token = $this->get_component_access_token(C('CHUYUN_APPID'),C('CHUYUN_APPSECRET'));
            $authorizer_access_token = $this->refresh_authorizer_access_token($component_access_token,C('CHUYUN_APPID'),$authorizer_info['appid'],$authorizer_info['refresh_token']);
            if($authorizer_access_token->authorizer_access_token){
                //将新获得的数据存入数据库中
                $data['id'] = $authorizer_info['id'];
                $data['expires_in'] = $authorizer_access_token->expires_in;
                $data['access_token'] = $authorizer_access_token->authorizer_access_token;
                $data['refresh_token'] = $authorizer_access_token->authorizer_refresh_token;
                $check = $publicno_model->save($data);
                if (!$check) {
                     $authorizer_access_token = -1; 
                }
                //将access_token存入redis中
                $redis->setex("access_token:authorizer_access_token:$compid",7200,$authorizer_access_token->authorizer_access_token);
                $this->disConnectRedis();
                return $data['access_token'];
            }else if ($authorizer_access_token == -1) {
                //若refresh_access_token过期，则控制器应跳转授权界面
                return -1;
            }else{
                return false;
            }
        }else{
            return $authorizer_access_token;
        }
    }
    
    /**
     * 获取授权方的账户信息
     * @param string $component_access_token    第三方平台access_token
     * @param string $component_appid                服务appid
     * @param string $authorizer_appid                  授权方appid
     * @return Ambigous string                              JSON数据
     * authorizer_info              授权方昵称
     * head_img                         授权方头像
     * service_type_info          授权方公众号类型，0代表订阅号，1代表由历史老帐号升级后的订阅号，2代表服务号
     * verify_type_info            授权方认证类型，-1代表未认证，0代表微信认证，1代表新浪微博认证，2代表腾讯微博认证，3代表已资质认证通过但还未通过名称认证，4代表已资质认证通过、还未通过名称认证，但通过了新浪微博认证，5代表已资质认证通过、还未通过名称认证，但通过了腾讯微博认证
     * user_name                   授权方公众号的原始ID
     * alias                             授权方公众号所设置的微信号，可能为空
     * qrcode_url                   二维码图片的URL，开发者最好自行也进行保存
     * authorization_info:      授权信息
     * appid                          授权方appid
     * func_info:                    公众号授权给开发者的权限集列表
     */
    public function get_authorizer_info($component_access_token,$component_appid,$authorizer_appid){
        //请求url
        $url='https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token='.$component_access_token;
        
        //传递参数
        $parameter='{
            "component_appid":"'.$component_appid.'",
            "authorizer_appid":"'.$authorizer_appid.'"
        }';
        
        //POST请求接口
        $result = json_decode($this->http_post($url, $parameter));
        if ($result->authorizer_info){
            return $result->authorizer_info;
        }else {
            return false;
        }
    }
    
    /**
     * 获取授权方的选项设置信息
     * @param string $component_access_token    第三方平台access_token
     * @param string $component_appid               第三方平台appid
     * @param string $authorizer_appid                 授权公众号appid
     * @param string $option_name                       选项名称
     * @return Ambigous string                              JSON数据
     * authorizer_appid     授权公众号appid
     * option_name          选项名称
     * option_value         选项值
     */
    public function get_authorizer_option($component_access_token,$component_appid,$authorizer_appid,$option_name){
        //请求url
        $url='https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_option?component_access_token='.$component_access_token;
        
        //传递参数
        $parameter='{
            "component_appid":"'.$component_appid.'",
            "authorizer_appid":"'.$authorizer_appid.'",
            "option_name":"'.$option_name.'"
        }';
        
        //POST请求接口
        $result=$this->http_post($url, $parameter);
        if ($result){
            //TODO 请求成功
            return $result;
        }else {
            //TODO 请求失败
            return false;
        }
    }
    
    /**
     * 设置授权方的选项信息
     * @param string $component_access_token    第三方平台access_token
     * @param string $component_appid               第三方平台appid
     * @param string $authorizer_appid                 授权公众号appid
     * @param string $option_name                       选项名称
     * @param string $option_value                       设置的选项值，值见微信开放平台文档
     * @return Ambigous string                              JSON数据
     * errcode  错误码
     * errmsg   错误信息
     */
    public function set_authorizer_option($component_access_token,$component_appid,$authorizer_appid,$option_name,$option_value){
        //请求url
        $url='https://api.weixin.qq.com/cgi-bin/component/ api_set_authorizer_option?component_access_token='.$component_access_token;
        
        //传递参数
        $parameter='{
            "component_appid":"'.$component_appid.'",
            "authorizer_appid":"'.$authorizer_appid.'",
            "option_name":"'.$option_name.'",
            "option_value":"'.$option_value.'"
        }';
        
        //POST请求接口
        $result=$this->http_post($url, $parameter);
        if ($result){
            //TODO 请求成功
            return $result;
        }else {
            //TODO 请求失败
            return false;
        }
    }
    /**
     * 获取access_token（测试用）
    */
    public function test_access_token(){
        $baseModel=D('base');
        $redis=$baseModel->connectRedis();
        $cache=$redis->get('access_token:chuyun');
        if ($cache){
            $access_token=$cache;
        }else {
            $access_token=json_decode(file_get_contents('http://testwx.szfxhl.com/weixin/weixin/get_access_token/type/chuyun'))->access_token;
            $redis->setex('access_token:chuyun',7150,$access_token);
        }
        $baseModel->disConnectRedis();
        return $access_token;
    }
    /**
     * 设置公众号的底部菜单栏
     */
    public function set_menu($menu_name='',$menu_url='',$authorizer_access_token,$appid,$umid){
        //请求url
        $url='https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$authorizer_access_token;
        $help_url = 'http://' . $_SERVER['HTTP_HOST'] . '/WXClient/help?umid='.$umid;
        $head_url = 'http://' . $_SERVER['HTTP_HOST'] . '/WXClient/index?umid='.$umid;
        //完善菜单
       if (!($menu_name||$menu_url)) {
            $menu = '{"button":[
                {
                    "type":"view",
                    "name":"首页",
                    "url":"'.$head_url.'"
                },' ;
       }elseif ($menu_name&&!$menu_url) {
            $menu = '{"button":[
                {
                    "type":"view",
                    "name":"'.$menu_name.'",
                    "url":"'.$head_url.'"
                },';
       }elseif ($menu_name&&$menu_url) {
           $menu = '{"button":[
                {
                    "type":"view",
                    "name":"'.$menu_name.'", 
                    "url":"'.$menu_url.'"
                },';
       }
       $menu .= '{
                        "type":"click",
                        "name":"服务大厅",
                        "key":"V1001_SERVICE_HALL"
                      },
                    {
                        "type":"view",
                        "name":"帮助",
                        "url":"'.$help_url.'"
                    }]}'; 
       $result = json_decode($this->http_post($url,$menu));
       
       if ($result->errcode == 0) {
           return 1;
       }else{
            // 根据不同的返回码返回不同的值
            return "$result->errcode".':'."$result->errmsg";
       }
    }
    /**
     * 获取jsapi_ticket微信公众平台 JSSDK 凭证
     * @param string $accessToken                   第三方平台access_token
     */
    public function jsapi_ticket($accessToken){
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$accessToken}&type=jsapi";
        $result = $this->http_get($url);
        return $result;
    }

    /**
     * 获取维修号access_token
     */
    public function getRepairAccessToken(){
        $redis=$this->connectRedis();
        $access_token=$redis->get('repair_access_token');

        if (!$access_token){
            $appId = C('REPAIR_PUBLICNO.APPID');
            $appSecret = C('REPAIR_PUBLICNO.APPSECRET');
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appId}&secret={$appSecret}";
            $result = $this->http_get($url);
            if (!$result->access_token) return false;
            $access_token=$result->access_token;
            $redis->setex('repair_access_token',7000,$access_token);
        }
        $this->disConnectRedis();
        return $access_token;
    }

    /**
    * 将公众号所属的行业设置为IT科技/互联网|电子商务，IT科技/IT软件与服务（为模板消息而用）
    * @param $compid 公司id 
    */
    public function setIndustry($compid){
        $access_token = $this->get_authorizer_access_token($compid);
        $url = 'https://api.weixin.qq.com/cgi-bin/template/api_set_industry?access_token='.$access_token;
        $parameter = [
            'industry_id1' => 1,
            'industry_id2' => 2
        ];
        $result = json_decode($this->http_post($url, json_encode($parameter)));
        return ($result->errcode == 0) ? 1 : "$result->errcode".':'."$result->errmsg";
    }

    /**
    * 获得模板长ID(先检测模板表中是否有该模板，没有则添加，有则直接返回模板长id)
    * @param $compid 物业号绑定的公司id
    * @param $short_id 模板短id
    */
    public function getTemplateId($compid, $short_id, $type=''){
        $type = $type ? $type : C('PUBLICNO_TYPE.PROPERTY');
        $msgModel = D('Templatemsg');
        $check = ($type == C('PUBLICNO_TYPE.PROPERTY')) ? $msgModel->checkTemplate($compid, $short_id) : $msgModel->checkRepairTemplate($short_id);
        if ($check) {
            return $check['long_id'];
        }else{
            $access_token = ($type == C('PUBLICNO_TYPE.PROPERTY')) ? $this->get_authorizer_access_token($compid) : $this->getRepairAccessToken();
            $url = 'https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token='.$access_token;
            $parameter = [
                "template_id_short" => $short_id
            ];
            $result = json_decode($this->http_post($url, json_encode($parameter)));
            if($result->errcode == 0) {
                $msgModel->addTemplate(['cm_id' => $compid, 'short_id' => $short_id, 'long_id' => $result->template_id, 'type' => $type]);
                return $result->template_id;
            }
            return "$result->errcode".':'."$result->errmsg";
        }
    }

    /**
    * 发送模板消息
    * @param $compid 公司id
    * @param array $postData 模板消息数组
    */
    public function setTemplateInfo($compid, $postData){
        $access_token = $this->get_authorizer_access_token($compid);
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$access_token;
        $result = json_decode($this->http_post($url, json_encode($postData)));
        return ($result->errcode == 0) ?  1 : "$result->errcode".':'."$result->errmsg";
    }

}


