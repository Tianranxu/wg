#Dump of table fx_publicno
#--------------------------------------------------------------

CREATE TABLE `fx_publicno` (
`id`  int(11) NOT NULL AUTO_INCREMENT COMMENT'主键',
`appid`  varchar(100) NULL COMMENT'公众号的appid',
`access_token`  varchar(100) NULL COMMENT'公众号的acce_token',
`expires_in` varchar(4) NULL COMMENT'access_token的有效期',
`isCancel` tinyint(1) NULL COMMENT'是否已经取消授权',
`authorizer_info` varchar(50) NULL COMMENT'公众号的昵称',
`head_img`  varchar(200) NULL COMMENT'公众号头像',
`service_type_info`  tinyint(1) NULL COMMENT'授权方公众号类型，0代表订阅号，1代表由历史老帐号升级后的订阅号，2代表服务号',
`user_name`  varchar(50) NULL COMMENT'公众号原始id',
`alias`  varchar(100) NULL COMMENT'公众号所设置的微信号',
`qrcode_url`  varchar(200) NULL COMMENT'公众号二维码图片的url',
`refresh_token`  varchar(100) NULL COMMENT'公众号的refresh_token',
`cm_id`  int(11) NULL COMMENT'公众号所属公司id',
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(公众号管理)';


#add a auth of company manager
UPDATE `fx_sys_role` SET rule_id= concat(rule_id,',134') WHERE id=3;
#add a record of auth rule
INSERT INTO `fx_sys_auth_rule` VALUES ('134', 'Home/Publicno', '1', 'Home/Publicno/authrized', '授权成功返回页面', '1', '');