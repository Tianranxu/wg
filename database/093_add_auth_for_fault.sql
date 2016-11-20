INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES (259,'Home/Fault',1,'Home/Fault/evaluate','修复评价','故障管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES (260,'Home/Fault',1,'Home/Fault/finish','结单','故障管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES (261,'Home/Fault',1,'Home/Fault/reassign','转单','故障管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id,',259,260,261') WHERE id = 3;

ALTER TABLE `fx_fault_details`ADD COLUMN `repairman_openid` varchar(200) DEFAULT NULL COMMENT '维修员openid';