INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(284,'Home/Customersource',1,'Home/Customersource/save','保存客源','客源管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(285,'Home/Customersource',1,'Home/Customersource/addFollow','添加跟进信息','客源管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',284,285') WHERE id = 3;