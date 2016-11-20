DROP TABLE IF EXISTS `fx_bind_temp`;

CREATE TABLE `fx_bind_temp` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键' ,
`openid`  varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户的标识，对当前公众号唯一 ' ,
`type`  tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '绑定类型 1-房产绑定 2-车辆绑定，默认1' ,
`hm_id`  int(11) UNSIGNED NULL DEFAULT NULL COMMENT '房间ID' ,
`car_id`  int(11) UNSIGNED NULL DEFAULT NULL COMMENT '车辆ID' ,
`create_time`  datetime NOT NULL COMMENT '绑定日期' ,
PRIMARY KEY (`id`),
INDEX `openid` (`openid`) USING BTREE COMMENT '用户的标识，对当前公众号唯一 ',
INDEX `type` (`type`) USING BTREE COMMENT '绑定类型 1-房产绑定 2-车辆绑定，默认1',
INDEX `hm_id` (`hm_id`) USING BTREE COMMENT '房间ID',
INDEX `car_id` (`car_id`) USING BTREE COMMENT '车辆ID'
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
ROW_FORMAT=COMPACT
;