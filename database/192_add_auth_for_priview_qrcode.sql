INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(294,'Home/Imgtxt',1,'Home/Imgtxt/preview','预览二维码','图文消息管理');
INSERT INTO `fx_sys_auth_rule` (id,module,type,name,title,module_name) VALUES(295,'Home/Imgtxt',1,'Home/Imgtxt/publish','图文发布','图文消息管理');
UPDATE `fx_sys_role` SET rule_id = concat(rule_id, ',294,295') WHERE id = 3;