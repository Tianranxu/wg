alter table fx_lease_bill alter column status set default 1;
ALTER TABLE `fx_lease_bill`
MODIFY COLUMN `status`  tinyint(1) NULL DEFAULT 1 COMMENT '1生成未未缴费,-1即将到期,3过期未缴费,2已缴费,-2删除' AFTER `money`;