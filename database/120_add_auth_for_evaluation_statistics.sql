INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(264,'Home/Statistics',1,'Home/Statistics/evaluation','故障统计','评价统计');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',264') WHERE id = 3;