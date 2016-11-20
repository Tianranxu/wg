ALTER TABLE `fx_completed_work`
MODIFY COLUMN `form_id`  varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '填写的表单ID（mongodb）' AFTER `openid`;
UPDATE `fx_completed_work` SET `form_id` = '20_yunqijianchahezhuyuanfenmianjihuashengyuzhengmingbiaoge' WHERE `form_id` = '20_yunqijianchahezhuyuanfenmianjihuashengyuzhengmi';