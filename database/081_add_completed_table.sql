SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8;
DROP TABLE IF EXISTS `fx_have_matter`;
DROP TABLE IF EXISTS `fx_completed_work`;
CREATE TABLE `fx_completed_work` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `openid` varchar(200) NOT NULL COMMENT '办事人的openid',
  `form_id` varchar(50) NOT NULL  COMMENT '填写的表单ID（mongodb）',
  `form_name` varchar(50)  COMMENT '表单名字',
  `committer` varchar(50) COMMENT '提交人姓名',
  `serial` varchar(50) NOT NULL COMMENT '流水号',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态（1：有效，-1：无效，默认1）',
  `create_time` datetime COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='已办事项';