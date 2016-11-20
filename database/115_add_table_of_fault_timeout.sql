CREATE TABLE `fx_fault_timeout` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键' ,
`fault_id`  int(11) UNSIGNED NOT NULL COMMENT '工单ID' ,
`type`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '超时类型 1-接单超时 2-修复超时，默认1' ,
`update_time`  datetime NULL COMMENT '记录时间' ,
PRIMARY KEY (`id`),
INDEX `fault_id` (`fault_id`) USING BTREE COMMENT '工单ID',
INDEX `type` (`type`) USING BTREE COMMENT '超时类型'
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci
ROW_FORMAT=COMPACT
;

