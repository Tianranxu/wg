ALTER TABLE `fx_weixin_user`
MODIFY COLUMN `nickname`  varchar(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '昵称' AFTER `openid`;