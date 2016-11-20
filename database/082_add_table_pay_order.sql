CREATE TABLE `fx_pay_order` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键' ,
`appid`  varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '公众号APPID' ,
`timestamp`  timestamp NULL DEFAULT NULL COMMENT '统一下单接口时间戳' ,
`noncestr`  varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '统一下单接口随机字符串' ,
`package`  varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '统一下单接口返回的prepay_id参数值' ,
`signtype`  varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '签名算法，暂支持MD5' ,
`paysign`  varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '签名' ,
`openid`  varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '用户openid' ,
`out_trade_no`  varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '商户系统内部的订单号,32个字符内、可包含字母' ,
`order_id`  int(11) UNSIGNED NOT NULL COMMENT '订单ID' ,
`ac_ids`  varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '该订单所包含的账单ID' ,
`compid`  int(11) UNSIGNED NOT NULL COMMENT '企业ID' ,
`total`  float(11,2) UNSIGNED NOT NULL COMMENT '订单总金额' ,
`pay_date`  datetime NOT NULL COMMENT '付款时间' ,
`pay_return_date`  datetime NOT NULL COMMENT '支付接口返回时间' ,
`create_time`  datetime NOT NULL COMMENT '创建时间' ,
PRIMARY KEY (`id`),
INDEX `id` (`id`) USING BTREE ,
INDEX `order_id` (`order_id`) USING BTREE ,
INDEX `ac_ids` (`ac_ids`) USING BTREE ,
INDEX `out_trade_no` (`out_trade_no`) USING BTREE 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci
ROW_FORMAT=COMPACT
;

