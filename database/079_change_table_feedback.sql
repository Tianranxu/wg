ALTER TABLE `fx_feedback` MODIFY COLUMN `appid`  varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL COMMENT '公众号appid' AFTER `content`;
ALTER TABLE `fx_feedback` ADD COLUMN `cm_id` int(11) DEFAULT NULL COMMENT '公司id';

