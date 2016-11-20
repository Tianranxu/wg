INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(196,'Home/Form',1,'Home/Form/add','新建表单','新建表单');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',196') WHERE id = 3;