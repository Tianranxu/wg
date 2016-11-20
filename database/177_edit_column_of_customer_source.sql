ALTER TABLE `fx_customer_source`
MODIFY COLUMN `room_type`  tinyint(2) NULL DEFAULT NULL COMMENT '户型需求 1-一室 2-二室 3-三室 4-四室 5-四室以上 99-其它' AFTER `cc_id`,
MODIFY COLUMN `type`  tinyint(2) UNSIGNED NULL DEFAULT NULL COMMENT '类型需求 1-纯住宅 2-纯办公 3-住宅改办公 默认为1' AFTER `room_type`,
MODIFY COLUMN `cm_id`  int(11) NOT NULL COMMENT '公司id' AFTER `type`,
MODIFY COLUMN `furnish_type`  tinyint(2) NULL DEFAULT NULL COMMENT '装修 1-毛坯 2-简装 3-精装 4-豪装 99-其它' AFTER `cm_id`,
MODIFY COLUMN `area`  varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '0-9999' COMMENT '面积需求' AFTER `furnish_type`,
MODIFY COLUMN `price`  varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '0-99999' COMMENT '价格区间,元' AFTER `status`,
MODIFY COLUMN `name`  varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '客源姓名' AFTER `sign_time`;

