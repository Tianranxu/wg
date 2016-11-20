INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES (160,'Home/Fault',1,'Home/Fault/repair','PCr报修','故障管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES (161,'Home/Fault',1,'Home/Fault/index','报修首页','故障管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES (162,'Home/Fault',1,'Home/Fault/specific','报修具体情况','故障管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES (163,'Home/Fault',1,'Home/Fault/phenomenon','故障设备现象','故障管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES (164,'Home/Fault',1,'Home/Fault/do_repair','提交报修单(ajax)','故障管理');

UPDATE `fx_sys_role` SET rule_id = concat(rule_id,',160,161,162,163,164') WHERE id = 3;
alter table `fx_sys_repairer` modify column `head` varchar(200);