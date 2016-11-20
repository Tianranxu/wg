INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) 
VALUES(151,'Home/Repair',2,'Home/Repair/createGroup','维修新建分组','维修员管理'),
	  (152,'Home/Repair',2,'Home/Repair/delete','删除分组或成员','维修员管理'),
	  (153,'Home/Repair',2,'Home/Repair/move','移动成员','维修员管理'),
	  (154,'Home/Repair',2,'Home/Repair/item','分配工作','维修员管理'),
	  (155,'Home/Repair',2,'Home/Repair/area','分配区域','维修员管理'),
	  (156,'Home/Repair',2,'Home/Repair/do_item','保存分配工作(ajax)','维修员管理'),
	  (157,'Home/Repair',2,'Home/Repair/do_area','保存分配区域(ajax)','维修员管理'),
	  (158,'Home/Repair',2,'Home/Repair/city','查找省下所有城市(ajax)','维修员管理'),
	  (159,'Home/Repair',2,'Home/Repair/county','查找城市下所有县(ajax)','维修员管理');
	
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',151,152,153,154,155,156,157,158') WHERE id = 3;