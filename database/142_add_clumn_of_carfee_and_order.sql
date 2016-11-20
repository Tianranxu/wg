ALTER TABLE `fx_carfee_charges`
ADD COLUMN `cm_id`  int(11) UNSIGNED NOT NULL COMMENT '企业ID' AFTER `remark`,
ADD COLUMN `cc_id`  int(11) UNSIGNED NOT NULL COMMENT '楼盘ID' AFTER `cm_id`;
ALTER TABLE `fx_order_manage`
MODIFY COLUMN `hm_id`  int(11) NULL DEFAULT NULL COMMENT '房间id' AFTER `pay_type`,
ADD COLUMN `car_id`  int(11) UNSIGNED NULL DEFAULT NULL COMMENT '车位ID' AFTER `hm_id`;


