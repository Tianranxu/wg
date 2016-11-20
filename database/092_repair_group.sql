SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8;
DROP TABLE IF EXISTS `fx_repair_group`;
CREATE TABLE `fx_repair_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(100) COMMENT '分组名称',
  `type` tinyint(1) DEFAULT 1 COMMENT '分组类型 1:默认分组 2：自定分组 3：待审核',
  `cm_id` int(11) NOT NULL COMMENT '所属公司ID',
  `create_time` datetime COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='维修分组表';

DROP TABLE IF EXISTS `fx_repairer_device_temp`;
CREATE TABLE `fx_repairer_device_temp` (
  `cid` int(11) COMMENT '维修公司ID',
  `rid` int(11)  COMMENT '维修员ID',
  `dev_id` int(11) COMMENT '设备ID',
  KEY `cid` (`cid`),
  KEY `rid` (`rid`),
  KEY `dev_id` (`dev_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='维修员——设备中间表';

DROP TABLE IF EXISTS `fx_repairer_city`;
CREATE TABLE `fx_repairer_city` (
  `cid` int(11) COMMENT '维修公司ID',
  `rid` int(11) COMMENT '维修员ID',
  `city_id` int(11) COMMENT '城市ID',
  KEY `cid` (`cid`),
  KEY `rid` (`rid`),
  KEY `city_id` (`city_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='维修员——城市中间表';

alter table `fx_sys_repairer` add number varchar(50) comment '维修员编号';
alter table `fx_sys_repairer` add request datetime comment '维修员发起请求审核时间';


