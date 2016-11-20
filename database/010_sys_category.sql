/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : wg

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2015-09-10 18:00:31
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for fx_sys_category
-- ----------------------------
DROP TABLE IF EXISTS `fx_sys_category`;
CREATE TABLE `fx_sys_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(50) NOT NULL COMMENT '分类名称',
  `type` int(11) unsigned NOT NULL COMMENT '分类类型 1-图文信息 2-资讯 3-公告',
  `cm_id` int(11) unsigned DEFAULT NULL COMMENT '企业ID',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态 -1-禁用 1-正常',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
