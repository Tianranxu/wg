CREATE TABLE `fx_contract_picture` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID' ,
`contract_id`  int(11) UNSIGNED NOT NULL COMMENT '合约ID' ,
`pic_url`  varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '图片路径' ,
PRIMARY KEY (`id`),
INDEX `contract_id` (`contract_id`) USING BTREE COMMENT '合约ID'
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci
ROW_FORMAT=COMPACT
;

