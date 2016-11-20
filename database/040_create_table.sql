#Dump of table fx_index_images
#--------------------------------------------------------------
SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8;
DROP TABLE IF EXISTS `fx_property_pc_user`;
CREATE TABLE `fx_property_pc_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '客户名称',
  `pu_type` tinyint(1) DEFAULT '1' COMMENT '客户类型(1：个，2：企业，默认：1)',
  `contact_number` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '联系电话',
  `remark` text COLLATE utf8_unicode_ci COMMENT '备注',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态（1：有效，-1：无效，默认1）',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `modify_time` datetime DEFAULT NULL COMMENT '修改时间',
  `number` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '编号',
  `cm_id` int(11) DEFAULT NULL COMMENT '企业管理id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='住户表';