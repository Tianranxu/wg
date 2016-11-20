ALTER TABLE `fx_sys_category`
MODIFY COLUMN `type`  int(11) UNSIGNED NOT NULL COMMENT '分类类型 1-图文信息 2-资讯 3-公告 4-群发消息 5-联系我们 6-帮助 7-办事 100-图片库 101-图片库分组' AFTER `name`;
