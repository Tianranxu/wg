INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES (250,'Home/Form',1,'Home/Form/add_group','添加分组','表单管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES (251,'Home/Form',1,'Home/Form/change_group','修改分组','表单管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES (252,'Home/Form',1,'Home/Form/del_group','删除分组','表单管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(202,'Home/Imgtxt',1,'Home/Imgtxt/doImgtxt','新建/编辑图文信息','图文信息');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id,',250,251,252') WHERE id = 3;