ALTER TABLE `fx_comp_device_temp`
ADD COLUMN `status`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态 -1-停止服务 1-正常服务' AFTER `did`;

