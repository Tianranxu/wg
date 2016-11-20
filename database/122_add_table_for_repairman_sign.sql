CREATE TABLE `fx_repairman_sign` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键' ,
`sr_id`  int(11) UNSIGNED NOT NULL COMMENT '维修员ID' ,
`latitude`  int(20)  DEFAULT NULL COMMENT '十进制纬度' ,
`longitude`  int(20)  DEFAULT NULL COMMENT '十进制经度' ,
`accuracy` int(10) DEFAULT NULL COMMENT '位置精度',
`sign_time`  datetime NULL COMMENT '签到时间' ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci
ROW_FORMAT=COMPACT
;