INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title) VALUES(137,'Home/Material',1,'Home/Material/load_picture_library','图文信息-加载图片库');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',137') WHERE id = 3;