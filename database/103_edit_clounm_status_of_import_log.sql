ALTER TABLE `fx_import_log`
MODIFY COLUMN `status`  tinyint(1) NULL DEFAULT '-1' COMMENT '状态(1:导入完成，-1：导入中，-2-导入失败，默认-1)' AFTER `file_path`;

