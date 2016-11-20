INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(187,'Home/Lease',1,'Home/Lease/index','租赁账单','租赁账单管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(188,'Home/Lease',2,'Home/Lease/build','查询楼栋','租赁账单管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(189,'Home/Lease',2,'Home/Lease/house','查询房间','租赁账单管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(190,'Home/Lease',2,'Home/Lease/page','分页','租赁账单管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(191,'Home/Lease',2,'Home/Lease/delete','删除账单','租赁账单管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(192,'Home/Lease',2,'Home/Lease/modifly','修改金额（优惠和滞纳金）','租赁账单管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',187,188,189,190,191,192') WHERE id = 3;
