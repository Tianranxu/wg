ALTER TABLE `fx_accounts_charges`ADD COLUMN `year`  int(11) UNSIGNED NULL DEFAULT NULL COMMENT '年份' AFTER `is_preferential`,ADD COLUMN `month`  int(11) UNSIGNED NULL DEFAULT NULL COMMENT '月份' AFTER `year`;