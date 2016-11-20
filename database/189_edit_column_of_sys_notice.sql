ALTER TABLE `fx_sys_notice`
MODIFY COLUMN `other_id`  varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '其他ID  比如故障ID，表单ID，反馈ID等' AFTER `url`;

