ALTER TABLE `fx_comp_menus` ADD COLUMN `icon_id` int(11)  null COMMENT '用户修改后的图标';
ALTER TABLE `fx_comp_serve` ADD COLUMN `icon_id` int(11)  null COMMENT '用户修改后的图标';
update `fx_comp_menus`, `fx_wechat_menus` set `fx_comp_menus`.`icon_id`=  `fx_wechat_menus`.`icon_id` where fx_wechat_menus.id=fx_comp_menus.menu_id;
update `fx_comp_serve`, `fx_sys_micserve` set `fx_comp_serve`.`icon_id`=  `fx_sys_micserve`.`icon_id` where fx_sys_micserve.id=fx_comp_serve.serve_id;