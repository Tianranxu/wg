CREATE TABLE `fx_room_source` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID' ,
`parent_id`  varchar(50) DEFAULT NULL COMMENT '房源的所属,形式为：公司id_楼盘id_楼宇id_房间id',
`hm_id` int(11) UNSIGNED NOT NULL COMMENT '房间id',
`type`  tinyint(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT '类型 1-纯住宅 2-纯办公 3-住宅改办公 默认为1' ,
`room_type`  tinyint(2) DEFAULT 1 COMMENT '户型 1-一室 2-二室 3-三室 4-四室 5-四室以上 99-其它' ,
`furnish_type` tinyint(2) DEFAULT 2 COMMENT '装修 1-毛坯 2-简装 3-精装 4-豪装 99-其它' ,
`follow_type` tinyint(2) DEFAULT 1 COMMENT '跟进方式 1-普通 2-重点 3-放弃' ,
`status` tinyint(2) DEFAULT 1 COMMENT '状态 1-待租，未签约 2-已租，已签约 3-终止托管' ,
`cm_id` int(11) DEFAULT NULL COMMENT '公司id',
`start_time` datetime NOT NULL COMMENT '托管开始时间' ,
`sign_time` datetime NOT NULL COMMENT '托管登记时间' ,
`end_time` datetime NOT NULL COMMENT '托管结束时间' ,
`limit` int(10) NOT NULL COMMENT '托管期限(以月为单位)' ,
`trustee_fee` int(10) UNSIGNED NOT NULL COMMENT '托管价格（元/月）' ,
`deposit` int(10) UNSIGNED NOT NULL COMMENT '押金' ,
`rent` int(10) UNSIGNED NOT NULL COMMENT '租金 元/月' ,
`furniture` varchar(60) DEFAULT NULL COMMENT '配套设施(ids)' ,
`isIncreasing` tinyint(1) DEFAULT -1 COMMENT '是否递增 1:是  -1:否',
`increasing_type` tinyint(1) DEFAULT 1 COMMENT '递增类型 1:定额 2:百分比',
`increasing_price` int(12) DEFAULT NULL COMMENT '递增金额(每期)',
`increasing_cycle` int(10) DEFAULT NULL COMMENT '递增周期(以月为单位)', 
`uid` int(11) UNSIGNED DEFAULT NULL COMMENT '录入用户id',
`remark` varchar(300) DEFAULT NULL COMMENT '备注',
`update_time`  datetime NULL COMMENT '更新时间' ,
PRIMARY KEY (`id`),
INDEX `parent_id` (`parent_id`) USING BTREE COMMENT '房源所属ID',
INDEX `hm_id` (`hm_id`) USING BTREE COMMENT '房间ID'
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci
ROW_FORMAT=COMPACT
;

CREATE TABLE `fx_customer_source` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID' ,
`customer_id` int(11) UNSIGNED NOT NULL COMMENT '客户id',
`cc_id` int(11) UNSIGNED NOT NULL COMMENT '楼盘id，即楼盘需求',
`room_type`  tinyint(2) DEFAULT 1 COMMENT '户型需求 1-一室 2-二室 3-三室 4-四室 5-四室以上 99-其它' ,
`type`  tinyint(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT '类型需求 1-纯住宅 2-纯办公 3-住宅改办公 默认为1' ,
`cm_id` int(11) DEFAULT NULL COMMENT '公司id',
`furnish_type` tinyint(2) DEFAULT 2 COMMENT '装修 1-毛坯 2-简装 3-精装 4-豪装 99-其它' ,
`area` int(5) DEFAULT 0 COMMENT '面积需求',
`status` tinyint(2) DEFAULT 1 COMMENT '状态 1-未签约，2-已签约 3-终止委托',
`price` int(11) DEFAULT NULL COMMENT '价格区间,元',
`other_demand` varchar(100) DEFAULT NULL COMMENT '其他需求',
`intention` tinyint(1) DEFAULT 1 COMMENT '性质（意向）1-求租 2-求购',
`uid` int(11) UNSIGNED DEFAULT NULL COMMENT '录入用户id',
`remark` varchar(300) DEFAULT NULL COMMENT '备注',
`sign_time` datetime NOT NULL COMMENT '登记时间',
PRIMARY KEY (`id`),
INDEX `customer_id` (`customer_id`) USING BTREE COMMENT '客户id',
INDEX `uid` (`uid`) USING BTREE COMMENT '录入用户id'
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci
ROW_FORMAT=COMPACT
;

CREATE TABLE `fx_furniture` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID' ,
`name` varchar(30) NOT NULL COMMENT '名称',
`update_time` datetime DEFAULT NULL COMMENT '更新时间',
`status` tinyint(1) DEFAULT 1 COMMENT '状态 1-正常 -1-禁用',
PRIMARY KEY (`id`),
INDEX `id` (`id`) USING BTREE COMMENT '配置设施id'
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci
ROW_FORMAT=COMPACT
;

CREATE TABLE `fx_room_picture` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID' ,
`rs_id` int(11) NOT NULL COMMENT '房源id',
`url` varchar(200) NOT NULL COMMENT '图片的url',
PRIMARY KEY (`id`),
INDEX `rs_id` (`rs_id`) USING BTREE COMMENT '房源id'
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci
ROW_FORMAT=COMPACT
;

CREATE TABLE `fx_follow` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID' ,
`rs_id` int(11) DEFAULT NULL COMMENT '房源id',
`customer_id` int(11) DEFAULT NULL COMMENT '客源id',
`uid` int(11) UNSIGNED DEFAULT NULL COMMENT '录入用户id',
`msg` varchar(200) NOT NULL COMMENT '跟进信息',
`create_time` datetime DEFAULT NULL COMMENT '创建时间',
PRIMARY KEY (`id`),
INDEX `rs_id` (`rs_id`) USING BTREE COMMENT '房源id',
INDEX `customer_id` (`customer_id`) USING BTREE COMMENT '客源id'
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci
ROW_FORMAT=COMPACT
;

