<?php
/**
 * 文件名：PublicnoController.class.php
 * 功能：公众号管理控制器
 * 作者：XU
 * 日期：2015-09-02
 * 版权：CopyRight @2015 风馨科技 All Rights Reserved
 */
namespace Home\Controller;

use Home\Controller\AccessController;
use Predis\Client;
use Org\Util\RabbitMQ;
class PublicnoController extends AccessController{
    protected $userQueue = 'get_user_queue';
    protected $imgtxtQueue = 'get_imgtxt_queue';
    protected $publicnoModel;

    /*
     * 初始化函数
     */
    public function _initialize()
    {
        header("Content-Type:text/html;charset=utf-8");
        parent::_initialize();
        $this->publicnoModel = D('publicno');
    }

    /**
     * 管理界面函数
     */
    public function index()
    {
        $compid = I('get.compid');
        // 获取企业信息
        $companyModel = D('Company');
        $company = $companyModel->selectCompanyDetail($compid);
        
        $this->assign('flag', $flag);
        $this->assign('companyName', $company['name']);
        $this->assign('compid', $compid);
        $this->display();
    }

    /**
     * 公众号接入函数
     */
    public function access()
    {
        // 获取公司id
        $compid = I('get.compid', '');
        
        if (I('post.flag')) {
            $weixinModel = D('Weixin');
            // 获取第三方access_token
            $access_token = $weixinModel->get_component_access_token(C('CHUYUN_APPID'), C('CHUYUN_APPSECRET'));
            if (! $access_token) {
                retMessage(false, null, "获取不到第三方access_token", "", 4001);
                exit();
            }
            
            // 获取第三方预授权码
            $pre_auth_code = $weixinModel->get_pre_auth_code($access_token, C('CHUYUN_APPID'));
            if (! $pre_auth_code) {
                retMessage(false, null, "获取不到预授权码", "", 4002);
                exit();
            }
            
            // 组装ajax的返回值
            $data['component_appid'] = C('CHUYUN_APPID');
            $data['pre_auth_code'] = $pre_auth_code->pre_auth_code;
            $data['redirect_uri'] = "http://{$_SERVER['HTTP_HOST']}/publicno/authrized";
            retMessage(true, $data, "", "", 2000);
        }
        
        session('compid', $compid); // 将公司id写入session
        $this->assign('compid', $compid);
        $this->display();
    }

