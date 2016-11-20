ALTER TABLE `fx_pay_type_temp`
MODIFY COLUMN `total`  float(11,2) UNSIGNED NULL DEFAULT 0 COMMENT '付款金额' AFTER `type`;

