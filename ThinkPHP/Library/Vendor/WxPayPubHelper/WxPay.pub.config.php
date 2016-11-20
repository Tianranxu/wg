<?php
/**
* 	配置账号信息
*/

class WxPayConf_pub
{
	//=======【基本信息设置】=====================================
	//微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看
	public $appId;
	//受理商ID，身份标识
	public $mchId;
	//商户支付密钥Key。审核通过后，在微信发送的邮件中查看
	public $key;
	//JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看
	public $appSecret;
	
	//=======【JSAPI路径设置】===================================
	//获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面
	const JS_API_CALL_URL = 'http://testwx.szfxhl.com/paytest/index/index?showwxpaytitle=1';
	
	//=======【证书路径设置】=====================================
	//证书路径,注意应该填写绝对路径
	public $sslCertPath = './cacert/apiclient_cert.pem';
	public $sslKeyPath = './cacert/apiclient_key.pem';
	
	//=======【异步通知url设置】===================================
	//异步通知url，商户根据实际开发过程设定
	public $notifyUrl;

	//=======【curl超时设置】===================================
	//本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
	public $curlTimeout=30;
	
	public function __construct($appId,$mchId,$key,$notifyUrl='',$curlTimeout=30,$appSecret=''){
	    $this->appId=$appId;
	    $this->mchId=$mchId;
	    $this->key=$key;
	    $this->notifyUrl=$notifyUrl;
	    $this->curlTimeout=$curlTimeout;
	    $this->appSecret;
	}
}
	
?>