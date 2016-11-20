ALTER TABLE `fx_comp_manage`
ADD COLUMN `notice_remark`  text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL COMMENT '通知单说明' AFTER `repair_time_limit`;

