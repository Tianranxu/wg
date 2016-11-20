ALTER TABLE `fx_pay_order`
MODIFY COLUMN `openid`  varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '用户openid' AFTER `paysign`,
MODIFY COLUMN `pay_return_date`  datetime NULL DEFAULT NULL COMMENT '支付接口返回时间' AFTER `pay_date`,
ADD COLUMN `uid`  int(11) UNSIGNED NULL DEFAULT NULL COMMENT '操作人ID' AFTER `create_time`,
ADD COLUMN `pay_user`  varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '缴费人员' AFTER `uid`;
