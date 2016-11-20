CREATE TABLE `fx_weixin_msg_template` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID' ,
`cm_id`  int(11) UNSIGNED NOT NULL COMMENT '公司ID' ,
`short_id`  varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '模板短id' ,
`long_id`  varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '模板长id' ,
PRIMARY KEY (`id`),
INDEX `cm_id` (`cm_id`) USING BTREE COMMENT '公司ID'
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci
ROW_FORMAT=COMPACT
;

ALTER TABLE `fx_weixin_user` ADD COLUMN `user_type` tinyint(2) DEFAULT 1 COMMENT '用户类型：1.普通用户 2.客服';
