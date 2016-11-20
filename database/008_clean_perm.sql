TRUNCATE TABLE `fx_sys_auth_rule`;

INSERT INTO `fx_sys_auth_rule` VALUES ('1', 'Home/Company', '1', 'Home/Company/index', '企业管理首页', '1', 'test');
INSERT INTO `fx_sys_auth_rule` VALUES ('2', 'Home/Company', '2', 'Home/Company/addcompany', '新增企业页面', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('3', 'Home/Company', '2', 'Home/Company/addgroup', '新建群组', '1', null);
INSERT INTO `fx_sys_auth_rule` VALUES ('4', 'Home/Company', '2', 'Home/Company/delgroup', '删除分组', '1', null);
INSERT INTO `fx_sys_auth_rule` VALUES ('5', 'Home/Company', '2', 'Home/Company/editpowe', '权限编辑', '1', null);
INSERT INTO `fx_sys_auth_rule` VALUES ('6', 'Home/Company', '2', 'Home/Company/editcompany', '编辑企业', '1', null);
INSERT INTO `fx_sys_auth_rule` VALUES ('7', 'Home/Company', '2', 'Home/Company/invitepeople', '邀请人', '1', null);
INSERT INTO `fx_sys_auth_rule` VALUES ('8', 'Home/Company', '2', 'Home/Company/invitelist', '邀请人列表', '1', null);
INSERT INTO `fx_sys_auth_rule` VALUES ('9', 'Home/Company', '2', 'Home/Company/getpeople', '获取企业人员', '1', null);
INSERT INTO `fx_sys_auth_rule` VALUES ('10', 'Home/Company', '2', 'Home/Company/renewcomp', '恢复服务', '1', null);
INSERT INTO `fx_sys_auth_rule` VALUES ('11', 'Home/Company', '2', 'Home/Company/delcomp', '删除企业', '1', null);
INSERT INTO `fx_sys_auth_rule` VALUES ('12', 'Home/Company', '2', 'Home/Company/stopcomp', '停止企业', '1', null);
INSERT INTO `fx_sys_auth_rule` VALUES ('13', 'Home/Company', '2', 'Home/Company/addmember', '添加成员', '1', null);
INSERT INTO `fx_sys_auth_rule` VALUES ('14', 'Home/Company', '2', 'Home/Company/selectphone', '查询手机用户', '1', null);
INSERT INTO `fx_sys_auth_rule` VALUES ('15', 'Home/Company', '2', 'Home/Company/do_add', '添加成员', '1', null);
INSERT INTO `fx_sys_auth_rule` VALUES ('16', 'Home/Company', '2', 'Home/Company/delmember', '删除成员', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('17', 'Home/Company', '2', 'Home/Company/movetocomp', '移动成员', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('18', 'Home/Company', '2', 'Home/Company/do_editpowe', '保存权限编辑', '1', '');

INSERT INTO `fx_sys_auth_rule` VALUES ('19', 'Home/Property', '1', 'Home/Property/index', '楼盘首页', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('20', 'Home/Property', '1', 'Home/Property/property', '楼盘管理首页', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('21', 'Home/Property', '1', 'Home/Property/add_property', '新增楼盘', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('22', 'Home/Property', '1', 'Home/Property/edit_property', '编辑楼盘', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('23', 'Home/Property', '1', 'Home/Property/find_city_list', '楼盘-查找城市', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('24', 'Home/Property', '1', 'Home/Property/find_area_list', '楼盘-查找区', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('25', 'Home/Property', '1', 'Home/Property/do_add_property', '楼盘-添加楼盘', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('26', 'Home/Customer', '2', 'Home/Customer/importerror', '客户导入失败', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('27', 'Home/Property', '1', 'Home/Property/propertyLoadMore', '楼盘-加载更多', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('28', 'Home/Property', '1', 'Home/Property/do_edit_property', '楼盘-编辑楼盘', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('29', 'Home/Property', '1', 'Home/Property/house', '房产首页', '1', null);
INSERT INTO `fx_sys_auth_rule` VALUES ('30', 'Home/Property', '1', 'Home/Property/add_house', '房产-新增房产', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('31', 'Home/Property', '1', 'Home/Property/edit_house', '房产-编辑房产', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('32', 'Home/Property', '1', 'Home/Property/houseLoadMore', '房产-加载更多', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('33', 'Home/Property', '1', 'Home/Property/do_add_house', '房产-添加房产', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('34', 'Home/Property', '1', 'Home/Property/do_edit_house', '房产-编辑房产', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('35', 'Home/Property', '1', 'Home/Property/check_house_number', '房产-查看房号是否重复', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('36', 'Home/Property', '1', 'Home/Property/import_house', '楼盘-房产数据导入', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('37', 'Home/Property', '1', 'Home/Property/upload', '房产导入-上传', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('38', 'Home/Property', '1', 'Home/Property/import_wrong', '房产导入-导入错误报告', '1', '');

INSERT INTO `fx_sys_auth_rule` VALUES ('39', 'Home/Customer', '1', 'Home/Customer/index', '客户管理首页', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('40', 'Home/Customer', '2', 'Home/Customer/addcustomer', '新建客户', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('41', 'Home/Customer', '2', 'Home/Customer/importcustomer', '导入客户', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('42', 'Home/Customer', '2', 'Home/Customer/edit', '编辑客户', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('43', 'Home/Customer', '2', 'Home/Customer/do_import', '完成导入客户', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('44', 'Home/Customer', '2', 'Home/Customer/search_c', '搜索客户', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('45', 'Home/Customer', '2', 'Home/Customer/flow', '客户加载更多', '1', '');

INSERT INTO `fx_sys_auth_rule` VALUES ('46', 'Home/Building', '1', 'Home/Building/index', '楼宇首页', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('47', 'Home/Building', '2', 'Home/Building/addbuild', '新建楼宇', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('48', 'Home/Building', '2', 'Home/Building/editbuild', '编辑楼宇', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('49', 'Home/Building', '2', 'Home/Building/search_b', '搜索楼宇', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('50', 'Home/Building', '2', 'Home/Building/flow', '楼宇加载更多', '1', '');

INSERT INTO `fx_sys_auth_rule` VALUES ('51', 'Home/Car', '1', 'Home/Car/index', '车辆管理首页', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('52', 'Home/Car', '1', 'Home/Car/add', '添加车辆页面', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('53', 'Home/Car', '1', 'Home/Car/modify', '编辑车辆页面', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('54', 'Home/Car', '1', 'Home/Car/doAdd', '添加车辆', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('55', 'Home/Car', '1', 'Home/Car/doModify', '编辑车辆', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('56', 'Home/Car', '1', 'Home/Car/import', '车辆导入页面', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('57', 'Home/Car', '1', 'Home/Car/upload', '导入车辆', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('58', 'Home/Car', '1', 'Home/Car/importWrong', '车辆导入错误信息界面', '1', '');

INSERT INTO `fx_sys_auth_rule` VALUES ('59', 'Home/Authrole', '1', 'Home/Authrole/index', '角色权限', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('60', 'Home/Authrole', '1', 'Home/Authrole/add', '添加角色', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('61', 'Home/Authrole', '1', 'Home/Authrole/delete', '删除角色', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('62', 'Home/Authrole', '1', 'Home/Authrole/modify', '修改角色界面', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('63', 'Home/Authrole', '1', 'Home/Authrole/doModify', '修改角色', '1', '');

INSERT INTO `fx_sys_auth_rule` VALUES ('64', 'Home/Charge', '1', 'Home/Charge/index', '收费项目管理首页', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('65', 'Home/Charge', '1', 'Home/Charge/add_charge', '新增收费项目页面', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('66', 'Home/Charge', '1', 'Home/Charge/edit_charge', '编辑收费项目页面', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('67', 'Home/Charge', '1', 'Home/Charge/loadMore', '收费项目-加载更多', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('68', 'Home/Charge', '1', 'Home/Charge/do_add_charge', '新增收费项目', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('69', 'Home/Charge', '1', 'Home/Charge/do_edit_charge', '编辑收费项目', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('70', 'Home/Charge', '1', 'Home/Charge/get_meter_list', '新增收费项目-读取仪表管理数据', '1', '');


INSERT INTO `fx_sys_auth_rule` VALUES ('71', 'Home/Propertycharges', '1', 'Home/Propertycharges/index', '房产收费管理', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('72', 'Home/Propertycharges', '2', 'Home/Propertycharges/set', '收费设置', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('73', 'Home/Propertycharges', '2', 'Home/Propertycharges/edit', '编辑收费', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('74', 'Home/Propertycharges', '2', 'Home/Propertycharges/get_build_house', '获取楼宇和房间数据', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('75', 'Home/Propertycharges', '2', 'Home/Propertycharges/do_edit', '收费编辑', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('76', 'Home/Propertycharges', '1', 'Home/Propertycharges/review', '房产收费管理-收费预览', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('77', 'Home/Propertycharges', '1', 'Home/Propertycharges/get_all_building', '收费预览-查询楼宇', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('78', 'Home/Propertycharges', '1', 'Home/Propertycharges/get_charges_setting', '收费预览-查询收费', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('79', 'Home/Propertycharges', '1', 'Home/Propertycharges/unbill', '未出账单', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('80', 'Home/Propertycharges', '1', 'Home/Propertycharges/loadMore', '未出账单-加载更多', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('81', 'Home/Propertycharges', '1', 'Home/Propertycharges/do_discount', '未出账单-录入优惠', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('82', 'Home/Propertycharges', '1', 'Home/Propertycharges/del_discount', '未出账单-删除优惠', '1', null);
INSERT INTO `fx_sys_auth_rule` VALUES ('83', 'Home/Propertycharges', '1', 'Home/Propertycharges/generate_bills', '未出账单-生成账单', '1', null);

INSERT INTO `fx_sys_auth_rule` VALUES ('84', 'Home/Metermanage', '1', 'Home/Metermanage/index', '仪表读数管理界面', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('85', 'Home/Metermanage', '1', 'Home/Metermanage/addMeter', '添加仪表读数', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('86', 'Home/Metermanage', '1', 'Home/Metermanage/editMeter', '编辑仪表读数', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('87', 'Home/Metermanage', '1', 'Home/Metermanage/importMeter', '仪表读数导入界面', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('88', 'Home/Metermanage', '1', 'Home/Metermanage/importWrong', '仪表读数导入错误界面', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('89', 'Home/Metermanage', '1', 'Home/Metermanage/upload', '仪表数据上传界面', '1', '');

INSERT INTO `fx_sys_auth_rule` VALUES ('90', 'Home/Meterset', '1', 'Home/Meterset/index', '仪表设置界面', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('91', 'Home/Meterset', '1', 'Home/Meterset/add', '仪表添加界面', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('92', 'Home/Meterset', '1', 'Home/Meterset/edit', '仪表编辑界面', '1', '');

INSERT INTO `fx_sys_auth_rule` VALUES ('93', 'Home/Cilpay', '1', 'Home/Cilpay/index', '客户缴费', '1', null);
INSERT INTO `fx_sys_auth_rule` VALUES ('94', 'Home/Cilpay', '2', 'Home/Cilpay/get_build_house', 'ajax查出楼宇和房间', '1', null);

INSERT INTO `fx_sys_auth_rule` VALUES ('95', 'Home/Material', '1', 'Home/Material/image_text', '图文信息首页', '1', null);
INSERT INTO `fx_sys_auth_rule` VALUES ('96', 'Home/Material', '1', 'Home/Material/add_image_text', '新增图文信息', '1', null);
INSERT INTO `fx_sys_auth_rule` VALUES ('97', 'Home/Material', '1', 'Home/Material/edit_image_text', '编辑图文信息', '1', null);
INSERT INTO `fx_sys_auth_rule` VALUES ('98', 'Home/Material', '1', 'Home/Material/del_image_text', '删除图文信息', '1', null);
INSERT INTO `fx_sys_auth_rule` VALUES ('99', 'Home/Material', '1', 'Home/Material/picture_library', '图片库', '1', null);

INSERT INTO `fx_sys_auth_rule` VALUES ('100', 'Home/Template', '1', 'Home/Template/index', '模板管理首页', '1', null);

INSERT INTO `fx_sys_auth_rule` VALUES ('101', 'Home/Micserve', '1', 'Home/Micserve/index', '微服务首页', '1', null);
INSERT INTO `fx_sys_auth_rule` VALUES ('102', 'Home/Micserve', '1', 'Home/Micserve/confirm_do', '微服务-确认排序及显示功能', '1', null);
INSERT INTO `fx_sys_auth_rule` VALUES ('103', 'Home/Publicno', '1', 'Home/Publicno/index', '管理主界面', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('104', 'Home/Publicno', '1', 'Home/Publicno/access', '公众号接入', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('105', 'Home/Publicno', '1', 'Home/Publicno/unlock', '公众号解绑', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('106', 'Home/Publicno', '1', 'Home/Publicno/customMenu', '自定义菜单', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('107', 'Home/Slide', '1', 'Home/Slide/index', '幻灯片管理', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('108', 'Home/Slide', '1', 'Home/Slide/addSlide', '添加幻灯片', '1', '');
INSERT INTO `fx_sys_auth_rule` VALUES ('109', 'Home/Slide', '1', 'Home/Slide/editSlide', '编辑幻灯片', '1', '');









