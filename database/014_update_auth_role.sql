INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title) VALUES(135,'Home/Material',1,'Home/Material/search_image_text','搜索图文信息');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',135') WHERE id = 3;
