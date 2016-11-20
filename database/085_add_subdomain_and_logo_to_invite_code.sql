ALTER TABLE `fx_invite_code` ADD COLUMN `domain` varchar(50) NULL COMMENT '二级域名';
ALTER TABLE `fx_invite_code` ADD COLUMN `logo` varchar(255) NULL COMMENT 'LOGO';

update `fx_invite_code` set `domain`='www', `logo`='/Public/images/logo.jpg' where id = 1;

insert fx_invite_code values (2, 120192, '哈德莱', '2099-12-31 23:59:59', 'hdl', '/Public/images/logos/hdl.png');
insert fx_invite_code values (3, 680800, '中国电信', '2099-12-31 23:59:59', '10000', '/Public/images/logos/10000.png');
