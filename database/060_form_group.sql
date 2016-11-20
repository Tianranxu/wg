CREATE TABLE `fx_form_group_temp` (
      `user_id` int(11) NOT NULL COMMENT '当前使用者id',
      `group_id` int(11) NOT NULL COMMENT '自定义分组id',
      `form_id` varchar(100) NOT NULL COMMENT '表单id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(表单分组表)';

ALTER TABLE `fx_sys_group` DROP COLUMN `group_type`;
ALTER TABLE `fx_sys_group` ADD COLUMN `cm_id` int(11) DEFAULT NULL COMMENT '公司id';