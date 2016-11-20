ALTER TABLE `fx_contract_manage`
MODIFY COLUMN `status`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态  -2-已终止，-1已到期，1-已生效' AFTER `cycle`;

