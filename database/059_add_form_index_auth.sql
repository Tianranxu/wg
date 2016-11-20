INSERT INTO `fx_sys_auth_rule` VALUES (131,'Home/Form',1,'Home/Form/index','表单管理首页',1,'','表单管理');
UPDATE `fx_sys_role` SET `rule_id` = concat(rule_id, ',131') WHERE `id` = 3; 