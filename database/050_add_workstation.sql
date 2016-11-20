SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8;
INSERT INTO `fx_sys_auth_rule` VALUES(129,'Home/Repair',1,'Home/Repair/index','维修管理首页',1,'','维修');
INSERT INTO `fx_sys_auth_rule` VALUES(130,'Home/Workstation',1,'Home/Workstation/index','工作站管理首页',1,'','工作站');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',129,130') WHERE id = 3;