TRUNCATE `fx_wechat_menus`;

INSERT INTO `fx_wechat_menus` (`id`, `title`, `icon_id`, `link_url`)
VALUES
    (1,'社区资讯',33,'/WXClient/infos'),
    (2,'账单缴费',34,'#'),
    (3,'公共报修',35,'#'),
    (4,'生活商圈',36,'#'),
    (5,'投诉建议',37,'#'),
    (6,'联系我们',38,'#'),
    (7,'通知公告',39,'/WXClient/notice'),
    (8,'房屋服务',40,'#'),
    (9,'微服务',41,'#');

