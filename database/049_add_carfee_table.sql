CREATE TABLE `fx_carfee_charges` (
      `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
      `car_id` int(11) DEFAULT NULL COMMENT '所属车辆id',
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
      `year` int(11) unsigned DEFAULT NULL COMMENT '年份',
      `month` int(11) unsigned DEFAULT NULL COMMENT '月份',
      `remark` varchar(280) CHARACTER SET utf8 DEFAULT NULL COMMENT '备注',
      PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1243 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(车辆费用管理)';

