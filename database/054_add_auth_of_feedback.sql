INSERT INTO `fx_sys_auth_rule` VALUES (246,'Home/Feedback',1,'Home/Feedback/index','反馈管理页面',1,'','反馈管理');
INSERT INTO `fx_sys_auth_rule` VALUES (247,'Home/Feedback',1,'Home/Feedback/search','反馈搜索',1,'','反馈管理');
INSERT INTO `fx_sys_auth_rule` VALUES (248,'Home/Feedback',1,'Home/Feedback/response','反馈回复',1,'','反馈管理');
UPDATE `fx_sys_auth_rule` SET `module_name` = '收费设置二级菜单' WHERE `id`=128; 
UPDATE `fx_sys_role` SET `rule_id` = concat(rule_id, ',246,247,248') WHERE `id` = 3; 