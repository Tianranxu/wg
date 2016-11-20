# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.6.22)
# Database: wg
# Generation Time: 2015-09-02 11:56:36 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

# Dump of table fx_accounts_charges
# ------------------------------------------------------------


CREATE TABLE `fx_accounts_charges` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `hm_id` int(11) DEFAULT NULL COMMENT '所属房间id',
  `cm_id` int(11) DEFAULT NULL COMMENT '所属收费项目id',
  `money` decimal(10,2) DEFAULT NULL COMMENT '金额',
  `number` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '编号',
  `bill_time` datetime DEFAULT NULL COMMENT '生成账单时间',
  `preferential_money` decimal(10,2) DEFAULT NULL COMMENT '优惠金额',
  `penalty` decimal(10,2) DEFAULT NULL COMMENT '滞纳金',
  `description` varchar(280) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '优惠说明',
  `status` tinyint(1) DEFAULT '-1' COMMENT '状态 -1：已生成，未出 1-录入优惠(优惠状态) 2-已出账单，未缴费 3-已缴费',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `modify_time` datetime DEFAULT NULL COMMENT '修改时间',
  `is_preferential` tinyint(1) DEFAULT '1' COMMENT '是否优惠(1：没有优惠，-1：删除优惠，2：增加优惠，默认1)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(应收费用管理)';



# Dump of table fx_building_manage
# ------------------------------------------------------------

CREATE TABLE `fx_building_manage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '楼宇编号',
  `name` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '楼栋名称',
  `remark` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '备注',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态（1：有效，-1：无效，默认1）',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `modify_time` datetime DEFAULT NULL COMMENT '修改时间',
  `cc_id` int(11) DEFAULT NULL COMMENT '所属楼盘',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(¥';



# Dump of table fx_car_manage
# ------------------------------------------------------------

CREATE TABLE `fx_car_manage` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `card_number` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '卡号',
  `car_number` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '车牌号',
  `user` varchar(26) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '使用人',
  `mobile_number` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '手机号码',
  `cc_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '所属楼盘',
  `address` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '地址',
  `description` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '描述',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态（1：有效，-1：无效，默认：1）',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `modify_time` datetime DEFAULT NULL COMMENT '修改时间',
  `monthly` decimal(10,2) DEFAULT NULL COMMENT '月租',
  `cycle` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '周期(多个周期用逗号【，】分开)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(车辆管理)';



# Dump of table fx_charges_manage
# ------------------------------------------------------------

CREATE TABLE `fx_charges_manage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '费项名称',
  `price` decimal(10,2) DEFAULT NULL COMMENT '单价',
  `remark` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '备注',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态（1：有效，-1：无效，默认1）',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `modify_time` datetime DEFAULT NULL COMMENT '修改时间',
  `charging_cycle` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '计费周期(多个id用逗号，分隔)',
  `measure_style` int(11) DEFAULT NULL COMMENT '计量方式（）',
  `category` int(11) DEFAULT NULL COMMENT '项目类别',
  `billing` int(11) DEFAULT NULL COMMENT '计费方式',
  `cm_id` int(11) DEFAULT NULL COMMENT '企业管理id',
  `number` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '编号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(收费项目管理)';



# Dump of table fx_charges_setting
# ------------------------------------------------------------

CREATE TABLE `fx_charges_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `cc_id` int(11) DEFAULT NULL COMMENT '楼盘id',
  `bm_id` int(11) DEFAULT NULL COMMENT '楼宇id',
  `hm_id` int(11) DEFAULT NULL COMMENT '房间id',
  `chm_id` int(11) DEFAULT NULL COMMENT '所属收费项目',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态（1：有效，-1：无效，默认：1）',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `modify_time` datetime DEFAULT NULL COMMENT '修改时间',
  `price` decimal(10,0) DEFAULT NULL COMMENT '价格',
  `charging_cycle` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '计费周期',
  `remark` varchar(260) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '备注',
  `cm_id` int(11) DEFAULT NULL COMMENT '企业ID',
  `user_id` int(11) NOT NULL COMMENT '添加设置用户ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(收费设置表)';



# Dump of table fx_city
# ------------------------------------------------------------

