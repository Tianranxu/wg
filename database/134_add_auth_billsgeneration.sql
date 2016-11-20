INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) 
VALUES(172,'Home/Billsgeneration',2,'Home/Billsgeneration/page','账单生成数据分页(AJAX)','收费管理'),
(173,'Home/Billsgeneration',2,'Home/Billsgeneration/delete','删除账单(AJAX)','收费管理'),
(174,'Home/Billsgeneration',2,'Home/Billsgeneration/modifly','修改优惠和滞纳金额(AJAX)','收费管理'),
(175,'Home/Billsgeneration',2,'Home/Billsgeneration/publish','发布账单(AJAX)','收费管理'),
(176,'Home/Billsgeneration',2,'Home/Billsgeneration/payment','待缴费','收费管理'),
(177,'Home/Billsgeneration',2,'Home/Billsgeneration/search','待缴费查询(AJAX)','收费管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',172,173,174,175,176,177') WHERE id = 3;