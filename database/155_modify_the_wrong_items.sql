INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(268,'Home/Pay',1,'Home/Pay/payDetails','收支明细','账单管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',268') WHERE id = 3;