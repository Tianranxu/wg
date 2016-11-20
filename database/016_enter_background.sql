SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8;

INSERT INTO `fx_sys_auth_rule` VALUES ('112', 'Home/Company', '2', 'Home/Company/background', '进入后台', '1', '');

update fx_sys_role set rule_id = concat(rule_id, ',112') where id = '3';