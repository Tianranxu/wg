ALTER TABLE `fx_comp_manage`
MODIFY COLUMN `catch_time_limit`  int(11) UNSIGNED NULL DEFAULT 10 COMMENT '接单时限' AFTER `code`,
MODIFY COLUMN `repair_time_limit`  int(11) UNSIGNED NULL DEFAULT 180 COMMENT '维修时限' AFTER `catch_time_limit`;

