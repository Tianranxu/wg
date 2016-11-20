#Dump of table fx_index_images
#--------------------------------------------------------------
SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8;

INSERT INTO `fx_sys_icon` VALUES ('17', '系统图标', '/Public/images/type_icon/1.png', '1', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('18', '系统图标', '/Public/images/type_icon/0.png', '1', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('19', '系统图标', '/Public/images/type_icon/2.png', '1', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('20', '系统图标', '/Public/images/type_icon/3.png', '1', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('21', '系统图标', '/Public/images/type_icon/4.png', '1', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('22', '系统图标', '/Public/images/type_icon/5.png', '1', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('23', '系统图标', '/Public/images/type_icon/6.png', '1', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('24', '系统图标', '/Public/images/type_icon/7.png', '1', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('25', '系统图标', '/Public/images/type_icon/8.png', '1', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('26', '系统图标', '/Public/images/type_icon/9.png', '1', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('27', '系统图标', '/Public/images/type_icon/10.png', '1', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('28', '系统图标', '/Public/images/type_icon/11.png', '1', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('29', '系统图标', '/Public/images/type_icon/12.png', '1', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('30', '系统图标', '/Public/images/type_icon/13.png', '1', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('31', '系统图标', '/Public/images/type_icon/14.png', '1', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('32', '系统图标', '/Public/images/type_icon/15.png', '1', '2015-07-29 16:06:38', '2015-07-29 16:06:38');

INSERT INTO `fx_sys_icon` VALUES ('33', '菜单图标', '/Public/images/wechat/u22.png', '4', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('34', '菜单图标', '/Public/images/wechat/u28.png', '4', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('35', '菜单图标', '/Public/images/wechat/w1.png', '4', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('36', '菜单图标', '/Public/images/wechat/w2.png', '4', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('37', '菜单图标', '/Public/images/wechat/w3.png', '4', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('38', '菜单图标', '/Public/images/wechat/w4.png', '4', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('39', '菜单图标', '/Public/images/wechat/w5.png', '4', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('40', '菜单图标', '/Public/images/wechat/w6.png', '4', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('41', '菜单图标', '/Public/images/wechat/w7.png', '4', '2015-07-29 16:06:38', '2015-07-29 16:06:38');



INSERT INTO `fx_sys_auth_rule` VALUES ('124', 'Home/Icon', '1', 'Home/Icon/index', '图标选择页面', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('125', 'Home/Icon', '2', 'Home/Icon/modify_icon', '保存图标修改(ajax)', '1', '');

update fx_sys_role set rule_id = concat(rule_id, ',124,125') where id = '3';

alter table fx_wechat_menus CHANGE image_url icon_id int(11);
truncate table `fx_wechat_menus`;
INSERT INTO `fx_wechat_menus` VALUES ('1', '社会资讯', '33', '/icon/index');
INSERT INTO `fx_wechat_menus` VALUES ('2', '账单缴费', '34', '/icon/index');
INSERT INTO `fx_wechat_menus` VALUES ('3', '公共报修', '35', '/icon/index');
INSERT INTO `fx_wechat_menus` VALUES ('4', '生活商圈', '36', '/icon/index');
INSERT INTO `fx_wechat_menus` VALUES ('5', '投诉建议', '37', '/icon/index');
INSERT INTO `fx_wechat_menus` VALUES ('6', '联系我们', '38', '/icon/index');
INSERT INTO `fx_wechat_menus` VALUES ('7', '通知公告', '39', '/icon/index');
INSERT INTO `fx_wechat_menus` VALUES ('8', '房屋服务', '40', '/icon/index');
INSERT INTO `fx_wechat_menus` VALUES ('9', '微服务', '41', '/icon/index');
