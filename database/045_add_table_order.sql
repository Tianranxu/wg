DROP TABLE IF EXISTS `fx_order_manage`;

CREATE TABLE `fx_order_manage` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID' ,
`type`  tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '待缴账单类型 1-房产 2-车辆' ,
`openid`  varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户的标识，对当前公众号唯一 ' ,
`ac_ids`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '待缴订单ID字符串，用逗号隔开' ,
`total`  float(11,2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '待缴账单总金额' ,
`create_time`  datetime NOT NULL COMMENT '创建时间' ,
`modify_time`  datetime NOT NULL COMMENT '修改时间' ,
PRIMARY KEY (`id`),
INDEX `type` (`type`) USING BTREE COMMENT '待缴账单类型 1-房产 2-车辆',
INDEX `openid` (`openid`) USING BTREE COMMENT '用户的标识，对当前公众号唯一 '
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
ROW_FORMAT=COMPACT
;



