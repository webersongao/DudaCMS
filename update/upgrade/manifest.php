<?php
// +----------------------------------------------------------------------
// | DudaCMS
// +----------------------------------------------------------------------
// | Copyright (c) 2026 https://ask.btbk.org
// +----------------------------------------------------------------------
// | DudaCMS团队一款基于TP8开发的社交化知识付费问答系统、企业内部知识库系统，打造私有社交化问答、内部知识存储
// +----------------------------------------------------------------------
// | Author: DudaCMS团队 <ask@btbk.org>
// +----------------------------------------------------------------------

/**
 * 数据库升级清单：升级步骤的唯一权威来源。
 *
 * 程序版本号只维护在 config/version.php；本清单只描述「数据库需要执行哪些迁移」。
 * 新增一个带数据库变更的版本时，只需两步：
 *   1) 把幂等 SQL 放到本目录；
 *   2) 在下面追加一条以目标版本为键的记录（from / file 必填）。
 *
 * 老版本包若没有本清单，升级器仍会按 SQL 文件名与文件头元数据扫描，保持兼容。
 *
 * 字段说明：
 *   from    前置版本（必须与实际相邻版本一致，禁止跨版本）
 *   file    同目录下的 SQL 文件名
 *   title   简短标题
 *   summary 补充说明，可多条空格拼接
 *   risk    风险等级提示（low / medium / high）
 *   verify  升级后自检项，格式 table:表名 / column:表名.列名 / index:表名.索引名（均不含表前缀）
 */
return [
    '1.0.1' => [
        'from'    => '1.0.0',
        'file'    => 'update1.0.0to1.0.1.sql',
        'title'   => '系统运维聚合与第三方登录菜单升级',
        'summary' => '统一版本源；系统运维菜单合并；第三方登录提升一级入口；权限与图标修复；新增在线升级记录表',
        'risk'    => 'low',
        'verify'  => 'table:upgrade_log, column:admin_auth.icon, column:config.tips',
    ],
    '1.0.2' => [
        'from'    => '1.0.1',
        'file'    => 'update1.0.1to1.0.2.sql',
        'title'   => '安全加固、统计回刷与查询索引升级',
        'summary' => '修复回答数/赞同数统计口径；补充内容表复合索引与全文索引；修正失效种子图路径',
        'risk'    => 'medium',
        'verify'  => 'index:article.idx_article_status_time, index:answer.idx_answer_question_status_time, index:topic_relation.idx_tr_topic_status_time, index:question.idx_q_status_time',
    ],
    '1.0.3' => [
        'from'    => '1.0.2',
        'file'    => 'update1.0.2to1.0.3.sql',
        'title'   => '第三方登录 Token 字段扩容',
        'summary' => '将 access_token / refresh_token 从 varchar(255) 扩为 TEXT，避免 Google 等平台令牌过长导致登录失败',
        'risk'    => 'low',
        'verify'  => 'column:oauth.access_token, column:oauth.refresh_token',
    ],
    '1.0.4' => [
        'from'    => '1.0.3',
        'file'    => 'update1.0.3to1.0.4.sql',
        'title'   => '第三方登录规范统一与数据库结构审计优化',
        'summary' => '统一回调文案为不带 .html；补充审计索引、扩容字段、修正注释与密钥输入类型',
        'risk'    => 'low',
        'verify'  => 'index:users_token.idx_ut_uid_expire, index:attach.idx_attach_item, column:users_online.last_url',
    ],
    '1.0.5' => [
        'from'    => '1.0.4',
        'file'    => 'update1.0.4to1.0.5.sql',
        'title'   => '配置体系精简与现代化',
        'summary' => '删除失效配置；补齐缺失键；修正类型、命名与文案；密钥不再明文回显',
        'risk'    => 'low',
        'verify'  => 'table:config',
    ],
    '1.0.6' => [
        'from'    => '1.0.5',
        'file'    => 'update1.0.5to1.0.6.sql',
        'title'   => '字段类型与注释规范化',
        'summary' => '修正布尔与计数字段显示宽度；搜索日志 IP 支持 IPv6；安全补充关系表唯一约束',
        'risk'    => 'low',
        'verify'  => 'column:search_log.ip, column:links.status, index:users_favorite.uk_uf_uid_item, index:answer_thanks.uk_at_uid_answer',
    ],
    '1.0.7' => [
        'from'    => '1.0.6',
        'file'    => 'update1.0.6to1.0.7.sql',
        'title'   => '配置类型兼容修复',
        'summary' => '将历史配置 type 映射为表单构造器支持的类型，修复后台配置页 500',
        'risk'    => 'low',
        'verify'  => 'table:config',
    ],
    '1.0.8' => [
        'from'    => '1.0.7',
        'file'    => 'update1.0.7to1.0.8.sql',
        'title'   => '全站统一规范配置补齐',
        'summary' => '新增时区、默认外观、水印参数与视频压缩配置',
        'risk'    => 'low',
        'verify'  => 'table:config',
    ],
    '1.0.9' => [
        'from'    => '1.0.8',
        'file'    => 'update1.0.8to1.0.9.sql',
        'title'   => '布尔与状态字段类型统一',
        'summary' => '布尔统一 tinyint(1) unsigned NOT NULL；多状态统一 tinyint unsigned；排序统一 smallint unsigned',
        'risk'    => 'low',
        'verify'  => 'column:action_log.anonymous, column:users.status, column:article.sort',
    ],
    '1.0.10' => [
        'from'    => '1.0.9',
        'file'    => 'update1.0.9to1.0.10.sql',
        'title'   => '配置项精简与分组分类展示',
        'summary' => '移除资源URL与水印最大行数；配置页按功能分类显示，避免大量选项堆在一页',
        'risk'    => 'low',
        'verify'  => 'config:cdn_url=absent, config:watermark_max_lines=absent',
    ],
    '1.0.11' => [
        'from'    => '1.0.10',
        'file'    => 'update1.0.10to1.0.11.sql',
        'title'   => '站点资源与账号策略内置化',
        'summary' => '站点图标/描述/关键词/联系邮箱、水印字体、账号策略改为代码集中配置，后台不再自定义',
        'risk'    => 'low',
        'verify'  => 'config:site_logo=absent, config:password_min_length=absent',
    ],
    '1.0.12' => [
        'from'    => '1.0.11',
        'file'    => 'update1.0.11to1.0.12.sql',
        'title'   => '草稿与多语言内置化',
        'summary' => '草稿默认开启间隔30秒、默认启用多语言改为内置；清理无读取点的失效配置',
        'risk'    => 'low',
        'verify'  => 'config:auto_save_draft=absent, config:remove_xss=absent',
    ],
];
