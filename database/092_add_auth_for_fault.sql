INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES (258,'Home/Fault',1,'Home/Fault/detail','故障详情','故障管理');

ALTER TABLE `fx_fault_details` ADD COLUMN `finish_time` datetime DEFAULT NULL COMMENT '结单时间';