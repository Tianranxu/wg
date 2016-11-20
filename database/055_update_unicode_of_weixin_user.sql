ALTER TABLE `fx_weixin_user`
MODIFY COLUMN `openid`  varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '用户的标识，对当前公众号唯一 ' AFTER `mobile`,
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci;