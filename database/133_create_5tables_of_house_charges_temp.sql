DROP TABLE `fx_house_charges_temp` IF EXISTS;

CREATE TABLE `fx_house_charges_temp_1` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID' ,
`cm_id`  int(11) UNSIGNED NOT NULL COMMENT '企业ID' ,
`hm_id`  int(11) UNSIGNED NOT NULL COMMENT '房间ID' ,
`ch_id`  int(11) UNSIGNED NOT NULL COMMENT '费项ID' ,
`status`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '绑定状态 -1-解绑，1-绑定，默认1' ,
`update_time`  datetime NULL COMMENT '更新时间' ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci
ROW_FORMAT=COMPACT
;

CREATE TABLE `fx_house_charges_temp_2` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID' ,
`cm_id`  int(11) UNSIGNED NOT NULL COMMENT '企业ID' ,
`hm_id`  int(11) UNSIGNED NOT NULL COMMENT '房间ID' ,
`ch_id`  int(11) UNSIGNED NOT NULL COMMENT '费项ID' ,
`status`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '绑定状态 -1-解绑，1-绑定，默认1' ,
`update_time`  datetime NULL COMMENT '更新时间' ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci
ROW_FORMAT=COMPACT
;

CREATE TABLE `fx_house_charges_temp_3` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID' ,
`cm_id`  int(11) UNSIGNED NOT NULL COMMENT '企业ID' ,
`hm_id`  int(11) UNSIGNED NOT NULL COMMENT '房间ID' ,
`ch_id`  int(11) UNSIGNED NOT NULL COMMENT '费项ID' ,
`status`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '绑定状态 -1-解绑，1-绑定，默认1' ,
`update_time`  datetime NULL COMMENT '更新时间' ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci
ROW_FORMAT=COMPACT
;

CREATE TABLE `fx_house_charges_temp_4` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID' ,
`cm_id`  int(11) UNSIGNED NOT NULL COMMENT '企业ID' ,
`hm_id`  int(11) UNSIGNED NOT NULL COMMENT '房间ID' ,
`ch_id`  int(11) UNSIGNED NOT NULL COMMENT '费项ID' ,
`status`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '绑定状态 -1-解绑，1-绑定，默认1' ,
`update_time`  datetime NULL COMMENT '更新时间' ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci
ROW_FORMAT=COMPACT
;

CREATE TABLE `fx_house_charges_temp_5` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID' ,
`cm_id`  int(11) UNSIGNED NOT NULL COMMENT '企业ID' ,
`hm_id`  int(11) UNSIGNED NOT NULL COMMENT '房间ID' ,
`ch_id`  int(11) UNSIGNED NOT NULL COMMENT '费项ID' ,
`status`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '绑定状态 -1-解绑，1-绑定，默认1' ,
`update_time`  datetime NULL COMMENT '更新时间' ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci
ROW_FORMAT=COMPACT
;