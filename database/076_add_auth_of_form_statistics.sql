INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES (254,'Home/Form',1,'Home/Form/statistics','统计表单','表单管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES (255,'Home/Form',1,'Home/Form/check','表单详情（未审核）','表单管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES (256,'Home/Form',1,'Home/Form/checked','表单详情（已审核）','表单管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id,',254,255,256') WHERE id = 3;