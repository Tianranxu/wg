ALTER TABLE `fx_weixin_user`
MODIFY COLUMN `user_type`  tinyint(2) NULL DEFAULT 1 COMMENT '用户类型：1：普通用户 2：反馈管理员 3：表单管理员 4：维修管理员 5：收费管理员' AFTER `session_id`;

ALTER TABLE `fx_sys_repairer`
ADD COLUMN `user_type`  tinyint(2) NULL DEFAULT 1 COMMENT '用户类型：1.普通用户 4.客服（维修管理员）' AFTER `expires`;

ALTER TABLE `fx_weixin_msg_template`
ADD COLUMN `type`  tinyint(2) NULL DEFAULT 1 COMMENT '1.物业号 2.维修号' AFTER `long_id`;