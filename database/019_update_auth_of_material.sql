INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title) VALUES(138,'Home/Material',1,'Home/Material/add_material','添加素材');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',138') WHERE id = 3;