##把维修和工作站，用户和角色中间表的管理员ID更换成各自的管理员ID
update fx_user_role_temp rt,(select id from fx_comp_manage where cm_type=2)cm set role_id=6 where rt.cm_id=cm.id and rt.role_id=3;
update fx_user_role_temp rt,(select id from fx_comp_manage where cm_type=3)cm set role_id=9 where rt.cm_id=cm.id and rt.role_id=3;