INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(273,'Home/Chargeitems',1,'Home/Chargeitems/clearItem','清除费项','费项管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',273') WHERE id = 3;