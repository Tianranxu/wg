ALTER TABLE `fx_comp_menus` ADD COLUMN `nomen` varchar(200) NULL COMMENT '目录图标修改名';
ALTER TABLE `fx_wechat_menus` ADD COLUMN `type` tinyint default 1 COMMENT '1:物业图标 2:工作站图标';
ALTER TABLE `fx_comp_manage` ADD COLUMN `associate` int(11) NUll COMMENT '邦定工作站';