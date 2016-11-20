INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(301,'Home/Lease',1,'Home/Lease/count','租赁统计','租赁管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',301') WHERE id = 3;
####lease_bill表加两个字段
ALTER TABLE `fx_lease_bill`
ADD COLUMN `year`  int(11) NOT NULL AFTER `status`,
ADD COLUMN `month`  int(11) NOT NULL AFTER `year`;