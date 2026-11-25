-- ============================================================================
-- DudaCMS 数据库升级脚本
-- @version   1.0.9
-- @requires  1.0.8
-- @title     布尔与状态字段类型统一
-- @summary   布尔统一 tinyint(1) unsigned NOT NULL；多状态统一 tinyint unsigned；排序统一 smallint unsigned
-- @summary   MODIFY COLUMN 幂等，UPDATE 仅回填 NULL，可安全重复执行
-- @verify    column:action_log.anonymous, column:users.status, column:article.sort
-- ============================================================================

-- 1. 空值先按列默认值回填，保证后续统一 NOT NULL 不会丢失记录。
UPDATE `dask_action` SET `status`=0 WHERE `status` IS NULL;
UPDATE `dask_action_log` SET `anonymous`=0 WHERE `anonymous` IS NULL;
UPDATE `dask_action_log` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_action_log_all` SET `anonymous`=0 WHERE `anonymous` IS NULL;
UPDATE `dask_action_log_all` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_action_log_data` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_admin_auth` SET `auth_open`=1 WHERE `auth_open` IS NULL;
UPDATE `dask_admin_auth` SET `status`=0 WHERE `status` IS NULL;
UPDATE `dask_admin_group` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_admin_group` SET `system`=0 WHERE `system` IS NULL;
UPDATE `dask_announce` SET `status`=0 WHERE `status` IS NULL;
UPDATE `dask_answer` SET `is_anonymous`=0 WHERE `is_anonymous` IS NULL;
UPDATE `dask_answer` SET `is_best`=0 WHERE `is_best` IS NULL;
UPDATE `dask_answer_comment` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_article` SET `is_recommend`=0 WHERE `is_recommend` IS NULL;
UPDATE `dask_attach` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_browse_records` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_category` SET `status`=0 WHERE `status` IS NULL;
UPDATE `dask_column` SET `recommend`=0 WHERE `recommend` IS NULL;
UPDATE `dask_config` SET `source`=0 WHERE `source` IS NULL;
UPDATE `dask_config` SET `system`=0 WHERE `system` IS NULL;
UPDATE `dask_credit_group` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_credit_group` SET `system`=0 WHERE `system` IS NULL;
UPDATE `dask_credit_rule` SET `status`=0 WHERE `status` IS NULL;
UPDATE `dask_email_log` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_feature` SET `status`=0 WHERE `status` IS NULL;
UPDATE `dask_help_chapter` SET `status`=0 WHERE `status` IS NULL;
UPDATE `dask_help_chapter_relation` SET `status`=0 WHERE `status` IS NULL;
UPDATE `dask_links` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_menu_rule` SET `auth_open`=1 WHERE `auth_open` IS NULL;
UPDATE `dask_menu_rule` SET `is_home`=0 WHERE `is_home` IS NULL;
UPDATE `dask_menu_rule` SET `status`=0 WHERE `status` IS NULL;
UPDATE `dask_page` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_post_relation` SET `status`=0 WHERE `status` IS NULL;
UPDATE `dask_post_relation` SET `is_anonymous`=0 WHERE `is_anonymous` IS NULL;
UPDATE `dask_post_relation` SET `is_recommend`=0 WHERE `is_recommend` IS NULL;
UPDATE `dask_question` SET `is_anonymous`=0 WHERE `is_anonymous` IS NULL;
UPDATE `dask_question` SET `is_recommend`=0 WHERE `is_recommend` IS NULL;
UPDATE `dask_question_comment` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_question_focus` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_report` SET `status`=0 WHERE `status` IS NULL;
UPDATE `dask_search_engine` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_topic` SET `is_parent`=0 WHERE `is_parent` IS NULL;
UPDATE `dask_topic` SET `lock`=0 WHERE `lock` IS NULL;
UPDATE `dask_topic` SET `top`=0 WHERE `top` IS NULL;
UPDATE `dask_topic_relation` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_users` SET `forbidden_ip`=0 WHERE `forbidden_ip` IS NULL;
UPDATE `dask_users` SET `is_first_login`=0 WHERE `is_first_login` IS NULL;
UPDATE `dask_users` SET `is_valid_email`=0 WHERE `is_valid_email` IS NULL;
UPDATE `dask_users` SET `is_valid_mobile`=0 WHERE `is_valid_mobile` IS NULL;
UPDATE `dask_users_favorite_tag` SET `is_public`=0 WHERE `is_public` IS NULL;
UPDATE `dask_users_follow` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_users_forbidden` SET `status`=0 WHERE `status` IS NULL;
UPDATE `dask_users_inbox` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_users_inbox_dialog` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_users_notify` SET `anonymous`=0 WHERE `anonymous` IS NULL;
UPDATE `dask_users_notify` SET `read_flag`=0 WHERE `read_flag` IS NULL;
UPDATE `dask_users_notify` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_users_notify_setting` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_users_notify_setting` SET `system`=0 WHERE `system` IS NULL;
UPDATE `dask_users_verify_type` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_approval` SET `status`=0 WHERE `status` IS NULL;
UPDATE `dask_article` SET `status`=0 WHERE `status` IS NULL;
UPDATE `dask_column` SET `verify`=0 WHERE `verify` IS NULL;
UPDATE `dask_column_recommend_article` SET `status`=0 WHERE `status` IS NULL;
UPDATE `dask_invitation` SET `active_status`=0 WHERE `active_status` IS NULL;
UPDATE `dask_question` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_topic` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_upgrade_log` SET `status`=0 WHERE `status` IS NULL;
UPDATE `dask_users` SET `status`=1 WHERE `status` IS NULL;
UPDATE `dask_users_verify` SET `status`=0 WHERE `status` IS NULL;

-- 2. 布尔字段统一 tinyint(1) unsigned NOT NULL。
ALTER TABLE `dask_action` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态';
ALTER TABLE `dask_action_log` MODIFY COLUMN `anonymous` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否匿名';
ALTER TABLE `dask_action_log` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态';
ALTER TABLE `dask_action_log_all` MODIFY COLUMN `anonymous` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否匿名';
ALTER TABLE `dask_action_log_all` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态';
ALTER TABLE `dask_action_log_data` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态';
ALTER TABLE `dask_admin_auth` MODIFY COLUMN `auth_open` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放权限';
ALTER TABLE `dask_admin_auth` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '菜单状态';
ALTER TABLE `dask_admin_group` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态';
ALTER TABLE `dask_admin_group` MODIFY COLUMN `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否系统内置';
ALTER TABLE `dask_announce` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '公告状态：1正常，0删除';
ALTER TABLE `dask_answer` MODIFY COLUMN `is_anonymous` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否匿名回答';
ALTER TABLE `dask_answer` MODIFY COLUMN `is_best` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否最佳回答';
ALTER TABLE `dask_answer_comment` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态：1正常，0删除';
ALTER TABLE `dask_article` MODIFY COLUMN `is_recommend` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否推荐';
ALTER TABLE `dask_attach` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态';
ALTER TABLE `dask_browse_records` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态：1正常，0删除';
ALTER TABLE `dask_category` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '分类状态';
ALTER TABLE `dask_column` MODIFY COLUMN `recommend` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否推荐';
ALTER TABLE `dask_config` MODIFY COLUMN `source` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '配置来源';
ALTER TABLE `dask_config` MODIFY COLUMN `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否系统配置';
ALTER TABLE `dask_credit_group` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态';
ALTER TABLE `dask_credit_group` MODIFY COLUMN `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否系统内置';
ALTER TABLE `dask_credit_rule` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态';
ALTER TABLE `dask_email_log` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '发送状态：0失败，1成功';
ALTER TABLE `dask_feature` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态';
ALTER TABLE `dask_help_chapter` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态';
ALTER TABLE `dask_help_chapter_relation` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态';
ALTER TABLE `dask_links` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态：1正常，0禁用';
ALTER TABLE `dask_menu_rule` MODIFY COLUMN `auth_open` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放权限';
ALTER TABLE `dask_menu_rule` MODIFY COLUMN `is_home` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否默认首页';
ALTER TABLE `dask_menu_rule` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '菜单状态';
ALTER TABLE `dask_page` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态';
ALTER TABLE `dask_post_relation` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态：1正常，0删除';
ALTER TABLE `dask_post_relation` MODIFY COLUMN `is_anonymous` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否匿名';
ALTER TABLE `dask_post_relation` MODIFY COLUMN `is_recommend` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否推荐';
ALTER TABLE `dask_question` MODIFY COLUMN `is_anonymous` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否匿名提问';
ALTER TABLE `dask_question` MODIFY COLUMN `is_recommend` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否推荐';
ALTER TABLE `dask_question_comment` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态：1正常，0删除';
ALTER TABLE `dask_question_focus` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态：1正常，0删除';
ALTER TABLE `dask_report` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否处理';
ALTER TABLE `dask_search_engine` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态';
ALTER TABLE `dask_topic` MODIFY COLUMN `is_parent` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否根话题';
ALTER TABLE `dask_topic` MODIFY COLUMN `lock` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否锁定';
ALTER TABLE `dask_topic` MODIFY COLUMN `top` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否推荐';
ALTER TABLE `dask_topic_relation` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态：1正常，0删除';
ALTER TABLE `dask_users` MODIFY COLUMN `forbidden_ip` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否封禁IP';
ALTER TABLE `dask_users` MODIFY COLUMN `is_first_login` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否首次登录';
ALTER TABLE `dask_users` MODIFY COLUMN `is_valid_email` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '邮箱是否已验证';
ALTER TABLE `dask_users` MODIFY COLUMN `is_valid_mobile` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '手机是否已验证';
ALTER TABLE `dask_users_favorite_tag` MODIFY COLUMN `is_public` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否公开';
ALTER TABLE `dask_users_follow` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态：1正常，0删除';
ALTER TABLE `dask_users_forbidden` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态：1有效，0删除';
ALTER TABLE `dask_users_inbox` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态';
ALTER TABLE `dask_users_inbox_dialog` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态';
ALTER TABLE `dask_users_notify` MODIFY COLUMN `anonymous` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否匿名';
ALTER TABLE `dask_users_notify` MODIFY COLUMN `read_flag` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已读';
ALTER TABLE `dask_users_notify` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态：1正常，0删除';
ALTER TABLE `dask_users_notify_setting` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否启用';
ALTER TABLE `dask_users_notify_setting` MODIFY COLUMN `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否系统内置';
ALTER TABLE `dask_users_verify_type` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态';

-- 3. 多状态字段统一 tinyint unsigned，明确其非布尔语义。
ALTER TABLE `dask_approval` MODIFY COLUMN `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '审核状态：0待审核，1已审核，2已拒绝';
ALTER TABLE `dask_article` MODIFY COLUMN `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '文章状态：0删除，1正常，2待审核，3待发布';
ALTER TABLE `dask_column` MODIFY COLUMN `verify` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '审核状态：0审核中，1已通过，2已拒绝';
ALTER TABLE `dask_column_recommend_article` MODIFY COLUMN `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '审核状态：0待审核，1已审核，2已拒绝';
ALTER TABLE `dask_invitation` MODIFY COLUMN `active_status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '邀请状态：0未使用，1已使用';
ALTER TABLE `dask_question` MODIFY COLUMN `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '问题状态：0删除，1正常，2待审核';
ALTER TABLE `dask_topic` MODIFY COLUMN `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '话题状态';
ALTER TABLE `dask_upgrade_log` MODIFY COLUMN `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '升级状态：0进行中，1成功，2失败';
ALTER TABLE `dask_users` MODIFY COLUMN `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '用户状态：0删除，1正常，2待审核，3已封禁';
ALTER TABLE `dask_users_verify` MODIFY COLUMN `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '审核状态：0未提交，1待审核，2已审核，3拒绝';

