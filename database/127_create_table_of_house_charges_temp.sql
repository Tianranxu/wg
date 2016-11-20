CREATE TABLE `fx_house_charges_temp` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键' ,
`cm_id`  int(11) UNSIGNED NOT NULL COMMENT '企业ID' ,
`cc_id`  int(11) UNSIGNED NOT NULL COMMENT '楼盘ID' ,
`bm_id`  int(11) UNSIGNED NOT NULL COMMENT '楼栋ID' ,
`hm_id`  int(11) NOT NULL COMMENT '房间ID' ,
`ch_id`  int(11) UNSIGNED NOT NULL COMMENT '费项ID' ,
`status`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '绑定状态  -1-禁用，1-正常' ,
`update_time`  datetime NULL COMMENT '更新时间' ,
PRIMARY KEY (`id`),
INDEX `cm_id` (`cm_id`) USING BTREE COMMENT '企业ID',
INDEX `hm_id` (`hm_id`) USING BTREE COMMENT '房间ID',
INDEX `ch_id` (`ch_id`) USING BTREE 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci
COMMENT='房间与费项中间表'
ROW_FORMAT=COMPACT
;

