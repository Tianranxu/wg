
INSERT INTO `fx_sys_auth_rule` VALUES ('111', 'Home/Property', '1', 'Home/Property/checkPublicno', '检查公众号接入情况', '1', '');

UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',111') WHERE id = 3;

UPDATE `fx_sys_role` SET rule_id= concat(rule_id,',90,91,92') WHERE id=1;

ALTER TABLE `fx_publicno` ADD COLUMN `create_time`  datatime CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '创建时间';