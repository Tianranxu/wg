ALTER TABLE `fx_accounts_charges`
MODIFY COLUMN `remark`  varchar(280) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '备注' AFTER `month`;

ALTER TABLE `fx_bind_temp`
MODIFY COLUMN `openid`  varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '用户的标识，对当前公众号唯一 ' AFTER `id`,
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `fx_city`
MODIFY COLUMN `name`  varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `id`,
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `fx_imgtxt_manage`
MODIFY COLUMN `media_id`  varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '图文信息mediaID' AFTER `id`,
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `fx_order_manage`
MODIFY COLUMN `openid`  varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '用户的标识，对当前公众号唯一 ' AFTER `type`,
MODIFY COLUMN `ac_ids`  varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '待缴订单ID字符串，用逗号隔开' AFTER `openid`,
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `fx_sys_category`
MODIFY COLUMN `name`  varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '分类名称' AFTER `id`,
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `fx_weixin_user`
MODIFY COLUMN `mobile`  varchar(11) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '微信用户手机号码' AFTER `id`,
MODIFY COLUMN `nickname`  varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '用户的昵称' AFTER `openid`,
MODIFY COLUMN `language`  varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '用户的语言，简体中文为zh_CN' AFTER `sex`,
MODIFY COLUMN `city`  varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '用户所在城市' AFTER `language`,
MODIFY COLUMN `province`  varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '用户所在省份' AFTER `city`,
MODIFY COLUMN `country`  varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '用户所在国家' AFTER `province`,
MODIFY COLUMN `headimgurl`  varchar(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空。若用户更换头像，原有头像URL将失效' AFTER `country`,
MODIFY COLUMN `unionid`  varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '只有在用户将公众号绑定到微信开放平台帐号后，才会出现该字段' AFTER `subscribe_time`,
MODIFY COLUMN `remark`  varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '公众号运营者对粉丝的备注，公众号运营者可在微信公众平台用户管理界面对粉丝添加备注' AFTER `unionid`;

