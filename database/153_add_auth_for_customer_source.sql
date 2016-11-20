INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(278,'Home/Customersource',1,'Home/Customersource/index','客源管理界面','客源管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(279,'Home/Customersource',1,'Home/Customersource/match','匹配房源','客源管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(280,'Home/Customersource',1,'Home/Customersource/follow','跟进客源','客源管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(281,'Home/Customersource',1,'Home/Customersource/addcustomer','登记客源','客源管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(282,'Home/Customersource',1,'Home/Customersource/details','客源详情','客源管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',278,279,280,281,282') WHERE id = 3;