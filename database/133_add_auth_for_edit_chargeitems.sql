INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(269,'Home/Chargeitems',1,'Home/Chargeitems/editItem','编辑费项','费项管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(270,'Home/Chargeitems',1,'Home/Chargeitems/doEdit','ajax保存编辑费项数据','费项管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(271,'Home/Chargeitems',1,'Home/Chargeitems/roomItems','房间收费管理','费项管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(272,'Home/Chargeitems',1,'Home/Chargeitems/saveRoomItems','保存房间收费信息','费项管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',269,270,271,272') WHERE id = 3;