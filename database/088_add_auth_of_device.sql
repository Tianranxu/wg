INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(211,'Home/Device',1,'Home/Device/addDevice','添加设备','故障设备');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',211') WHERE id = 1;