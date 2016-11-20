ALTER TABLE `fx_invite_code` ADD COLUMN `expire_time` datetime NULL COMMENT '过期时间';
update `fx_invite_code` set `expire_time`='2099-12-31 23:59:59' where id = 1;