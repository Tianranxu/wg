INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(262,'Home/Repair',1,'Home/Repair/relatedProperty','关联物业','维修管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',262') WHERE id = 3;