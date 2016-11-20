INSERT INTO `fx_sys_auth_rule` (id, module, type, name, title, status, rule_cond, module_name)
VALUES(146, 'Home/Homecompose', 1, 'Home/Homecompose/search', '查询工作站', 1, '', '模糊查询工作站'),
	  (147, 'Home/Homecompose', 1, 'Home/Homecompose/binding', '绑定工作站', 1, '', '绑定工作站'),
	  (148, 'Home/Homecompose', 1, 'Home/Homecompose/unbinding', '解绑工作站', 1, '', '解绑工作站'),
	  (149, 'Home/Homecompose', 1, 'Home/Homecompose/rename', '修改菜单名', 1, '', '修改菜单名');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',146,147,148,149') WHERE id = 3;