INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(209,'Home/Piclibrary',1,'Home/Piclibrary/getPiclibrary','图片库-获取数据','图片库');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',209') WHERE id = 3;