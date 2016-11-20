<?php
/*************************************************
 * 文件名：BaseModel.class.php
 * 功能：     基础模型
 * 日期：     2015.8.20
 * 作者：     DA mimi
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Home\Model;
use Think\Model;
use Predis\Client;

class BaseModel extends Model{
    protected $_redis;
    
    /**
     * 连接Redis服务器
     */
    public function connectRedis(){
        //Redis初始化
        vendor('Redis.autoload');
        $this->_redis=new Client(array(
            'host' => C('REDIS_HOST'),
            'port' => C('REDIS_PORT'),
            'database' => C('REDIS_DB'),
        ));
        
        return $this->_redis;
    }
    
    /**
     * 断开连接Redis服务器
     */
    public function disConnectRedis(){
        $this->_redis->quit();
    }
    
}