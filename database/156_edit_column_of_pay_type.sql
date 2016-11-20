ALTER TABLE `fx_pay_type_temp`
MODIFY COLUMN `type`  tinyint(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT '付款方式 1-现金 2-刷卡 3-银行转账 4-微信支付 99-其他，默认为1' AFTER `pay_id`;

