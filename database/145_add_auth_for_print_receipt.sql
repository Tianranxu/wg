INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(276,'Home/Print',1,'Home/Print/printReceipt','收费打印页面','打印管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(277,'Home/Print',1,'Home/Print/doPrint','打印物业费收据','打印管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',276,277') WHERE id = 3;
