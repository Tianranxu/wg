INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(208,'Home/Piclibrary',1,'Home/Piclibrary/doDel','图片库-删除分组/删除图片','图片库');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',208') WHERE id = 3;