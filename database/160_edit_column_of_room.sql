ALTER TABLE `fx_room_source`
MODIFY COLUMN `increase_type`  tinyint(1) UNSIGNED NULL DEFAULT NULL COMMENT '递增类型 1:定额 2:百分比' AFTER `is_increase`;

