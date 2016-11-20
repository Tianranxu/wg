INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(287,'Home/Contract',1,'Home/Contract/details','合同详情','合同管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(288,'Home/Contract',1,'Home/Contract/stopContract','中止合同','合同管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',287,288') WHERE id = 3;