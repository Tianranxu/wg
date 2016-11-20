ALTER TABLE `fx_sys_notice`
ADD COLUMN `other_id`  int(11) NULL DEFAULT NULL COMMENT '其他ID  比如故障ID，表单ID，反馈ID等' AFTER `url`;