    /**
     * 公众号接入函数
     */
    public function authrized(){
        $auth_code = I('get.auth_code','');
        if ($auth_code) {
            $compid = session('compid');
            $weixinModel = D('Weixin');
            $publicnoModel = D('publicno');
            // 获取授权信息
            $component_access_token = $weixinModel->get_component_access_token(C('CHUYUN_APPID'), C('CHUYUN_APPSECRET'));
            $authorization_info = $weixinModel->get_authorization_info($component_access_token, C('CHUYUN_APPID'), $auth_code);
            
            // 获取公众号基本信息
            $authorizer_info = $weixinModel->get_authorizer_info($component_access_token, C('CHUYUN_APPID'), $authorization_info->authorizer_appid);
            if ($authorization_info && $authorizer_info) {
                // 查看该公众号是否已有绑定企业
                $whereCompany = array(
                    'appid' => $authorization_info->authorizer_appid,
                    'isCancel' => - 1
                );
                $check = $publicnoModel->field('id')
                    ->where($whereCompany)
                    ->find();
                if ($check) {
                    $data['id'] = $check['id'];
                    $data['refresh_token'] = $authorization_info->authorizer_refresh_token;
                    $publicnoModel->save($data);
                    $this->error("该公众号已有绑定企业，请勿重复绑定！", U('publicno/access', array(
                        'compid' => $compid
                    )));
                }
                // 将授权公众号的access_token传入redis中
                vendor('Redis.autoload');
                $this->_redis = new Client(array(
                    'host' => C('REDIS_HOST'),
                    'port' => C('REDIS_PORT'),
                    'database' => C('REDIS_DB')
                ));
                $this->_redis->set("auth_code:$authorization_info->authorizer_appid", $auth_code);
                $this->_redis->setex("access_token:authorizer_access_token:$compid", 7200, $authorization_info->authorizer_access_token);
                $this->_redis->quit();
                $checkAuthor = $publicnoModel->getPublicnoByAppid($authorization_info->authorizer_appid);
                // 将公众号内的信息存入数据库中
                $data['appid'] = $authorization_info->authorizer_appid;
                $data['access_token'] = $authorization_info->authorizer_access_token;
                $data['expires_in'] = $authorization_info->expires_in;
                $data['head_img'] = $authorizer_info->head_img;
                $data['authorizer_info'] = $authorizer_info->nick_name;
                $data['service_type_info'] = $authorizer_info->service_type_info->id;
                $data['user_name'] = $authorizer_info->user_name;
                $data['alias'] = $authorizer_info->alias;
                $data['qrcode_url'] = $authorizer_info->qrcode_url;
                $data['refresh_token'] = $authorization_info->authorizer_refresh_token;
                $data['cm_id'] = $compid;
                //$data['create_time'] = date('Y-m-d H:i:s', time());
                $data['isCancel'] = -1;
                $data['um_id'] = $checkAuthor['umid'] ? $checkAuthor['umid'] : uniqid();
                $data['id'] = $publicnoModel->add($data);
                if (!$data['id']) {
                    $this->error("信息录入失败",U('publicno/access',array('compid'=>$compid)));
                }
                //检查分类信息是否已经添加
                $weixin_cate_model =  M('sys_category');
                $whereCategory = array('cm_id' => $compid);
                $checkCate = $weixin_cate_model->field('id')->where($whereCategory)->find();
                if (!($checkCate)) {
                    //添加分类信息
                    for ($i=1; $i<=19 ; $i++) {
                        $cate_info[$i]['name'] = "自定义$i";
                        $cate_info[$i]['type'] = 1;
                        $cate_info[$i]['sequence'] = $i;
                        if ($i == 16) {
                            $cate_info[$i]['name'] = '公告';
                            $cate_info[$i]['type'] = 3;
                        } elseif ($i == 17) {
                            $cate_info[$i]['name'] = '群发消息';
                            $cate_info[$i]['type'] = 4;
                        } elseif ($i == 18) {
                            $cate_info[$i]['name'] = '联系我们';
                            $cate_info[$i]['type'] = 5;
                        } elseif ($i == 19) {
                            $cate_info[$i]['name'] = '帮助';
                            $cate_info[$i]['type'] = 6;
                        }
                        $cate_info[$i]['cm_id'] = $compid;
                        $weixin_cate_model->add($cate_info[$i]);
                    }
                }
                //为公众号设置行业
                $industryResult = $weixinModel->setIndustry($compid);
                //将获取用户和图文发送到队列
                RabbitMQ::publish($this->userQueue, json_encode($data));
                RabbitMQ::publish($this->imgtxtQueue, json_encode($data));
                //初始化公众号的默认菜单
                $menu = $weixinModel->set_menu('','',$authorization_info->authorizer_access_token,$authorization_info->authorizer_appid,$data['um_id']);
                if ($menu != 1) {
                    $this->error("设置菜单失败", U('publicno/customMenu', array(
                        'compid' => $compid
                    )));
                }
                $this->success("授权成功", U('property/index', array(
                    'compid' => $compid
                )));
            } else {
                $this->error("信息录入失败", U('publicno/access', array(
                    'compid' => $compid
                )));
            }
        } else {
            $this->error("授权失败", U('publicno/access', array(
                'compid' => $compid
            )));
        }
    }

