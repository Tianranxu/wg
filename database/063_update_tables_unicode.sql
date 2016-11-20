ALTER TABLE `fx_accounts_charges`
MODIFY COLUMN `remark`  varchar(280) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '��ע' AFTER `month`;

ALTER TABLE `fx_bind_temp`
MODIFY COLUMN `openid`  varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '�û��ı�ʶ���Ե�ǰ���ں�Ψһ ' AFTER `id`,
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `fx_city`
MODIFY COLUMN `name`  varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `id`,
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `fx_imgtxt_manage`
MODIFY COLUMN `media_id`  varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'ͼ����ϢmediaID' AFTER `id`,
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `fx_order_manage`
MODIFY COLUMN `openid`  varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '�û��ı�ʶ���Ե�ǰ���ں�Ψһ ' AFTER `type`,
MODIFY COLUMN `ac_ids`  varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '���ɶ���ID�ַ������ö��Ÿ���' AFTER `openid`,
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `fx_sys_category`
MODIFY COLUMN `name`  varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '��������' AFTER `id`,
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `fx_weixin_user`
MODIFY COLUMN `mobile`  varchar(11) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '΢���û��ֻ�����' AFTER `id`,
MODIFY COLUMN `nickname`  varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '�û����ǳ�' AFTER `openid`,
MODIFY COLUMN `language`  varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '�û������ԣ���������Ϊzh_CN' AFTER `sex`,
MODIFY COLUMN `city`  varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '�û����ڳ���' AFTER `language`,
MODIFY COLUMN `province`  varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '�û�����ʡ��' AFTER `city`,
MODIFY COLUMN `country`  varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '�û����ڹ���' AFTER `province`,
MODIFY COLUMN `headimgurl`  varchar(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '�û�ͷ�����һ����ֵ����������ͷ���С����0��46��64��96��132��ֵ��ѡ��0����640*640������ͷ�񣩣��û�û��ͷ��ʱ����Ϊ�ա����û�����ͷ��ԭ��ͷ��URL��ʧЧ' AFTER `country`,
MODIFY COLUMN `unionid`  varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'ֻ�����û������ںŰ󶨵�΢�ſ���ƽ̨�ʺź󣬲Ż���ָ��ֶ�' AFTER `subscribe_time`,
MODIFY COLUMN `remark`  varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '���ں���Ӫ�߶Է�˿�ı�ע�����ں���Ӫ�߿���΢�Ź���ƽ̨�û��������Է�˿��ӱ�ע' AFTER `unionid`;

