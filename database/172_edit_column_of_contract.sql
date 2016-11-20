ALTER TABLE `fx_contract_manage`
MODIFY COLUMN `month`  int(11) UNSIGNED NULL COMMENT '第几个月，当租金周期为1时，可为空' AFTER `deposit`,
MODIFY COLUMN `days`  int(11) UNSIGNED NULL COMMENT '第几日，当租金周期为1时，可为空' AFTER `month`,
MODIFY COLUMN `cycle`  int(11) UNSIGNED NOT NULL DEFAULT 1 COMMENT '租金周期（以月为单位）' AFTER `rent`;