-- 4. 排序字段统一 smallint unsigned。
ALTER TABLE `dask_announce` MODIFY COLUMN `sort` smallint unsigned NOT NULL DEFAULT '0' COMMENT '排序值';
ALTER TABLE `dask_article` MODIFY COLUMN `sort` smallint unsigned NOT NULL DEFAULT '0' COMMENT '排序值';
ALTER TABLE `dask_question` MODIFY COLUMN `sort` smallint unsigned NOT NULL DEFAULT '0' COMMENT '排序值';

-- 5. 补齐剩余布尔/状态字段：避免 NULL 与 tinyint(1) 多状态混用。
UPDATE `dask_answer` SET `force_fold`=0 WHERE `force_fold` IS NULL;
UPDATE `dask_app_token` SET `type`=1 WHERE `type` IS NULL;
UPDATE `dask_article_vote` SET `vote_value`=0 WHERE `vote_value` IS NULL;
UPDATE `dask_question_vote` SET `vote_value`=0 WHERE `vote_value` IS NULL;
UPDATE `dask_report` SET `handle_type`=0 WHERE `handle_type` IS NULL;
UPDATE `dask_topic` SET `related`=0 WHERE `related` IS NULL;
UPDATE `dask_users` SET `sex`=0 WHERE `sex` IS NULL;
UPDATE `dask_users` SET `url_token_modified`=0 WHERE `url_token_modified` IS NULL;
UPDATE `dask_users_inbox` SET `recipient_remove`=0 WHERE `recipient_remove` IS NULL;
UPDATE `dask_users_inbox` SET `sender_remove`=0 WHERE `sender_remove` IS NULL;
UPDATE `dask_users_notify_setting` SET `user_setting`=1 WHERE `user_setting` IS NULL;
ALTER TABLE `dask_announce` MODIFY COLUMN `set_top` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否置顶';
ALTER TABLE `dask_answer` MODIFY COLUMN `force_fold` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否强制折叠';
ALTER TABLE `dask_article` MODIFY COLUMN `set_top` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否置顶';
ALTER TABLE `dask_post_relation` MODIFY COLUMN `set_top` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否置顶';
ALTER TABLE `dask_question` MODIFY COLUMN `set_top` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否置顶';
ALTER TABLE `dask_topic` MODIFY COLUMN `related` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否被用户关联';
ALTER TABLE `dask_users` MODIFY COLUMN `sex` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '性别：0保密，1男，2女';
ALTER TABLE `dask_users` MODIFY COLUMN `url_token_modified` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '个性域名是否可修改';
ALTER TABLE `dask_users_inbox` MODIFY COLUMN `recipient_remove` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '接收者是否已删除';
ALTER TABLE `dask_users_inbox` MODIFY COLUMN `sender_remove` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '发送者是否已删除';
ALTER TABLE `dask_users_notify_setting` MODIFY COLUMN `user_setting` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否允许用户配置';
ALTER TABLE `dask_app_token` MODIFY COLUMN `type` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '令牌类型：1系统模块，2插件';
ALTER TABLE `dask_article_vote` MODIFY COLUMN `vote_value` tinyint NOT NULL DEFAULT '0' COMMENT '投票值：1赞同，-1反对';
ALTER TABLE `dask_question_vote` MODIFY COLUMN `vote_value` tinyint NOT NULL DEFAULT '0' COMMENT '投票值：1赞同，-1反对';
ALTER TABLE `dask_report` MODIFY COLUMN `handle_type` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '处理方式：0拒绝，1删除内容，2修改内容';