    /**
     * 公众号解绑函数
     */
    public function unlock()
    {
        $compid = I('get.compid', '');
        // 获取公众号信息
        $publicnoModel = M('publicno');
        $where = array(
            'cm_id' => $compid
        );
        $publicnoInfo = $publicnoModel->field(array(
            'authorizer_info',
            'user_name'
        ))
            ->where($where)
            ->find();
        
        $this->assign('compid', $compid);
        $this->assign('publicno', $publicnoInfo);
        $this->display();
    }

    /**
     * 自定义菜单界面函数
     */
    public function customMenu()
    {
        $compid = I('request.compid', '');
        $weixinModel = D('Weixin');
        $publicnoModel = M('publicno');
        // 查询该公众号的自定义菜单信息
        $where = array(
            'cm_id' => $compid
        );
        $menu_info = $publicnoModel->field(array(
            'id',
            'custom_menu',
            'custom_url',
            'custom_type',
            'appid',
            'um_id',
        ))
            ->where($where)
            ->find();
        $data['id'] = $menu_info['id'];
        // 接收页面传送过来的数据
        $data['custom_menu'] = I('post.menu_name', '');
        $data['custom_url'] = I('post.url', '');
        $data['custom_type'] = I('post.type', '');
        // 获取公众号的access_token
        $access_token = $weixinModel->get_authorizer_access_token($compid);
        if ($access_token == - 1) {
            $this->error("授权不可用或已过期，请重新授权", U('publicno/access', array(
                'compid' => $compid
            )));
        }
        
        if ($data['custom_type'] == 1) {
            $result = $weixinModel->set_menu($data['custom_menu'], '', $access_token, $menu_info['appid'],$menu_info['um_id']);
        } elseif ($data['custom_type'] == 2) {
            $result = $weixinModel->set_menu($data['custom_menu'], $data['custom_url'], $access_token, $menu_info['appid'],$menu_info['um_id']);
        }
        
        // 根据$result 的不同值返回页面不同的值
        if ($result == 1) {
            if ($check = $publicnoModel->save($data)) {
                retMessage(true, null, "", "", 2000);
            } else {
                retMessage(false, null, "数据保存失败", "数据保存失败", 2333);
            }
        } elseif ($result) {
            retMessage(false, null, "", "", $result);
        }
        
        $this->assign('menu', $menu_info);
        $this->assign('compid', $compid);
        $this->display();
    }

    /**
     * 微信支付设置页面
     */
    public function payinfo()
    {
        // 获取绑定的公众号信息
        $info = $this->publicnoModel->getPublicnoInfo($this->companyID);
        if ($info['isCancel'] == 1) {
            $this->error('请绑定公众号！', U('access', array(
                'compid' => $this->companyID
            )));
        }
        
        $this->assign('info', $info);
        $this->display();
    }

    /**
     * 保存微信支付设置
     */
    public function savePayInfo()
    {
        // 接收数据
        $id = I('post.id', '');
        $mchId = I('post.mchId', '');
        $apiKey = I('post.apiKey', '');
        if (!$id || ! $mchId || ! $apiKey) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
        
        $result = $this->publicnoModel->savePayInfo($id, $mchId, $apiKey);
        if (! $result) {
            retMessage(false, null, '保存微信支付设置失败', '保存微信支付设置失败', 4002);
            exit();
        }
        retMessage(true, null);
        exit();
    }

    /**
     * 生成API秘钥
     */
    public function createApiKey()
    {
        // 接收数据
        $compid = I('post.compid', '');
        $mchId = I('post.mchId', '');
        if (! $compid || ! $mchId) {
            retMessage(false, null, '接收不到数据', '接收不到数据', 4001);
            exit();
        }
        
        $apiKey = md5(password_hash($compid . $mchId, PASSWORD_BCRYPT, array(
            'cost' => 12,
            'salt' => mcrypt_create_iv(22, MCRYPT_RAND)
        )));
        if (! $apiKey) {
            retMessage(false, null, '生成API秘钥失败', '生成API秘钥失败', 4002);
            exit();
        }
        retMessage(true, $apiKey);
        exit;
    }
}
