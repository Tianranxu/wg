INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) 
VALUES(178,'Home/Parking',1,'Home/Parking/index','停车费生成','停车费管理'),
(179,'Home/Parking',2,'Home/Parking/card','查找楼盘停车卡（AJAX）','停车费管理'),
(180,'Home/Parking',2,'Home/Parking/generation','生成账单（AJAX）','停车费管理'),
(181,'Home/Parking',2,'Home/Parking/page','数据分页(AJAX)','停车费管理'),
(182,'Home/Parking',2,'Home/Parking/delete','删除账单(AJAX)','停车费管理'),
(183,'Home/Parking',2,'Home/Parking/modifly','修改优惠和滞纳金(AJAX)','停车费管理'),
(184,'Home/Parking',2,'Home/Parking/publish','发布账单(AJAX)','停车费管理'),
(185,'Home/Parking',2,'Home/Parking/payment','待缴费','停车费管理'),
(186,'Home/Parking',2,'Home/Parking/search','搜索账单(AJAX)','停车费管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',178,179,180,181,182,183,184,185,186') WHERE id = 3;