ALTER TABLE `fx_sys_role`
MODIFY COLUMN `rule_id`  varchar(2000) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `modify_time`;

