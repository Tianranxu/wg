ALTER TABLE `fx_sys_user` ADD COLUMN `session_id`  varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '�û���¼������sessionID' AFTER `invite_per_id`;