TRUNCATE `fx_wechat_menus`;

INSERT INTO `fx_wechat_menus` (`id`, `title`, `icon_id`, `link_url`)
VALUES
    (1, '社区资讯', 33, '/WXClient/infos'),
    (2, '账单缴费', 34, '#'),
    (3, '公共报修', 35, '#'),
    (4, '生活商圈', 36, '#'),
    (5, '投诉建议', 37, '#'),
    (6, '联系我们', 38, '#'),
    (7, '通知公告', 39, '/WXClient/notice'),
    (8, '房屋服务', 40, '#'),
    (9, '微服务', 41, '/WXClient/micserve');

TRUNCATE `fx_sys_micserve`;
INSERT INTO `fx_sys_micserve` (`id`, `name`, `icon_id`, `link_url`)
VALUES
    (1, '天气', 18, 'http://www.weather.com.cn/weather/101280601.shtml'),
    (2, '火车', 19, 'http://train.qunar.com/'),
    (3, '新闻', 20, 'http://news.baidu.com/'),
    (4, '快递', 21, 'http://www.kuaidi100.com/'),
    (5, '彩票', 22, 'http://baidu.lecai.com/lottery/draw/'),
    (6, '黄历', 23, 'http://www.365djs.com/calendarfullyear.html'),
    (7, '百度', 24, 'http://www.baidu.com/'),
    (8, '音乐', 25, 'http://music.baidu.com/'),
    (9, '翻译', 26, 'http://fanyi.baidu.com/');
