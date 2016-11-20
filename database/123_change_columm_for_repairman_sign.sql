ALTER TABLE `fx_repairman_sign`
MODIFY COLUMN `latitude`  varchar(30) NULL DEFAULT NULL COMMENT '十进制纬度' AFTER `sr_id`,
MODIFY COLUMN `longitude`  varchar(30) NULL DEFAULT NULL COMMENT '十进制经度' AFTER `latitude`;