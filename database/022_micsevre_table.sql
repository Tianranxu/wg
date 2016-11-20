#Dump of table fx_index_images
#--------------------------------------------------------------
SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8;
CREATE TABLE `fx_sys_micserve` (
`id`  int(11) NOT NULL AUTO_INCREMENT COMMENT'主键',
`name`  varchar(100) default '未命名' COMMENT'服务名称',
`icon_id`  int(11) default 18 COMMENT'服务图标id',
`link_url`  varchar(100) NULL COMMENT'点击图标链接地址',
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(微服务)';

DROP TABLE IF EXISTS `fx_comp_serve`;
CREATE TABLE `fx_comp_serve` (
  `cm_id` int(11) NOT NULL COMMENT'企业ID',
  `serve_id` tinyint(1) NULL COMMENT'微服务ID',
  `ord_id` tinyint(1) NULL COMMENT'排序ID',
  KEY `cm_id` (`cm_id`),
  KEY `serve_id` (`serve_id`),
  KEY `ord_id` (`ord_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(企业与微服务中间表)';

INSERT INTO `fx_sys_micserve` VALUES ('1', '天气', '18', null);
INSERT INTO `fx_sys_micserve` VALUES ('2', '火车', '19', null);
INSERT INTO `fx_sys_micserve` VALUES ('3', '新闻', '20', null);
INSERT INTO `fx_sys_micserve` VALUES ('4', '快递', '21', null);
INSERT INTO `fx_sys_micserve` VALUES ('5', '彩票', '22', null);
INSERT INTO `fx_sys_micserve` VALUES ('6', '黄历', '23', null);
INSERT INTO `fx_sys_micserve` VALUES ('7', '百度', '24', null);
INSERT INTO `fx_sys_micserve` VALUES ('8', '音乐', '25', null);
INSERT INTO `fx_sys_micserve` VALUES ('9', '翻译', '26', null);

INSERT INTO `fx_comp_serve` VALUES ('1', '1', 0);
INSERT INTO `fx_comp_serve` VALUES ('1', '2', 1);
INSERT INTO `fx_comp_serve` VALUES ('1', '3', 2);
INSERT INTO `fx_comp_serve` VALUES ('1', '4', 3);
INSERT INTO `fx_comp_serve` VALUES ('1', '5', 4);
INSERT INTO `fx_comp_serve` VALUES ('1', '6', 5);
INSERT INTO `fx_comp_serve` VALUES ('1', '7', 6);
INSERT INTO `fx_comp_serve` VALUES ('1', '8', 7);
INSERT INTO `fx_comp_serve` VALUES ('1', '9', 8);

INSERT INTO `fx_sys_auth_rule` VALUES ('126', 'Home/Micserve', '1', 'Home/Micserve/index', '微服务排序', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('127', 'Home/Micserve', '2', 'Home/Micserve/sort', '保存微服务排序(ajax)', '1', '');
update fx_sys_role set rule_id = concat(rule_id, ',126,127') where id = '3';



