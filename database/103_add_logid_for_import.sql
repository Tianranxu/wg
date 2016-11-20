ALTER TABLE `fx_meter_degree`
ADD COLUMN `logId`  int(11) DEFAULT NULL COMMENT '导入记录id';

ALTER TABLE `fx_car_manage`
ADD COLUMN `logId`  int(11) DEFAULT NULL COMMENT '导入记录id';

ALTER TABLE `fx_property_pc_user`
ADD COLUMN `logId`  int(11) DEFAULT NULL COMMENT '导入记录id';