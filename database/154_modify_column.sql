ALTER TABLE `fx_customer_source`
MODIFY COLUMN `area`  varchar(10) NULL DEFAULT 0 COMMENT '面积需求' AFTER `furnish_type`,
MODIFY COLUMN `price`  varchar(15) NULL DEFAULT NULL COMMENT '价格区间,元' AFTER `status`;