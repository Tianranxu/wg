SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8;
DROP TABLE IF EXISTS `fx_sys_repairer`;
CREATE TABLE `fx_sys_repairer` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(100) COMMENT '姓名',
  `openid` varchar(100) COMMENT '维修员OPENID',
  `phone` varchar(50) COMMENT '联系手机号',
  `head` varchar(50) COMMENT '用户头像',
  `cm_id` int(11) COMMENT '维修公司ID',
  `gid` int(11) default NULL COMMENT '所属分组ID',
  `status` tinyint(1) DEFAULT 1 COMMENT '1:待审核 2：审核通过 3：审核不通过 -1:禁用',
  `exam_id` int(11) DEFAULT NULL COMMENT '审核人ID',
  `create_time` datetime COMMENT '创建时间',
  `last_log` datetime COMMENT '上一次登入时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='维修员表';

DROP TABLE IF EXISTS `fx_repairer_device_temp`;
CREATE TABLE `fx_repairer_device_temp` (
  `rid` int(11) NOT NULL COMMENT '维修员ID',
  `dev_id` int(11) NOT NULL COMMENT '设备ID',
  KEY `rid` (`rid`),
  KEY `dev_id` (`dev_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='维修员——设备中间表';

DROP TABLE IF EXISTS `fx_repairer_city`;
CREATE TABLE `fx_repairer_city` (
  `rid` int(11) NOT NULL COMMENT '维修员ID',
  `city_id` int(11) NOT NULL COMMENT '城市ID',
  KEY `rid` (`rid`),
  KEY `city_id` (`city_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='维修员——城市中间表';
