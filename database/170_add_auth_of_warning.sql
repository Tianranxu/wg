INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(193,'Home/Warning',1,'Home/Warning/index','预警首页','预警');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(194,'Home/Warning',1,'Home/Warning/set','预警设置(AJAX)','预警');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(195,'Home/Pay',1,'Home/Pay/leasePayDetails','租赁收支明细','租赁账单管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',193,194,195') WHERE id = 3;