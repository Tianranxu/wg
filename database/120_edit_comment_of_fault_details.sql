ALTER TABLE `fx_fault_details`
MODIFY COLUMN `status`  tinyint(2) NULL DEFAULT '-1' COMMENT '状态(未修复或未接单是-1；正在修复或已接单是1；已修复是2；已评价是3；已转单是4；被挂起，即筛选不到维修员-4)' AFTER `fault_number`,
ADD COLUMN `timeout_status`  tinyint(2) NULL DEFAULT NULL COMMENT '1-接单超时1次 2-接单超时2次，转后台 3-修复超时' AFTER `status`;

