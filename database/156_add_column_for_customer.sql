ALTER TABLE `fx_customer_source`
ADD COLUMN `name`  varchar(30) NULL COMMENT '客源姓名' AFTER `sign_time`;

INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(283,'Home/Customersource',1,'Home/Customersource/doAdd','添加客源','客源管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',283') WHERE id = 3;