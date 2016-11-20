#Dump of table fx_index_images
#--------------------------------------------------------------
SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8;

alter table fx_sys_category add sequence int(11) comment '新添加排序字段';
INSERT INTO `fx_sys_auth_rule` VALUES ('122', 'Home/Information', '1', 'Home/Information/index', '资讯排序', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('123', 'Home/Information', '2', 'Home/Information/organize', '保存资讯排序(ajax)', '1', '');
update fx_sys_role set rule_id = concat(rule_id, ',122,123') where id = '3';
