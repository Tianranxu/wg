ALTER TABLE `fx_customer_source`
MODIFY COLUMN `type`  tinyint(2) UNSIGNED NULL DEFAULT 1 COMMENT '类型需求 1-纯住宅 2-纯办公 3-住宅改办公 默认为1' AFTER `room_type`;

