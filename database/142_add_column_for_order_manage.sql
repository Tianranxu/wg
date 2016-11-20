ALTER TABLE `fx_order_manage`
ADD COLUMN `car_id`  int(11) DEFAULT NULL COMMENT '车辆id' ,
MODIFY COLUMN `hm_id`  int(11) NULL COMMENT '房间id' AFTER `pay_type`;

ALTER TABLE `fx_carfee_charges`
MODIFY COLUMN `status`  tinyint(1) NULL DEFAULT '-1' COMMENT '状态 -1：已生成，未发布；1：已发布，未缴费；2：已缴费； ' AFTER `description`;

INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(275,'Home/Pay',1,'Home/Pay/carPayDetails','车辆收支明细','账单管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',275') WHERE id = 3;