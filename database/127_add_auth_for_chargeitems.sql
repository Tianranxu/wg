INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(266,'Home/Chargeitems',1,'Home/Chargeitems/index','费项设置','费项管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(267,'Home/Chargeitems',1,'Home/Chargeitems/bindRecord','查看费项绑定记录','费项管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',266,267') WHERE id = 3;