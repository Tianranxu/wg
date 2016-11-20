ALTER TABLE `fx_contract_manage`  ADD COLUMN `expire` tinyint(1) default 1 COMMENT '-1,已到期 1,还没期 2,即将到期';
ALTER TABLE `fx_room_source`  ADD COLUMN `expire` tinyint(1) default 1 COMMENT '-1,已到期 1,还没期 2,即将到期';
