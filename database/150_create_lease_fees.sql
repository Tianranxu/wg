drop table if exists `fx_lease_bill`;
CREATE TABLE `fx_lease_bill` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID' ,
`number` varchar(50) NOT NULL COMMENT '账单纺号',
`payment` date NOT NULL COMMENT '交租时间',
`property` varchar(100) COMMENT '房产地址',
`payer` varchar(50) COMMENT '付款人',
`contact` varchar(50) COMMENT '付款人联系号码',
`cm_id` int(11) UNSIGNED NOT NULL COMMENT '企业ID',
`cc_id` int(11) UNSIGNED NOT NULL COMMENT '楼盘ID',
`bm_id` int(11) UNSIGNED NOT NULL COMMENT '楼栋ID',
`hm_id` int(11) UNSIGNED NOT NULL COMMENT '房间ID',
`contract_id`  int(11) UNSIGNED NOT NULL COMMENT '合约ID' ,
`discount` DECIMAL(10,2) default 0.00 COMMENT '优惠',
`delaying` DECIMAL(10,2) default 0.00 COMMENT '滞纳金',
`money` DECIMAL(10,2) default 0.00 COMMENT '金额',
`status`  tinyint(1) DEFAULT -1 COMMENT '-1生成未未缴费,1即将到期,3过期未缴费,2已缴费',
`create_time`  datetime NULL COMMENT '更新时间',
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci;