ALTER TABLE `fx_pay_type_temp`
ADD COLUMN `total`  float(11,0) UNSIGNED NULL DEFAULT 0.00 COMMENT '付款金额' AFTER `type`;

