SET NAMES 'utf8';
ALTER TABLE fx_charges_setting MODIFY price double(50,2) not null default '0';
update fx_sys_auth_rule  set title = '新建和编辑群组' where id = 3;