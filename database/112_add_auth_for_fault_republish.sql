INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) 
VALUES(263,'Home/Fault',2,'Home/Fault/republish','维修公司再派单','故障管理');

UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',263') WHERE id = 3;