ALTER TABLE `fx_sys_role`
MODIFY COLUMN `rule_id`  varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '所属规则（多个规则逗号隔离）' AFTER `modify_time`;