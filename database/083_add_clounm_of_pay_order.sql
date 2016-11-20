ALTER TABLE `fx_pay_order`
ADD COLUMN `status`  tinyint(1) NOT NULL COMMENT '支付状态 -1-支付错误 1-支付失败 2-取消支付 3-支付成功' AFTER `pay_return_date`,
ADD INDEX `status` (`status`) USING BTREE ;

