ALTER TABLE `fx_feedback`
ADD COLUMN `isSystem`  tinyint(1) DEFAULT -1 COMMENT '是否为系统反馈(-1为否，1为是)';

UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',246,247,248') WHERE id = 1;