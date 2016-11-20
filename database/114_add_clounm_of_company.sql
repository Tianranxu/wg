ALTER TABLE `fx_comp_manage`
ADD COLUMN `catch_time_limit`  int(11) UNSIGNED NULL DEFAULT NULL COMMENT '接单时限' AFTER `code`,
ADD COLUMN `repair_time_limit`  int(11) UNSIGNED NULL DEFAULT NULL COMMENT '维修时限' AFTER `catch_time_limit`;

