INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(239,'Home/Room',1,'Home/Room/getFollows','房源跟进','房源管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',239') WHERE id = 3;