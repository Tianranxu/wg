DROP TABLE IF EXISTS `fx_contract_manage`;
CREATE TABLE `fx_contract_manage` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID' ,
`number`  varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '合同编号' ,
`name`  varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '合同名称' ,
`start_date`  datetime NOT NULL COMMENT '起始日期' ,
`end_date`  datetime NOT NULL COMMENT '结束日期' ,
`sign_date`  datetime NOT NULL COMMENT '签约日期' ,
`custom_id`  int(11) UNSIGNED NOT NULL COMMENT '客源ID' ,
`cert_number`  varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '证件号码' ,
`room_id`  int(11) UNSIGNED NOT NULL COMMENT '房源ID' ,
`out_accounts_date`  datetime NOT NULL COMMENT '出账日期' ,
`deposit`  float(11,2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '押金' ,
`month`  int(11) UNSIGNED NOT NULL COMMENT '第几个月' ,
`days`  int(11) UNSIGNED NOT NULL COMMENT '第几日' ,
`rent`  float(11,2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '租金' ,
`cycle`  int(11) UNSIGNED NOT NULL COMMENT '租金周期' ,
`status`  tinyint(1) NOT NULL DEFAULT -1 COMMENT '状态  -2-已终止，-1已到期，1-已生效' ,
`is_increase`  tinyint(1) NOT NULL DEFAULT -1 COMMENT '是否递增  -1否，1-是' ,
`increase_cycle`  int(11) UNSIGNED NULL DEFAULT NULL ,
`increase_rent`  float(11,2) UNSIGNED NULL DEFAULT NULL COMMENT '加租金额' ,
`ins_rent_type`  tinyint(1) UNSIGNED NULL DEFAULT NULL COMMENT '加租金额类型  1-定额，2-百分比' ,
`marketer`  varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '营销人员' ,
`remark`  varchar(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '备注' ,
PRIMARY KEY (`id`),
INDEX `id` (`id`) USING BTREE COMMENT '主键ID',
INDEX `start_date` (`start_date`) USING BTREE COMMENT '起始日期',
INDEX `end_date` (`end_date`) USING BTREE COMMENT '结束如期',
INDEX `custom_id` (`custom_id`) USING BTREE COMMENT '客源ID',
INDEX `room_id` (`room_id`) USING BTREE COMMENT '房源ID'
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci
ROW_FORMAT=COMPACT
;

