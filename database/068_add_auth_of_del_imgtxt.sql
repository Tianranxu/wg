INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(203,'Home/Imgtxt',1,'Home/Imgtxt/doDel','删除图文信息','图文信息');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',203') WHERE id = 3;