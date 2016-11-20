ALTER TABLE `fx_sys_micserve` MODIFY COLUMN `link_url`  varchar(1000);


INSERT INTO `fx_sys_micserve` (`id`, `name`, `icon_id`, `link_url`)
VALUES
(10, '甲岸社区地图', 23, 'http://map.baidu.com/mobile/webapp/search/search/qt=s&da_src=pcmappg.searchBox.sugg&wd=甲岸社区&c=340&src=0&wd2=深圳市宝安区&sug=1&l=12&from=webmap&force=newsample&sug_forward=3f15ed671bb85bf297ec8475/newmap=1&force=newsample&tn=B_NORMAL_MAP&hb=B_SATELLITE_STREET&t=1445847229&da_from=weixin&openna=1&vt=map&ecom=0');
