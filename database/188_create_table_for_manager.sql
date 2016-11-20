CREATE TABLE `fx_manager_wxuser_temp` (
`cm_id`  int(11) UNSIGNED NOT NULL COMMENT '公司ID' ,
`wu_id`  int(11) UNSIGNED NOT NULL COMMENT '微信用户id' ,
`type`  int(11) UNSIGNED NOT NULL COMMENT '管理员（客服）类型:1：普通用户 2：反馈管理员 3：表单管理员 4：维修管理员 5：收费管理员' ,
INDEX `cm_id` (`cm_id`) USING BTREE COMMENT '公司ID'
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci
ROW_FORMAT=COMPACT
;

ALTER TABLE `fx_weixin_user`
DROP COLUMN `user_type`;