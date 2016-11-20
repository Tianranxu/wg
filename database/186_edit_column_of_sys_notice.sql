ALTER TABLE `fx_sys_notice`
MODIFY COLUMN `type`  tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '类型  1-超时未接故障，2-意见反馈，3-待审核表单，4-微信用户物业缴费，5-微信用户车辆缴费，6-转单故障' AFTER `content`;

