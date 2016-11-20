INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(290,'Home/ManagerSet',1,'Home/ManagerSet/index','管理员（客服）设置界面','管理员（客服）管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(291,'Home/ManagerSet',1,'Home/ManagerSet/setPropertyManager','设置物业公司管理员（客服）','管理员（客服）管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(292,'Home/ManagerSet',1,'Home/ManagerSet/setWorkstationManager','设置工作站管理员（客服）','管理员（客服）管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(293,'Home/ManagerSet',1,'Home/ManagerSet/setRepairManager','设置维修公司管理员（客服）','管理员（客服）管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',290,291,292,293') WHERE id = 3;