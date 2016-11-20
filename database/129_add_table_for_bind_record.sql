CREATE TABLE `fx_house_bind_record` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键' ,
`cm_id`  int(11) UNSIGNED NOT NULL COMMENT '企业ID' ,
`cc_id`  int(11) UNSIGNED DEFAULT NULL COMMENT '楼盘ID',
`ch_id`  int(11) UNSIGNED NOT NULL COMMENT '费项ID' ,
`bind_status`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '绑定状态  -1-解绑，1-绑定' ,
`adress` varchar(50) NOT NULL COMMENT '绑定房产',
`update_time`  datetime NULL COMMENT '更新时间' ,
`uid` int(11) UNSIGNED NOT NULL COMMENT '操作者id',
PRIMARY KEY (`id`),
INDEX `cm_id` (`cm_id`) USING BTREE COMMENT '企业ID',
INDEX `ch_id` (`ch_id`) USING BTREE 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci
COMMENT='房间与费项中间表'
ROW_FORMAT=COMPACT
;