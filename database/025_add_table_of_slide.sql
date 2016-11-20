#Dump of table fx_slide
#--------------------------------------------------------------

CREATE TABLE `fx_slide`(
`id`  int(11) NOT NULL AUTO_INCREMENT COMMENT'主键',
`cm_id`  int(11) NOT NULL COMMENT'公司id',
`url`  varchar(200) NOT NULL COMMENT'图片地址',
`order`  tinyint(1) NOT NULL COMMENT'点击图标链接地址',
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(幻灯片)';