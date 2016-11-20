DROP TABLE IF EXISTS `fx_pay_type_temp`;

CREATE TABLE `fx_pay_type_temp` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID' ,
`pay_id`  int(11) UNSIGNED NOT NULL COMMENT '支付表ID' ,
`type`  tinyint(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT '付款方式 1-现金 2-刷卡 3-银行转账 99-其他，默认为1' ,
`remark`  varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '备注' ,
`update_time`  datetime NULL COMMENT '更新时间' ,
PRIMARY KEY (`id`),
INDEX `pay_id` (`pay_id`) USING BTREE COMMENT '支付表ID'
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci
ROW_FORMAT=COMPACT
;

