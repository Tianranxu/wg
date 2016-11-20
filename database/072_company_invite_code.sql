INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(132,'Home/Company',1,'Home/Company/checkcode','检查验证码','企业管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',132') WHERE id = 3;

ALTER TABLE `fx_comp_manage` ADD COLUMN `code` varchar(50) DEFAULT NULL COMMENT '邀请码';

update `fx_comp_manage` set code = '668668';

CREATE TABLE `fx_invite_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `code` varchar(50) DEFAULT NULL COMMENT '邀请码',
  `distributor` varchar(100) DEFAULT NULL COMMENT '代理商名字',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3326 DEFAULT CHARSET=utf8 COMMENT='代理商和邀请码';

INSERT INTO `fx_invite_code` VALUES (1,'668668','风馨科技');