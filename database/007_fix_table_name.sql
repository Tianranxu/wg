UPDATE `fx_sys_auth_rule` SET name='Home/Company' WHERE id=37;
UPDATE `fx_sys_role` SET rule_id= concat(rule_id,',106,107,108') WHERE id=1;
