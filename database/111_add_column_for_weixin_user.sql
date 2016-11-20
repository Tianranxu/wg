ALTER TABLE `fx_weixin_user` 
ADD COLUMN `session_id` varchar(100) NOT NULL COMMENT '用户sessionID';

ALTER TABLE `fx_publicno` 
ADD COLUMN `um_id` varchar(16) NOT NULL COMMENT 'uuid';