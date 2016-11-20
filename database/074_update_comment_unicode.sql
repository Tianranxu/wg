ALTER TABLE `fx_accounts_charges`
MODIFY COLUMN `remark`  varchar(280) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '备注' AFTER `month`;

ALTER TABLE `fx_bind_temp`
MODIFY COLUMN `id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键' FIRST ,
MODIFY COLUMN `openid`  varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '用户openid' AFTER `id`,
MODIFY COLUMN `type`  tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '绑定类型 1-房产 2-车辆' AFTER `openid`,
MODIFY COLUMN `hm_id`  int(11) UNSIGNED NULL DEFAULT NULL COMMENT '房间ID' AFTER `type`,
MODIFY COLUMN `car_id`  int(11) UNSIGNED NULL DEFAULT NULL COMMENT '车辆ID' AFTER `hm_id`,
MODIFY COLUMN `create_time`  datetime NOT NULL COMMENT '创建时间' AFTER `is_pay`;

ALTER TABLE `fx_house_user_temp`
COMMENT='';

ALTER TABLE `fx_imgtxt_manage`
MODIFY COLUMN `media_id`  varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '媒体ID' AFTER `id`,
MODIFY COLUMN `modify_time`  datetime NULL DEFAULT NULL COMMENT '更新时间' AFTER `category_id`;

ALTER TABLE `fx_order_manage`
MODIFY COLUMN `openid`  varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '用户openid' AFTER `type`,
MODIFY COLUMN `ac_ids`  varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '账单ID，用逗号隔开' AFTER `openid`;

ALTER TABLE `fx_sys_category`
MODIFY COLUMN `name`  varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '分类名称' AFTER `id`;

ALTER TABLE `fx_weixin_user`
MODIFY COLUMN `id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键' FIRST ,
MODIFY COLUMN `mobile`  varchar(11) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '手机号码' AFTER `id`,
MODIFY COLUMN `openid`  varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '用户openid' AFTER `mobile`,
MODIFY COLUMN `nickname`  varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '昵称' AFTER `openid`,
MODIFY COLUMN `sex`  tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '性别' AFTER `nickname`,
MODIFY COLUMN `language`  varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '语言' AFTER `sex`,
MODIFY COLUMN `city`  varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '城市' AFTER `language`,
MODIFY COLUMN `province`  varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '省' AFTER `city`,
MODIFY COLUMN `country`  varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '国籍' AFTER `province`,
MODIFY COLUMN `headimgurl`  varchar(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '头像' AFTER `country`,
MODIFY COLUMN `subscribe_time`  int(11) NULL DEFAULT NULL COMMENT '关注时间' AFTER `headimgurl`,
MODIFY COLUMN `unionid`  varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'unionID' AFTER `subscribe_time`,
MODIFY COLUMN `remark`  varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '描述' AFTER `unionid`,
MODIFY COLUMN `groupid`  int(11) UNSIGNED NULL DEFAULT NULL COMMENT '用户所属分组ID' AFTER `remark`;

