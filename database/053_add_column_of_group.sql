#add group type and initialize it 
ALTER TABLE fx_sys_group ADD COLUMN group_type tinyint(2) NOT NULL COMMENT '分组类型：1.企业分组 2.表单分组';

UPDATE fx_sys_group SET group_type = 1 ;