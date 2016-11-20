ALTER TABLE `fx_wechat_menus` MODIFY COLUMN `link_url`  varchar(1000);

update `fx_wechat_menus` set link_url = 'http://map.baidu.com/mobile/webapp/search/search/qt=s&da_src=pcmappg.searchBox.sugg&from=webmap&force=newsample&tn=B_NORMAL_MAP&hb=B_SATELLITE_STREET&openna=1&vt=map&ecom=0&wd=' where id = 22;
