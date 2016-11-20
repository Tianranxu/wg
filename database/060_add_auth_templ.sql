INSERT INTO `fx_sys_auth_rule` (id, module, type, name, title, status, rule_cond, module_name)
VALUES(150, 'Home/Template', 1, 'Home/Template/templ', '选择微信模板', 1, '', '选择微信模板(ajax)');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',150') WHERE id = 3;
DROP TABLE IF EXISTS `fx_sys_wechat_templ`;
CREATE TABLE `fx_sys_wechat_templ` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
    `name` varchar(100) NOT NULL COMMENT '模板名字',
    `style` varchar(100) NOT NULL COMMENT '模板样式文件名',
    `icon1` varchar(100) NOT NULL COMMENT '样板图',
    `icon2` varchar(100) NOT NULL COMMENT '样板图',
	`icon3` varchar(100) NOT NULL COMMENT '样板图',
	`status` tinyint default 1 COMMENT '状态',
	`reject` int(11) NULL COMMENT '不为空时只能用于此企业ID',
    PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(微信模板表)';
INSERT INTO `fx_sys_wechat_templ` (id, name, style, icon1, icon2, icon3, status, reject)
VALUES(1, '通用模板', 'modal.css', 'm1.png', 'm6.png', 'm7.png', 1, null),
	  (2, '甲岸模板', 'jiaan.css', 'jiaan1.png', 'jiaan2.png', 'jiaan3.png', 1, null);
ALTER TABLE `fx_comp_manage` ADD COLUMN `templet` int(11) default 1 COMMENT '微信端模板';	  