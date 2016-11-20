ALTER TABLE `fx_completed_work`
ADD COLUMN `cm_id`  varchar(50) default NULL COMMENT '公司ID' AFTER `form_id`;