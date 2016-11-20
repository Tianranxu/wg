INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) 
VALUES(166,'Home/Statistics',2,'Home/Statistics/count','故障统计','统计管理'),
      (167,'Home/Statistics',2,'Home/Statistics/purview','故障统计(ajax)','统计管理');

UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',166,167') WHERE id = 3;