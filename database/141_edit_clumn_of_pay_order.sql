ALTER TABLE `fx_pay_order`
CHANGE COLUMN `compid` `cm_id`  int(11) UNSIGNED NOT NULL COMMENT '企业ID' AFTER `ac_ids`;

