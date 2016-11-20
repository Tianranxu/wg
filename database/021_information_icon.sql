
#--------------------------------------------------------------
SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8;

ALTER TABLE fx_sys_category add COLUMN icon_id int(11) DEFAULT 42 COMMENT '图标'; 
INSERT INTO `fx_sys_icon` VALUES ('42', '资讯图标', '/Public/images/message/0.png', '5', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('43', '资讯图标', '/Public/images/message/13.png', '5', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('44', '资讯图标', '/Public/images/message/14.png', '5', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('45', '资讯图标', '/Public/images/message/15.png', '5', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('46', '资讯图标', '/Public/images/message/16.png', '5', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('47', '资讯图标', '/Public/images/message/17.png', '5', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('48', '资讯图标', '/Public/images/message/18.png', '5', '2015-07-29 16:06:38', '2015-07-29 16:06:38');
INSERT INTO `fx_sys_icon` VALUES ('49', '资讯图标', '/Public/images/message/19.png', '5', '2015-07-29 16:06:38', '2015-07-29 16:06:38');

