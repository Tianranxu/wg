ALTER TABLE `fx_weixin_user`
MODIFY COLUMN `openid`  varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '�û��ı�ʶ���Ե�ǰ���ں�Ψһ ' AFTER `mobile`,
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci;