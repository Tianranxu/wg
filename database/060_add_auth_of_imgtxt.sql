INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(198,'Home/Imgtxt',1,'Home/Imgtxt/index','图文信息页面','图文信息页面');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',198') WHERE id = 3;