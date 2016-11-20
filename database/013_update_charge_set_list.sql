/*
Navicat MySQL Data Transfer

Source Server         : 初云
Source Server Version : 50543
Source Host           : 119.29.10.40:3306
Source Database       : wg

Target Server Type    : MYSQL
Target Server Version : 50543
File Encoding         : 65001

Date: 2015-09-02 18:39:58
*/

SET FOREIGN_KEY_CHECKS=0;


INSERT INTO `fx_sys_auth_rule` VALUES ('110', 'Home/Propertycharges', '2', 'Home/Propertycharges/set_list', '保存收费设置', '1', '');

update fx_sys_role set rule_id = concat(rule_id, ',110') where id = '2';
update fx_sys_role set rule_id = concat(rule_id, ',110') where id = '3';
