ALTER TABLE `fx_publicno`
ADD COLUMN `mch_id`  varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL COMMENT '微信支付商户ID' AFTER `custom_type`,
ADD COLUMN `api_key`  char(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL COMMENT '微信支付API秘钥' AFTER `mch_id`;

