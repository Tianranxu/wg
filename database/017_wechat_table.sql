#Dump of table fx_index_images
#--------------------------------------------------------------
SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8;
CREATE TABLE `fx_wechat_menus` (
`id`  int(11) NOT NULL AUTO_INCREMENT COMMENT'主键',
`title`  varchar(100) default '未命名' COMMENT'图标名称',
`image_url`  varchar(100) default '/Public/images/wechat/w5.png' COMMENT'图标地址',
`link_url`  varchar(100) NULL COMMENT'点击图标链接地址',
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(微信菜单)';

CREATE TABLE `fx_comp_menus` (
`id`  int(11) NOT NULL AUTO_INCREMENT COMMENT'主键',
`cm_id` int(11) NOT NULL COMMENT'企业ID',
`menu_id` tinyint(1) NULL COMMENT'菜单ID',
`ord_id` tinyint(1) NULL COMMENT'排序ID',
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(企业与菜单中间表)';

INSERT INTO `fx_wechat_menus` VALUES ('1', '社会资讯', '/Public/images/wechat/w5.png', null);
INSERT INTO `fx_wechat_menus` VALUES ('2', '账单缴费', '/Public/images/wechat/w50.png', null);
INSERT INTO `fx_wechat_menus` VALUES ('3', '公共报修', '/Public/images/wechat/w9.png', null);
INSERT INTO `fx_wechat_menus` VALUES ('4', '生活商圈', '/Public/images/wechat/w8.png', null);
INSERT INTO `fx_wechat_menus` VALUES ('5', '投诉建议',  '/Public/images/wechat/w7.png', null);
INSERT INTO `fx_wechat_menus` VALUES ('6', '联系我们', '/Public/images/wechat/w1.png', null);
INSERT INTO `fx_wechat_menus` VALUES ('7', '通知公告', '/Public/images/wechat/w2.png', null);
INSERT INTO `fx_wechat_menus` VALUES ('8', '房屋服务', '/Public/images/wechat/w4.png', null);
INSERT INTO `fx_wechat_menus` VALUES ('9', '微服务', '/Public/images/wechat/w3.png', null);

INSERT INTO `fx_comp_menus` VALUES ('1', '1', '1', 0);
INSERT INTO `fx_comp_menus` VALUES ('2', '1', '2', 1);
INSERT INTO `fx_comp_menus` VALUES ('3', '1', '3', 2);
INSERT INTO `fx_comp_menus` VALUES ('4', '1', '4', 3);
INSERT INTO `fx_comp_menus` VALUES ('5', '1', '5', 4);
INSERT INTO `fx_comp_menus` VALUES ('6', '1', '6', 5);
INSERT INTO `fx_comp_menus` VALUES ('7', '1', '7', 6);
INSERT INTO `fx_comp_menus` VALUES ('8', '1', '8', 7);
INSERT INTO `fx_comp_menus` VALUES ('9', '1', '9', 8);
INSERT INTO `fx_comp_menus` VALUES ('10', '1', '10', 9);

INSERT INTO `fx_sys_auth_rule` VALUES ('111', 'Home/Wechatuser', '1', 'Home/Wechatuser/index', '微信用户管理', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('113', 'Home/Wechatuser', '2', 'Home/Wechatuser/moveTogroup', '移动用户到某个分组', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('114', 'Home/Wechatuser', '2', 'Home/Wechatuser/modifyRemark', '设置备注', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('115', 'Home/Wechatuser', '2', 'Home/Wechatuser/create', '创建分组', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('116', 'Home/Wechatuser', '2', 'Home/Wechatuser/delGroup', '删除分组', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('117', 'Home/Wechatuser', '2', 'Home/Wechatuser/editGroup', '编辑分组', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('118', 'Home/Homecompose', '1', 'Home/Homecompose/index', '微信首页排版', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('119', 'Home/Homecompose', '1', 'Home/Homecompose/compose', '微信排版(ajax)', '1', '');

INSERT INTO `fx_sys_auth_rule` VALUES ('120', 'Home/Wechatuser', '2', 'Home/Wechatuser/load_more', '加载更多(ajax)', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('121', 'Home/Wechatuser', '2', 'Home/Wechatuser/load_specific', '显示特定分组(ajax)', '1', '');


update fx_sys_role set rule_id = concat(rule_id, ',111,113,114,115,116,117,118,119,120,121') where id = '3';
