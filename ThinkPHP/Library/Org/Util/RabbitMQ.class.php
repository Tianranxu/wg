<?php
/*************************************************
 * 文件名：RabbitMQ.class.php
 * 功能：     RabbitMQ
 * 日期：     2015.8.20
 * 作者：     L
 * 版权：     Copyright @ 2015 风馨科技 All Rights Reserved
 ***********************************************/
namespace Org\Util;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQ  {

    public static function publish($queue, $msg)
    {
        vendor('RabbitMQ.autoload');
        $conn = new AMQPConnection(
            C('RABBITMQ_HOST'), 
            C('RABBITMQ_PORT'), 
            C('RABBITMQ_USER'), 
            C('RABBITMQ_PASSWD'), 
            C('RABBITMQ_VHOST')
        );
        $ch = $conn->channel();
        /*
         * queue 一般为xxx_xxx_queue, exchange 一般为exchange_xxx_xxx
         */
        $exchange = 'exchange_'.str_replace('_queue', '', $queue);
        $ch->queue_declare($queue, false, true, false, false);
        $ch->exchange_declare($exchange, 'direct', false, true, false);
        $ch->queue_bind($queue, $exchange);
        $msg = new AMQPMessage($msg, array('content_type' => 'text/plain', 'delivery_mode' => 2));
        $ch->basic_publish($msg, $exchange);
        $ch->close();
        $conn->close();
    }

}
