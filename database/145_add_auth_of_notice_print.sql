INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(232,'Home/Print',1,'Home/Print/notice','通知单','打印管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',232') WHERE id = 3;