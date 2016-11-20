#Dump of table fx_index_images
#--------------------------------------------------------------

CREATE TABLE `fx_index_images` (
`id`  int(11) NOT NULL AUTO_INCREMENT COMMENT'主键',
`title`  varchar(100) default '未命名' COMMENT'图标名称',
`image_url`  varchar(100) default '__PUBLIC__/wechatImages/default.png' COMMENT'图标地址',
`cm_id` varchar(4) NULL COMMENT'企业ID',
`sort_id` tinyint(1) NULL COMMENT'排序ID',
`status` tinyint(1) default 1 COMMENT'排序ID',
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(首页排版)';


UPDATE `fx_sys_role` SET rule_id= concat(rule_id,',134') WHERE id=3;
#add a record of auth rule
INSERT INTO `fx_sys_auth_rule` VALUES ('134', 'Home/Publicno', '1', 'Home/Publicno/authrized', '授权成功返回页面', '1', '');