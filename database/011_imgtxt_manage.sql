/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : wg

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2015-09-10 18:01:15
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for fx_imgtxt_manage
-- ----------------------------
DROP TABLE IF EXISTS `fx_imgtxt_manage`;
CREATE TABLE `fx_imgtxt_manage` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `media_id` varchar(100) NOT NULL COMMENT '图文信息mediaID',
  `category_id` int(11) unsigned NOT NULL COMMENT '所属分类ID',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态 -1-禁用 1-正常',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
