INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) 
VALUES(168,'Home/Billsgeneration',1,'Home/Billsgeneration/index','账单生成','收费管理'),
(169,'Home/Billsgeneration',2,'Home/Billsgeneration/build','查询楼栋','收费管理'),
(170,'Home/Billsgeneration',2,'Home/Billsgeneration/house','查询房间','收费管理'),
(171,'Home/Billsgeneration',2,'Home/Billsgeneration/generation','生成账单','收费管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',168,169,170,171') WHERE id = 3;
