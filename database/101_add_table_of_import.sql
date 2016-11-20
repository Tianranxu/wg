DROP TABLE `fx_import_log`;
DROP TABLE `fx_import_manage`;

CREATE TABLE fx_import_log (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  user_id int(11) DEFAULT NULL COMMENT '导入人员id',
  file_name varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '文件名',
  file_path varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '文件路径',
  status tinyint(1) DEFAULT '-1' COMMENT '状态(1:导入完成，-1：导入中，默认-1)',
  cm_id int(11) DEFAULT NULL COMMENT '所属企业id',
  cc_id int(11) DEFAULT NULL COMMENT '楼盘id',
  il_type tinyint(2) DEFAULT NULL COMMENT '导入类型（1:仪表 2.车辆 3.客户 4.房产）',
  success int(10) DEFAULT NULL COMMENT '导入成功数',
  failures int(10) DEFAULT NULL COMMENT '导入失败数',
  error_no varchar(4000) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '错误信息(多个id用逗号分隔)',
  import_time datetime DEFAULT NULL COMMENT '导入(成功)时间',
  create_time datetime DEFAULT NULL COMMENT '上传时间',
  remark text COLLATE utf8_unicode_ci COMMENT '错误信息备注',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(导入日志表)';