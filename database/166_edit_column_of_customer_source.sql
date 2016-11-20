ALTER TABLE `fx_customer_source`
ADD INDEX `type` (`type`) USING BTREE COMMENT '类型需求',
ADD INDEX `room_type` (`room_type`) USING BTREE COMMENT '户型需求',
ADD INDEX `furnish_type` (`furnish_type`) USING BTREE COMMENT '装修',
ADD INDEX `area` (`area`) USING BTREE COMMENT '面积需求',
ADD INDEX `status` (`status`) USING BTREE COMMENT '状态',
ADD INDEX `intention` (`intention`) USING BTREE COMMENT '性质';

