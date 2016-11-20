ALTER TABLE `fx_sys_repairer`
ADD COLUMN `session_id`  varchar(100) NOT NULL COMMENT '维修员sessionID';

ALTER TABLE `fx_sys_repairer`
ADD COLUMN `access_token`  varchar(200) DEFAULT NULL COMMENT '维修员access_token';

ALTER TABLE `fx_sys_repairer`
ADD COLUMN `refresh_token`  varchar(200) DEFAULT NULL COMMENT '维修员refresh_token';

ALTER TABLE `fx_sys_repairer`
ADD COLUMN `expires`  int(11) DEFAULT NULL  COMMENT 'access_token有效期限';