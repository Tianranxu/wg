INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title) VALUES(136,'Home/Material',1,'Home/Material/del_material','删除素材');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',136') WHERE id = 3;