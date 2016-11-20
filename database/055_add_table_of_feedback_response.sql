CREATE TABLE `fx_feedback_response` (
      `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
      `user_id` int(11) NOT NULL COMMENT '管理员id',
      `fid` int(11) NOT NULL COMMENT '反馈信息id',
      `content` text DEFAULT NULL COMMENT '回复内容',
      `create_time` datetime NOT NULL COMMENT '创建时间',
      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(反馈回复表)';

ALTER TABLE `fx_feedback` ADD COLUMN `status` tinyint(1) NOT NULL DEFAULT -1 COMMENT '状态 未回复：-1，已回复：1';
