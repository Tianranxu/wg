ALTER TABLE `fx_publicno` ADD COLUMN `custom_menu` varchar(8) NULL COMMENT '自定义菜单名称';
ALTER TABLE `fx_publicno` ADD COLUMN `custom_url` varchar(200) NULL COMMENT '菜单链接';
ALTER TABLE `fx_publicno` ADD COLUMN `custom_type` tinyint(1) DEFAULT '1' COMMENT '菜单类型（1：首页 2：外部链接）';
