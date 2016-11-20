CREATE TABLE `fx_device` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(50) NOT NULL COLLATE utf8_unicode_ci COMMENT '设备名称',
  `status` tinyint(2) DEFAULT 1 COMMENT '状态',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='故障设备表';

CREATE TABLE `fx_fault_phenomenon` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(100) NOT NULL COLLATE utf8_unicode_ci COMMENT '现象名称',
  `status` tinyint(2) DEFAULT 1 COMMENT '状态',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `did` int(11) NOT NULL COMMENT '故障设备id',
  PRIMARY KEY (`id`)
)  ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='故障现象表';

CREATE TABLE `fx_fault_reason` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(100) NOT NULL COLLATE utf8_unicode_ci COMMENT '现象名称',
  `status` tinyint(2) DEFAULT 1 COMMENT '状态',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `did` int(11) NOT NULL COMMENT '故障设备id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='故障原因表';

CREATE TABLE `fx_comp_device_temp` (
  `cc_id` int(11) DEFAULT NULL COMMENT '楼盘id',
  `rc_id` int(11) DEFAULT NULL COMMENT '维修公司id',
  `did` int(11) DEFAULT NULL COMMENT '故障设备id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='维修公司、物业公司和故障设备的中间表';

CREATE TABLE `fx_fault_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `fault_number` varchar(20) NOT NULL COLLATE utf8_unicode_ci COMMENT '故障编号',
  `status` tinyint(2) DEFAULT -1 COMMENT '状态(未修复或未接单是-1；正在修复或已接单是1；已修复是2；已评价是3；已转单是4；超时接单是-2；超时2次转后台是-3)',
  `contacts` varchar(30) DEFAULT NULL COMMENT '联系人',
  `ct_mobile` varchar(30) DEFAULT NULL COMMENT '联系电话',
  `submitter` varchar(100) DEFAULT NULL COMMENT '报修人(微信账户为openid,pc为管理员userID)',
  `type` tinyint(2) DEFAULT NULL COMMENT '报修类型(1为pc报障,2为微信报障)',
  `cid` int(11) DEFAULT NULL COMMENT '城市id',
  `cm_id` int(11) DEFAULT NULL COMMENT '物管公司id',
  `cc_id` int(11) DEFAULT NULL COMMENT '楼盘id',
  `bm_id` int(11) DEFAULT NULL COMMENT '楼栋id',
  `location` varchar(50) DEFAULT NULL COMMENT '详细地址',
  `did` int(11) DEFAULT NULL COMMENT '故障设备id',
  `fp_id` int(11) DEFAULT NULL COMMENT '故障现象id',
  `fr_id` int(11) DEFAULT NULL COMMENT '故障原因id',
  `origin_fd_id` int(11) DEFAULT NULL COMMENT '转单后原有单的id',
  `shift_reson` varchar(200) DEFAULT NULL COMMENT '转单原因',
  `remark` text DEFAULT NULL COLLATE utf8_unicode_ci COMMENT '报障时的备注',
  `rc_id` int(11) DEFAULT NULL COMMENT '维修公司id',
  `sr_id` int(11) DEFAULT NULL COMMENT '维修人员id',
  `description` text DEFAULT NULL COMMENT '修复后维修员填写的修复描述',
  `create_time` datetime DEFAULT NULL COMMENT '报障时间或故障创建时间',
  `catch_time` datetime DEFAULT NULL COMMENT '接单时间',
  `restore_time` datetime DEFAULT NULL COMMENT '修复时间',
  `shift_time` datetime DEFAULT NULL COMMENT '转单时间',
  `evaluate_time` datetime DEFAULT NULL COMMENT '评价时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='故障详情表';

CREATE TABLE `fx_fault_picture` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
    `pic_url` varchar(200) NOT NULL COLLATE utf8_unicode_ci COMMENT '图片url',
    `fd_id` int(11) NOT NULL COMMENT '故障id',
     PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='故障附件表';

CREATE TABLE `fx_evaluation` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `work_evaluation` varchar(10) DEFAULT NULL COMMENT '工作评分',
  `service_evaluation` varchar(10) DEFAULT NULL COMMENT '服务评分',
  `eva_content` text DEFAULT NULL COLLATE utf8_unicode_ci COMMENT '评价内容',
  `fd_id` int(11) NOT NULL COMMENT '故障id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='故障评价表';

