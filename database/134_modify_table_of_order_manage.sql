ALTER TABLE `fx_order_manage`
MODIFY COLUMN `openid`  varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL COMMENT '用户openid' AFTER `type`,
ADD COLUMN `uid`  int(11) NULL COMMENT 'pc端用户id' AFTER `create_time`,
ADD COLUMN `cm_id`  int(11) NOT NULL COMMENT '公司id' AFTER `uid`,
ADD COLUMN `pay_type`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '支付类型：1.收入 2.支出' AFTER `cm_id`,
MODIFY COLUMN `status`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态 1-待缴费 2-已缴费' AFTER `total`,
ADD COLUMN `hm_id`  int(11) NOT NULL COMMENT '房间id' AFTER `pay_type`;

ALTER TABLE `fx_accounts_charges`
MODIFY COLUMN `status`  tinyint(1) NULL DEFAULT '-1' COMMENT '状态 -1：已生成，未发布；1：已发布，未缴费；2：已缴费； ' AFTER `description`;