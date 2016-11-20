ALTER TABLE `fx_pay_order`
MODIFY COLUMN `timestamp`  int(11) UNSIGNED NULL DEFAULT NULL COMMENT '统一下单接口时间戳' AFTER `appid`;