CREATE TABLE `fx_city` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `status` int(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `nidp` (`pid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table fx_community_comp
# ------------------------------------------------------------

CREATE TABLE `fx_community_comp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '楼盘编号',
  `name` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '楼盘名称',
  `cm_id` int(11) DEFAULT NULL COMMENT '所属物业公司',
  `address_id` int(11) DEFAULT NULL COMMENT '楼盘所属地',
  `remark` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '备注',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态(1：有效，-1：无效，默认1)',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `modify_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(¥';



# Dump of table fx_comp_group_temp
# ------------------------------------------------------------

CREATE TABLE `fx_comp_group_temp` (
  `group_id` int(11) DEFAULT '0' COMMENT '组ID',
  `cm_id` int(11) DEFAULT NULL COMMENT '企业ID',
  `user_id` int(11) DEFAULT '0' COMMENT '用户表ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='企业管理和组中间表';



# Dump of table fx_comp_manage
# ------------------------------------------------------------

CREATE TABLE `fx_comp_manage` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '企业名称',
  `description` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '企业简介',
  `contacts` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '联系人',
  `office_phone` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '办公电话',
  `mobile_num` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '手机号码',
  `e_mail` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '邮件',
  `remark` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '备注',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态(1：有效，-1：无效，默认有效)',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `modify_time` datetime DEFAULT NULL COMMENT '修改时间',
  `icon_id` int(11) DEFAULT NULL COMMENT '所属图标',
  `cm_type` tinyint(1) DEFAULT '1' COMMENT '物业公司：1，维修公司：2，其他：3',
  `number` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '企业管理编号，非空和唯一性',
  `is_delete` tinyint(1) DEFAULT '1' COMMENT '是否删除企业，1：否，-1：是。删除后页面不显示。',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `status` (`status`),
  KEY `type` (`cm_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(企业管理)';



# Dump of table fx_contract_manage
# ------------------------------------------------------------

CREATE TABLE `fx_contract_manage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '合同编号',
  `name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '合同名称',
  `start_date` datetime DEFAULT NULL COMMENT '起始日期',
  `end_date` datetime DEFAULT NULL COMMENT '结束日期',
  `signing_date` datetime DEFAULT NULL COMMENT '签约日期',
  `pu_id` int(11) DEFAULT NULL COMMENT '客户(物业管理PC端用户ID)',
  `remark` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '备注',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态(1:有效，-1：无效，默认1)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(合同管理)';



# Dump of table fx_data_dictionary
# ------------------------------------------------------------

CREATE TABLE `fx_data_dictionary` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `dd_type` tinyint(1) DEFAULT '1' COMMENT '类型（1：费用设置，2，其它【后续扩展】）默认为1',
  `dd_value` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '值',
  `status` tinyint(1) DEFAULT '1' COMMENT '1：有效，-1：无效，默认1',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `modify_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(数据字典表)';



# Dump of table fx_house_manage
# ------------------------------------------------------------

CREATE TABLE `fx_house_manage` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `number` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '房号',
  `hm_number` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '房间唯一编号',
  `floor` int(4) DEFAULT NULL COMMENT '楼层',
  `mobile_number` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '业主手机号码',
  `name` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '业主姓名',
  `building_area` float DEFAULT NULL COMMENT '建筑面积',
  `inside_area` float DEFAULT NULL COMMENT '套内面积',
  `flat_area` float DEFAULT NULL COMMENT '公摊面积',
  `charging_area` float DEFAULT NULL COMMENT '计费面积',
  `description` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '描述',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态（1：空置，-1：无效，2-入住，默认1）',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `modify_time` datetime DEFAULT NULL COMMENT '修改时间',
  `cm_id` int(11) DEFAULT NULL COMMENT '所属合同',
  `bm_id` int(11) DEFAULT NULL COMMENT '所属楼宇',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(房间)';



# Dump of table fx_house_user_temp
# ------------------------------------------------------------

CREATE TABLE `fx_house_user_temp` (
  `hm_id` int(11) DEFAULT NULL COMMENT '房间ID',
  `pu_id` int(11) DEFAULT NULL COMMENT '物管用户id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(����Ϳͻ��м��)';



# Dump of table fx_import_log
# ------------------------------------------------------------

CREATE TABLE `fx_import_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '文件名称',
  `code` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '导入账号',
  `user_name` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '导入人员',
  `import_time` datetime DEFAULT NULL COMMENT '导入时间',
  `success` int(10) DEFAULT NULL COMMENT '导入成功数',
  `failures` int(10) DEFAULT NULL COMMENT '导入失败数',
  `error_no` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '错误信息(多个id用逗号分隔)',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态(1:有效，-1：无效，默认有效1)',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `modify_time` datetime DEFAULT NULL COMMENT '修改时间',
  `cm_id` int(11) DEFAULT NULL COMMENT '所属企业id',
  `il_type` tinyint(2) DEFAULT NULL COMMENT '导入类型',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(导入日志表)';



# Dump of table fx_import_manage
# ------------------------------------------------------------

CREATE TABLE `fx_import_manage` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `cc_id` int(11) DEFAULT NULL COMMENT '楼盘id',
  `file_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '文件名',
  `file_path` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '文件路径',
  `im_type` tinyint(1) DEFAULT NULL COMMENT '类型(1：仪表读数 2：车辆)',
  `cm_id` int(11) DEFAULT NULL COMMENT '公司id',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态(1：有效，-1：无效，默认1)',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `modify_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(导入管理)';



# Dump of table fx_meter_degree
# ------------------------------------------------------------

CREATE TABLE `fx_meter_degree` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `year` char(4) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '年份',
  `month` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '月份',
  `degree` decimal(10,2) DEFAULT NULL COMMENT '度数',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `modify_time` datetime DEFAULT NULL COMMENT '修改时间',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态(1：有效，-1：无效，默认：1)',
  `hm_id` int(11) DEFAULT NULL COMMENT '所属房间',
  `ms_id` int(11) DEFAULT NULL COMMENT '仪表类型表',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(仪表度数管理)';



# Dump of table fx_meter_setting_category
# ------------------------------------------------------------

CREATE TABLE `fx_meter_setting_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '名称',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态（1：有效，-1：无效，默认：1）',
  `description` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '说明',
  `unit` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '单位',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `modify_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table fx_property_pc_user
# ------------------------------------------------------------

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(���PC���û�)';



# Dump of table fx_sys_auth_rule
# ------------------------------------------------------------

CREATE TABLE `fx_sys_auth_rule` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '规则id,自增主键',
  `module` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '规则所属module',
  `type` tinyint(1) DEFAULT NULL COMMENT '1-url,2-主菜单',
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '规则唯一英文标识',
  `title` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '规则中文描述',
  `status` tinyint(2) DEFAULT '1' COMMENT '是否有效(-1:无效,1:有效)',
  `rule_cond` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT ' 规则附加条件',
  PRIMARY KEY (`id`),
  KEY `type` (`type`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(权限规则)';



# Dump of table fx_sys_group
# ------------------------------------------------------------

CREATE TABLE `fx_sys_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `module_id` int(11) DEFAULT NULL COMMENT '所属模块',
  `type` tinyint(2) DEFAULT '1' COMMENT '组类型（1：默认组，2,：自定义组 3: 停止服务组），默认为1',
  `title` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '中文名称',
  `description` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '描述信息',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态（1：有效，-1：无效，默认为1）',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `modify_time` datetime DEFAULT NULL COMMENT '修改时间',
  `user_id` int(11) DEFAULT NULL COMMENT '所属用户',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='组';



# Dump of table fx_sys_icon
# ------------------------------------------------------------

CREATE TABLE `fx_sys_icon` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '图标名称',
  `url_address` varchar(80) COLLATE utf8_unicode_ci NOT NULL COMMENT '图标存储地址',
  `type` tinyint(1) NOT NULL COMMENT '图标类型（1：系统图标，2：企业管理图标 3:用户头像）',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `modify_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `id` (`id`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='图标管理)';



# Dump of table fx_sys_role
# ------------------------------------------------------------

CREATE TABLE `fx_sys_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '名称',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态（1：有效，-1：无效）',
  `description` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '角色描述',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `modify_time` datetime DEFAULT NULL COMMENT '修改时间',
  `rule_id` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '所属规则（多个规则逗号隔离）',
  `type` tinyint(1) DEFAULT '2' COMMENT '类型（1：系统角色，2：业务角色）',
  PRIMARY KEY (`id`),
  KEY `id` (`id`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `rule_id` (`rule_id`(255)) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(角色)';



# Dump of table fx_sys_user
# ------------------------------------------------------------

CREATE TABLE `fx_sys_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '姓名',
  `code` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '账号',
  `Contact_number` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '联系电话',
  `fixed_password` char(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '固定密码',
  `dyn_password` char(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '动态密码',
  `sex` tinyint(1) DEFAULT '1' COMMENT '性别(1:男，2：女，3：未知，默认1)',
  `photo` int(11) DEFAULT NULL COMMENT '头像',
  `mail` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '邮件',
  `QQ` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'QQ',
  `address` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '地址',
  `remark` text COLLATE utf8_unicode_ci COMMENT '备注',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态（1：有效，-1无效，默认1）',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `modify_time` datetime DEFAULT NULL COMMENT '修改时间',
  `reg_ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '注册IP',
  `last_login_ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '最后登录IP',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
  `sms_times` tinyint(2) DEFAULT NULL COMMENT '当天发送短信的次数',
  `invite_per_id` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '邀请人id,多个人用逗号【，】隔开',
  PRIMARY KEY (`id`),
  KEY `Index_1` (`id`),
  KEY `status` (`status`),
  KEY `sex` (`sex`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='物管系统用户表';



# Dump of table fx_user_role_temp
# ------------------------------------------------------------

CREATE TABLE `fx_user_role_temp` (
  `user_id` int(11) DEFAULT NULL COMMENT '主键',
  `role_id` int(11) DEFAULT NULL COMMENT '角色表主键',
  `cm_id` int(11) DEFAULT NULL COMMENT '企业管理id',
  KEY `user_id` (`user_id`),
  KEY `role_id` (`role_id`),
  KEY `cm_id` (`cm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(角色和用户中间表)';




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
