DROP TABLE IF EXISTS `fx_sys_notice`;
CREATE TABLE `fx_sys_notice` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `content` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '内容',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '类型  1-超时未接故障，2-意见反馈，3-待审核表单，4-微信用户缴费',
  `cm_id` int(11) unsigned NOT NULL COMMENT '企业ID',
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '#' COMMENT '消息所指url',
  `user_id` int(11) unsigned NOT NULL COMMENT '接收人，即用户ID',
  `status` tinyint(1) NOT NULL DEFAULT '-1' COMMENT '状态  -1-未阅读或未处理，1-已阅读或已处理',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `type` (`type`) USING BTREE,
  KEY `cm_id` (`cm_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;