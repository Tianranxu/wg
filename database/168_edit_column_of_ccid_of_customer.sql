ALTER TABLE `fx_customer_source`
MODIFY COLUMN `cc_id`  int(11) UNSIGNED NULL COMMENT '楼盘id，即楼盘需求' AFTER `customer_id`;

