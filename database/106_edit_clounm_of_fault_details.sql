ALTER TABLE `fx_fault_details`
MODIFY COLUMN `status`  tinyint(2) NULL DEFAULT '-1' COMMENT '状态(未修复或未接单是-1；正在修复或已接单是1；已修复是2；已评价是3；已转单是4；超时接单是-2；超时2次转后台是-3；被挂起，即筛选不到维修员-4)' AFTER `fault_number`;

