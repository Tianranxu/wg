ALTER TABLE `fx_sys_auth_rule` ADD COLUMN `module_name` VARCHAR(30) NOT NULL COMMENT '模块名称';
UPDATE `fx_sys_auth_rule` SET `module_name`='角色管理' WHERE `module`='Home/Authrole';
UPDATE `fx_sys_auth_rule` SET `module_name`='楼宇管理' WHERE `module`='Home/Building'; 
UPDATE `fx_sys_auth_rule` SET `module_name`='车辆管理' WHERE `module`='Home/Car'; 
UPDATE `fx_sys_auth_rule` SET `module_name`='收费管理' WHERE `module`='Home/Charge'; 
UPDATE `fx_sys_auth_rule` SET `module_name`='缴费管理' WHERE `module`='Home/Cilpay'; 
UPDATE `fx_sys_auth_rule` SET `module_name`='企业管理' WHERE `module`='Home/Company'; 
UPDATE `fx_sys_auth_rule` SET `module_name`='客户管理' WHERE `module`='Home/Customer'; 
UPDATE `fx_sys_auth_rule` SET `module_name`='微信排版' WHERE `module`='Home/Homecompose'; 
UPDATE `fx_sys_auth_rule` SET `module_name`='图标管理' WHERE `module`='Home/Icon'; 
UPDATE `fx_sys_auth_rule` SET `module_name`='资讯管理' WHERE `module`='Home/Information'; 
UPDATE `fx_sys_auth_rule` SET `module_name`='素材管理' WHERE `module`='Home/Material'; 
UPDATE `fx_sys_auth_rule` SET `module_name`='仪表读数' WHERE `module`='Home/Metermanage'; 
UPDATE `fx_sys_auth_rule` SET `module_name`='仪表设置' WHERE `module`='Home/Meterset'; 
UPDATE `fx_sys_auth_rule` SET `module_name`='微服务' WHERE `module`='Home/Micserve'; 
UPDATE `fx_sys_auth_rule` SET `module_name`='房产管理' WHERE `module`='Home/Property'; 
UPDATE `fx_sys_auth_rule` SET `module_name`='房产收费管理' WHERE `module`='Home/Propertycharges'; 
UPDATE `fx_sys_auth_rule` SET `module_name`='公众号管理' WHERE `module`='Home/Publicno'; 
UPDATE `fx_sys_auth_rule` SET `module_name`='幻灯片管理' WHERE `module`='Home/Slide'; 
UPDATE `fx_sys_auth_rule` SET `module_name`='模板管理' WHERE `module`='Home/Template'; 
UPDATE `fx_sys_auth_rule` SET `module_name`='微信用户' WHERE `module`='Home/Wechatuser'; 
