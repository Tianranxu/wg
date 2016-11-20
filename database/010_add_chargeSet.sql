SET FOREIGN_KEY_CHECKS=0;


INSERT INTO `fx_sys_auth_rule` VALUES ('110', 'Home/Propertycharges', '2', 'Home/Propertycharges/set_list', '保存收费设置', '1', '');

update fx_sys_role set rule_id = concat(rule_id, ',110') where id = '2';
update fx_sys_role set rule_id = concat(rule_id, ',110') where id = '3';