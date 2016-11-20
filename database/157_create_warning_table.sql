drop table if exists `fx_sys_warning`;
CREATE TABLE `fx_sys_warning` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID' ,
`cm_id` int(11) NOT NULL COMMENT '企业ID',
`type` tinyint(1) DEFAULT 1 COMMENT '1,合约, 2,账单 3,房源',
`days` int(11) default 7 COMMENT '预警天数',
`status` tinyint(1) DEFAULT 1 COMMENT '1正常,2,无效',
`create_time`  datetime NULL COMMENT '更新时间',
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci;