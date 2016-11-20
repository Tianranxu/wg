#Dump of table fx_index_images
#--------------------------------------------------------------
SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8;

INSERT INTO `fx_sys_auth_rule` VALUES ('128', 'Home/Fees', '1', 'Home/Fees/index', '收费设置', '1', null, '');
update fx_sys_role set rule_id = concat(rule_id, ',128') where id = '3';



