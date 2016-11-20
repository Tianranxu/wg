ALTER TABLE `fx_fault_timeout`
MODIFY COLUMN `type`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '超时类型 1-超时接单1次 2-超时接单2次 3-修复超时，默认-2' AFTER `fault_id`;

