SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
/*SET GLOBAL innodb_large_prefix = ON;
SET GLOBAL innodb_file_format=Barracuda;
SET GLOBAL innodb_file_per_table=ON;*/
-- ----------------------------
-- Table structure for dask_admin_auth
-- ----------------------------
DROP TABLE IF EXISTS `dask_admin_auth`;
CREATE TABLE `dask_admin_auth`  (
       `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
       `pid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父ID',
       `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '控制器/方法',
       `title` char(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
       `type` tinyint(1) NOT NULL DEFAULT 1,
       `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '菜单状态',
       `condition` char(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
       `sort` mediumint(8) NOT NULL DEFAULT 0 COMMENT '排序',
       `auth_open` tinyint(2) NULL DEFAULT 1,
       `icon` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '菜单图标',
       `create_time` int(11) NULL DEFAULT 0 COMMENT '添加时间',
       `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
       `param` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '参数',
       `group` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '权限分组',
       PRIMARY KEY (`id`) USING BTREE,
       UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC COMMENT='后台权限';

INSERT INTO `dask_admin_auth` VALUES (1, 0, 'index', '控制台', 1, 1, '', 0, 1, 'si si-speedometer', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (2, 0, 'system', '系统管理', 1, 1, '', 0, 1, 'fas fa-cogs', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (3, 0, 'member', '用户管理', 1, 1, '', 2, 1, 'fa fa-users', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (4, 0, 'extend', '系统拓展', 1, 1, '', 3, 1, 'fa fa-business-time', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (6, 0, 'content', '内容管理', 1, 1, '', 1, 1, 'fa fa-box', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (7, 1, 'Index/logout', '退出登录', 1, 0, '', 3, 1, 'fas fa-cogs', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (8, 1, 'Index/index', '后台首页', 1, 1, '', 0, 1, 'fa fa-home', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (9, 1, 'Index/select2', 'select2下拉', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (10, 1, 'Index/icons', '选择图标', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (11, 1, 'Index/clear', '清空缓存', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (13, 2, 'admin.Config/config', '系统配置', 1, 1, '', 0, 1, 'fa fa-edit', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (29, 321, 'admin.Group/index', '系统组', 1, 1, '', 2, 1, 'fa fa-people-carry', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (30, 29, 'admin.Group/add', '操作-添加', 1, 0, '', 3, 1, 'fas fa-cogs', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (31, 29, 'admin.Group/edit', '操作-编辑', 1, 0, '', 3, 1, 'fas fa-cogs', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (32, 29, 'admin.Group/delete', '操作-删除', 1, 0, '', 3, 1, 'fas fa-cogs', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (33, 29, 'admin.Group/permission', '操作-权限', 1, 0, '', 3, 1, 'fas fa-cogs', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (34, 29, 'admin.Group/state', '操作-状态', 1, 0, '', 3, 1, 'fas fa-cogs', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (35, 29, 'admin.Group/export', '操作-导出', 1, 0, '', 3, 1, 'fas fa-cogs', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (36, 29, 'admin.Group/choose', '操作-选择', 1, 0, '', 3, 1, 'fas fa-cogs', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (50, 3, 'member.Users/index', '用户管理', 1, 1, '', 0, 1, 'fa fa-users', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (51, 50, 'member.Users/add', '操作-添加', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (52, 50, 'member.Users/edit', '操作-编辑', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (53, 50, 'member.Users/delete', '操作-删除', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (54, 50, 'member.Users/state', '操作-状态', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (55, 50, 'member.Users/export', '操作-导出', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (56, 50, 'member.Users/sort', '操作-排序', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (58, 50, 'member.Users/choose', '操作-选择', 1, 0, '', 3, 1, 'fas fa-cogs', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (68, 321, 'member.Permission/index', '用户权限', 1, 1, '', 2, 1, 'fa fa-user-check', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (69, 68, 'member.Permission/add', '操作-添加', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (70, 68, 'member.Permission/edit', '操作-编辑', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (71, 68, 'member.Permission/delete', '操作-删除', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (72, 68, 'member.Permission/state', '操作-状态', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (73, 68, 'member.Permission/export', '操作-导出', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (74, 68, 'member.Permission/sort', '操作-排序', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (76, 68, 'member.Permission/choose', '操作-选择', 1, 0, '', 3, 1, 'fas fa-cogs', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (77, 4, 'extend.Database/database', '数据库备份', 1, 1, '', 31, 1, 'fa fa-server', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (78, 77, 'extend.Database/backup', '操作-备份', 1, 0, '', 1, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (79, 77, 'extend.Database/repair', '操作-修复', 1, 0, '', 2, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (80, 77, 'extend.Database/optimize', '操作-优化', 1, 0, '', 3, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (81, 4, 'extend.Database/restore', '数据库还原', 1, 1, '', 32, 1, 'fa fa-recycle', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (82, 77, 'extend.Database/import', '操作-还原', 1, 0, '', 1, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (83, 77, 'extend.Database/downFile', '操作-下载', 1, 0, '', 2, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (84, 77, 'extend.Database/delete', '操作-删除', 1, 0, '', 3, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (85, 4, 'extend.Links/index', '友情链接', 1, 1, '', 61, 1, 'fa fa-link', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (86, 85, 'extend.Links/add', '操作-添加', 1, 0, '', 1, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (87, 85, 'extend.Links/edit', '操作-修改', 1, 0, '', 3, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (88, 85, 'extend.Links/delete', '操作-删除', 1, 0, '', 5, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (89, 85, 'extend.Links/export', '操作-导出', 1, 0, '', 7, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (90, 85, 'extend.Links/sort', '操作-排序', 1, 0, '', 8, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (91, 85, 'extend.Links/state', '操作-状态', 1, 0, '', 9, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (92, 85, 'extend.Links/choose', '操作-选择', 1, 0, '', 9, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (118, 4, 'extend.Upgrade/index', '本地升级', 1, 1, '', 0, 1, 'fa fa-download', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (119, 118, 'extend.Upgrade/upload', '上传升级包', 1, 0, '', 50, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (120, 118, 'extend.Upgrade/check', '本地升级包检测', 1, 0, '', 50, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (124, 118, 'extend.Upgrade/apply', '应用升级包', 1, 0, '', 50, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (126, 6, 'content.Page/index', '页面管理', 1, 1, '', 2, 1, 'fa fa-file', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (127, 126, 'content.Page/add', '操作-添加', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (128, 126, 'content.Page/edit', '操作-编辑', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (129, 126, 'content.Page/delete', '操作-删除', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (130, 126, 'content.Page/state', '操作-状态', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (131, 126, 'content.Page/export', '操作-导出', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (132, 126, 'content.Page/choose', '操作-选择', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (133, 126, 'content.Page/sort', '操作-排序', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (134, 6, 'content.Category/index', '分类管理', 1, 1, '', 2, 1, 'fa fa-atlas', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (135, 134, 'content.Category/add', '操作-添加', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (136, 134, 'content.Category/edit', '操作-编辑', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (137, 134, 'content.Category/delete', '操作-删除', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (138, 134, 'content.Category/state', '操作-状态', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (139, 134, 'content.Category/sort', '操作-排序', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (140, 134, 'content.Category/export', '操作-导出', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (141, 134, 'content.Category/choose', '操作-选择', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (142, 321, 'member.CreditGroup/index', '成长等级组', 1, 1, '', 2, 1, 'fa fa-database', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (143, 142, 'member.CreditGroup/add', '操作-添加', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (144, 142, 'member.CreditGroup/edit', '操作-编辑', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (145, 142, 'member.CreditGroup/delete', '操作-删除', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (146, 142, 'member.CreditGroup/state', '操作-状态', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (147, 142, 'member.CreditGroup/permission', '用户权限', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (148, 142, 'member.CreditGroup/sort', '操作-排序', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (149, 142, 'member.CreditGroup/export', '操作-导出', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (150, 142, 'member.CreditGroup/choose', '操作-选择', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (151, 322, 'member.CreditRule/index', '成长规则', 1, 1, '', 61, 1, 'fa fa-database', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (152, 151, 'member.CreditRule/add', '操作-添加', 1, 0, '', 1, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (153, 151, 'member.CreditRule/edit', '操作-修改', 1, 0, '', 3, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (154, 151, 'member.CreditRule/delete', '操作-删除', 1, 0, '', 5, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (155, 151, 'member.CreditRule/export', '操作-导出', 1, 0, '', 7, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (156, 151, 'member.CreditRule/deleteLog', '操作-删除记录', 1, 0, '', 8, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (157, 151, 'member.CreditRule/state', '操作-状态', 1, 0, '', 9, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (158, 151, 'member.CreditRule/detail', '记录详情', 1, 0, '', 61, 1, 'fa fa-link', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (159, 322, 'member.CreditRule/log', '成长记录', 1, 1, '', 61, 1, 'far fa-bookmark', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (160, 151, 'member.CreditRule/choose', '操作-选择', 1, 0, '', 61, 1, 'fa fa-link', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (161, 323, 'member.Action/index', '行为规则', 1, 1, '', 61, 1, 'fa fa-cog', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (162, 161, 'member.Action/add', '操作-添加', 1, 0, '', 1, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (163, 161, 'member.Action/edit', '操作-修改', 1, 0, '', 3, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (164, 161, 'member.Action/delete', '操作-删除', 1, 0, '', 5, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (165, 161, 'member.Action/export', '操作-导出', 1, 0, '', 7, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (166, 161, 'member.Action/deleteLog', '操作-删除记录', 1, 0, '', 8, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (167, 161, 'member.Action/state', '操作-状态', 1, 0, '', 9, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (168, 161, 'member.Action/detail', '记录详情', 1, 0, '', 61, 1, 'fa fa-link', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (169, 323, 'member.Action/log', '行为记录', 1, 1, '', 61, 1, 'far fa-clipboard', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (170, 161, 'member.Action/choose', '操作-选择', 1, 0, '', 61, 1, 'fa fa-link', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (171, 3, 'userVerify', '认证管理', 1, 1, '', 9, 1, 'fa fa-user-check', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (172, 171, 'member.Verify/index', '认证记录', 1, 1, '', 9, 1, 'fa fa-user-check', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (173, 171, 'member.Verify/field', '认证字段', 1, 1, '', 10, 1, 'far fa-check-square', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (174, 173, 'member.Verify/edit', '操作-编辑', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (175, 172, 'member.Verify/delete', '操作-删除', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (176, 173, 'member.Verify/state', '操作-状态', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (177, 173, 'member.Verify/add', '操作-添加', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (178, 173, 'member.Verify/sort', '操作-排序', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (179, 172, 'member.Verify/preview', '操作-预览', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (180, 172, 'member.Verify/manager', '操作-管理', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (181, 173, 'member.Verify/choose', '操作-选择', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (183, 6, 'content.Question/index', '问题管理', 1, 1, '', 0, 1, 'fa fa-folder', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (184, 183, 'content.Question/add', '操作-添加', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (185, 183, 'content.Question/edit', '操作-编辑', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (186, 183, 'content.Question/delete', '操作-删除', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (187, 183, 'content.Question/state', '操作-状态', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (188, 183, 'content.Question/export', '操作-导出', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (189, 183, 'content.Question/seo', '操作-SEO设置', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (190, 183, 'content.Question/sort', '操作-排序', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (191, 183, 'content.Question/choose', '操作-选择', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (192, 6, 'content.Article/index', '文章管理', 1, 1, '', 0, 1, 'fa fa-folder', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (193, 192, 'content.Article/add', '操作-添加', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (194, 192, 'content.Article/edit', '操作-编辑', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (195, 192, 'content.Article/delete', '操作-删除', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (196, 192, 'content.Article/state', '操作-状态', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (197, 192, 'content.Article/export', '操作-导出', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (198, 192, 'content.Article/seo', '操作-SEO设置', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (199, 192, 'content.Article/sort', '操作-排序', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (200, 192, 'content.Article/choose', '操作-选择', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (201, 6, 'content.Column/index', '专栏管理', 1, 1, '', 0, 1, 'fa fa-folder', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (202, 201, 'content.Column/add', '操作-添加', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (203, 201, 'content.Column/edit', '操作-编辑', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (204, 201, 'content.Column/delete', '操作-删除', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (205, 201, 'content.Column/state', '操作-状态', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (206, 201, 'content.Column/export', '操作-导出', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (207, 201, 'content.Column/seo', '操作-SEO设置', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (208, 201, 'content.Column/sort', '操作-排序', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (209, 201, 'content.Column/choose', '操作-选择', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (210, 6, 'content.Topic/index', '话题管理', 1, 1, '', 0, 1, 'fa fa-folder', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (211, 210, 'content.Topic/add', '操作-添加', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (212, 210, 'content.Topic/edit', '操作-编辑', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (213, 210, 'content.Topic/delete', '操作-删除', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (214, 210, 'content.Topic/state', '操作-状态', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (215, 210, 'content.Topic/export', '操作-导出', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (216, 210, 'content.Topic/seo', '操作-SEO设置', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (217, 210, 'content.Topic/sort', '操作-排序', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (218, 210, 'content.Topic/choose', '操作-选择', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (219, 6, 'content.Approval/index', '审核管理', 1, 1, '', 0, 1, 'fa fa-folder', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (220, 219, 'content.Approval/add', '操作-添加', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (221, 219, 'content.Approval/edit', '操作-编辑', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (222, 219, 'content.Approval/delete', '操作-删除', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (223, 219, 'content.Approval/state', '操作-状态', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (224, 219, 'content.Approval/export', '操作-导出', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (225, 219, 'content.Approval/sort', '操作-排序', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (226, 219, 'content.Approval/choose', '操作-选择', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (227, 4, 'extend.Log/index', '操作记录', 1, 1, '', 61, 1, 'fa fa-link', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (228, 161, 'extend.Log/delete', '操作-删除', 1, 0, '', 5, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (229, 161, 'extend.Log/export', '操作-导出', 1, 0, '', 7, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (230, 161, 'extend.Log/detail', '记录详情', 1, 0, '', 61, 1, 'fa fa-link', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (243, 1, 'Index/statistic', '后台统计', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (244, 6, 'content.Report/index', '举报管理', 1, 1, '', 0, 1, 'fa fa-info-circle', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (245, 244, 'content.Report/edit', '操作-编辑', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (246, 244, 'content.Report/delete', '操作-删除', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (247, 244, 'content.Report/state', '操作-状态', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (248, 244, 'content.Report/export', '操作-导出', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (249, 244, 'content.Report/sort', '操作-排序', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (250, 244, 'content.Report/choose', '操作-选择', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (251, 6, 'content.Answer/index', '回答管理', 1, 1, '', 2, 1, 'fa fa-file', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (252, 251, 'content.Answer/add', '操作-添加', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (253, 251, 'content.Answer/edit', '操作-编辑', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (254, 251, 'content.Answer/delete', '操作-删除', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (255, 251, 'content.Answer/state', '操作-状态', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (256, 251, 'content.Answer/export', '操作-导出', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (257, 251, 'content.Answer/choose', '操作-选择', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (258, 251, 'content.Answer/sort', '操作-排序', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (284, 50, 'member.Users/forbidden', '操作-封禁', 1, 0, '', 3, 1, 'fas fa-cogs', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (285, 50, 'member.Users/un_forbidden', '操作-解除封禁', 1, 0, '', 3, 1, 'fas fa-cogs', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (286, 50, 'member.Users/approval', '操作-审核', 1, 0, '', 3, 1, 'fas fa-cogs', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (287, 50, 'member.Users/decline', '操作-拒绝审核', 1, 0, '', 3, 1, 'fas fa-cogs', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (288, 50, 'member.Users/manager', '操作-管理', 1, 0, '', 3, 1, 'fas fa-cogs', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (289, 50, 'member.Users/credit', '操作-成长值', 1, 0, '', 3, 1, 'fas fa-cogs', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (290, 6, 'content.Announce/index', '公告管理', 1, 1, '', 2, 1, 'fa fa-file', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (291, 290, 'content.Announce/add', '操作-添加', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (292, 290, 'content.Announce/edit', '操作-编辑', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (293, 290, 'content.Announce/delete', '操作-删除', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (294, 290, 'content.Announce/state', '操作-状态', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (295, 290, 'content.Announce/export', '操作-导出', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (296, 290, 'content.Announce/choose', '操作-选择', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (297, 290, 'content.Announce/sort', '操作-排序', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (303, 201, 'content.Column/approval', '操作-审核通过', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (304, 201, 'content.Column/decline', '操作-拒绝审核', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (305, 192, 'content.Article/manager', '操作-更多', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (306, 183, 'content.Question/manager', '操作-更多', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (307, 3, 'member.NotifySetting/index', '通知配置', 1, 1, '', 0, 1, 'fa fa-bell', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (308, 307, 'member.NotifySetting/add', '操作-添加', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (309, 307, 'member.NotifySetting/edit', '操作-编辑', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (310, 307, 'member.NotifySetting/group_add', '操作-添加分组', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (311, 307, 'member.NotifySetting/state', '操作-状态', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (312, 307, 'member.NotifySetting/group', '通知分组', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (313, 307, 'member.NotifySetting/sort', '操作-排序', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (314, 307, 'member.NotifySetting/group_edit', '操作-编辑分组', 1, 0, '', 3, 1, 'fas fa-cogs', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (315, 171, 'member.Verify/type', '认证类型', 1, 1, '', 10, 1, 'far fa-check-square', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (316, 173, 'member.Verify/delete_field', '操作-删除字段', 1, 0, '', 10, 1, 'far fa-check-square', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (317, 315, 'member.Verify/add_type', '操作-添加', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (318, 315, 'member.Verify/edit_type', '操作-编辑', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (319, 315, 'member.Verify/delete_type', '操作-删除类型', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (320, 315, 'member.Verify/state_type', '操作-状态', 1, 0, '', 0, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (321, 3, 'user_group', '用户组管理', 1, 1, '', 50, 1, 'fa fa-user-friends', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (322, 3, 'credit', '成长值管理', 1, 1, '', 50, 1, 'fa fa-database', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (323, 3, 'user_action', '行为管理', 1, 1, '', 50, 1, 'fa fa-bolt', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (324, 3, 'forbidden', '封禁管理', 1, 1, '', 0, 1, 'fa fa-ban', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (325, 324, 'member.Forbidden/ips', '封禁IP', 1, 1, '', 0, 1, 'fa fa-ban', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (353, 4, 'extend.Token/index', '接口请求', 1, 1, '', 61, 1, 'fa fa-anchor', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (354, 353, 'extend.Token/delete', '操作-删除', 0, 0, '', 5, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (355, 353, 'extend.Token/add', '操作-添加', 1, 0, '', 7, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (356, 353, 'extend.Token/edit', '操作-删除', 1, 0, '', 61, 1, 'fa fa-link', 0, 0, '', 'system');

INSERT INTO `dask_admin_auth` VALUES (373, 6, 'content.Help/index', '帮助章节', 1, 1, '', 61, 1, 'icon-help', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (374, 373, 'content.Help/add', '操作-添加', 1, 0, '', 1, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (375, 373, 'content.Help/edit', '操作-修改', 1, 0, '', 3, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (376, 373, 'content.Help/delete', '操作-删除', 1, 0, '', 5, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (377, 373, 'content.Help/export', '操作-导出', 1, 0, '', 7, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (378, 373, 'content.Help/sort', '操作-排序', 1, 0, '', 8, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (379, 373, 'content.Help/state', '操作-状态', 1, 0, '', 9, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (380, 373, 'content.Help/choose', '操作-选择', 1, 0, '', 9, 1, '', 0, 0, '', 'system');

INSERT INTO `dask_admin_auth` VALUES (381, 6, 'content.Feature/index', '专题管理', 1, 1, '', 61, 1, 'icon-help', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (382, 381, 'content.Feature/add', '操作-添加', 1, 0, '', 1, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (383, 381, 'content.Feature/edit', '操作-修改', 1, 0, '', 3, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (384, 381, 'content.Feature/delete', '操作-删除', 1, 0, '', 5, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (385, 381, 'content.Feature/export', '操作-导出', 1, 0, '', 7, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (386, 381, 'content.Feature/sort', '操作-排序', 1, 0, '', 8, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (387, 381, 'content.Feature/state', '操作-状态', 1, 0, '', 9, 1, '', 0, 0, '', 'system');
INSERT INTO `dask_admin_auth` VALUES (388, 381, 'content.Feature/choose', '操作-选择', 1, 0, '', 9, 1, '', 0, 0, '', 'system');






DROP TABLE IF EXISTS `dask_invitation`;
CREATE TABLE `dask_invitation` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '激活ID',
      `uid` int(11) DEFAULT '0' COMMENT '用户ID',
      `invitation_code` varchar(32) DEFAULT NULL COMMENT '激活码',
      `invitation_email` varchar(255) DEFAULT NULL COMMENT '激活email',
      `active_type` varchar(32) NOT NULL DEFAULT 'link' COMMENT '激活方式',
      `create_time` int(10) DEFAULT NULL COMMENT '添加时间',
      `add_ip` bigint(12) DEFAULT NULL COMMENT '添加IP',
      `active_expire` int(10) NOT NULL DEFAULT 0 COMMENT '激活过期',
      `active_time` int(10) DEFAULT NULL COMMENT '激活时间',
      `active_ip` bigint(12) DEFAULT NULL COMMENT '激活IP',
      `active_status` tinyint(4) DEFAULT '0' COMMENT '1-未使用2-已使用',
      `active_uid` int(11) DEFAULT NULL,
      PRIMARY KEY (`id`) USING BTREE,
      KEY `uid` (`uid`) USING BTREE,
      KEY `active_type` (`active_type`) USING BTREE,
      KEY `invitation_code` (`invitation_code`) USING BTREE,
      KEY `invitation_email` (`invitation_email`) USING BTREE,
      KEY `active_time` (`active_time`) USING BTREE,
      KEY `active_ip` (`active_ip`) USING BTREE,
      KEY `active_status` (`active_status`) USING BTREE
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='邀请表';

-- ----------------------------
-- Table structure for dask_admin_group
-- ----------------------------
DROP TABLE IF EXISTS `dask_admin_group`;
CREATE TABLE `dask_admin_group`  (
    `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
    `title` char(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '组名称',
    `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
    `rules` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '后台用户组拥有的规则id， 多个规则\",\"隔开',
    `permission` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '分组权限',
    `system` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '1系统内置',
     PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC COMMENT='权限分组表';

-- ----------------------------
-- Records of dask_admin_group
-- ----------------------------
INSERT INTO `dask_admin_group` (`id`, `title`, `status`, `rules`, `permission`, `system`) VALUES (1, '超级管理员', 1, '*', '{"visit_website":"Y","publish_question_enable":"Y","publish_question_approval":"Y","publish_article_enable":"Y","publish_article_approval":"N","publish_answer_enable":"Y","publish_answer_approval":"N","modify_answer_approval":"N","modify_article_approval":"N","modify_question_approval":"N","available_invite_count":"500","create_topic_enable":"Y","publish_approval_time_start":"","publish_approval_time_end":"","publish_url":"Y","publish_question_num":"100","publish_article_num":"100","topic_manager":"Y","modify_article":"Y","remove_article":"Y","modify_question":"Y","remove_question":"Y","set_best_answer":"Y","edit_content_topic":"Y","recommend_post":"Y","set_top_post":"Y","modify_answer":"Y","remove_answer":"Y","lock_topic":"Y","remove_topic":"Y"}', 1);
INSERT INTO `dask_admin_group` (`id`, `title`, `status`, `rules`, `permission`, `system`) VALUES (2, '前台管理员', 1, '1,8,9,10,11,243,7,42,43,44,45,46,47,48,49,6,183,184,185,186,187,188,189,190,191,192,193,194,195,196,197,198,199,200,201,202,203,204,205,206,207,208,209,210,211,212,213,214,215,216,217,218,219,220,221,222,224,223,225,226,227,228,230,229,231,232,234,233,235,244,245,246,247,248,249,250,126,127,128,129,130,131,132,133,134,135,136,137,138,139,140,141,50,52,59,61,64,68,70,142,144,147,171,173,178,158,159,169,168,77,78,82,79,83,80,84,5,101,102,103,104,106,107,109,118,119,120,124,125,121,122,123', '{"visit_website":"Y","publish_question_enable":"Y","publish_question_approval":"N","publish_article_enable":"Y","publish_article_approval":"N","publish_answer_enable":"Y","publish_answer_approval":"N","modify_answer_approval":"N","modify_article_approval":"N","modify_question_approval":"N","available_invite_count":"50","create_topic_enable":"Y","publish_approval_time_start":"","publish_approval_time_end":"","publish_url":"Y","publish_question_num":"100","publish_article_num":"100","topic_manager":"Y","modify_article":"Y","remove_article":"Y","modify_question":"Y","remove_question":"Y","set_best_answer":"Y","edit_content_topic":"Y","recommend_post":"Y","set_top_post":"Y","modify_answer":"Y","remove_answer":"Y","lock_topic":"Y","remove_topic":"Y"}', 1);
INSERT INTO `dask_admin_group` (`id`, `title`, `status`, `rules`, `permission`, `system`) VALUES (3, '未验证用户', 1, '0', '{"visit_website":"Y","publish_question_enable":"N","publish_question_approval":"Y","publish_article_enable":"N","publish_article_approval":"Y","publish_answer_enable":"N","publish_answer_approval":"Y","modify_answer_approval":"Y","modify_article_approval":"Y","modify_question_approval":"Y","available_invite_count":"0","create_topic_enable":"N","publish_approval_time_start":"","publish_approval_time_end":"","publish_url":"N","publish_question_num":"3","publish_article_num":"3","topic_manager":"N","modify_article":"N","remove_article":"N","modify_question":"N","remove_question":"N","set_best_answer":"N","edit_content_topic":"N","recommend_post":"N","set_top_post":"N","modify_answer":"N","remove_answer":"N","lock_topic":"N","remove_topic":"N"}', 1);
INSERT INTO `dask_admin_group` (`id`, `title`, `status`, `rules`, `permission`, `system`) VALUES (4, '普通用户', 1, '0', '{\"visit_website\":\"Y\",\"publish_question_enable\":\"Y\",\"publish_question_approval\":\"N\",\"publish_article_enable\":\"Y\",\"publish_article_approval\":\"N\",\"publish_answer_enable\":\"N\",\"publish_answer_approval\":\"N\",\"modify_answer_approval\":\"N\",\"modify_article_approval\":\"N\",\"modify_question_approval\":\"N\",\"available_invite_count\":\"5\",\"create_topic_enable\":\"N\",\"publish_approval_time_start\":\"\",\"publish_approval_time_end\":\"\",\"publish_url\":\"Y\",\"publish_question_num\":\"3\",\"publish_article_num\":\"3\",\"topic_manager\":\"N\",\"modify_article\":\"Y\",\"remove_article\":\"Y\",\"modify_question\":\"\",\"remove_question\":\"Y\",\"set_best_answer\":\"Y\",\"edit_content_topic\":\"Y\",\"recommend_content\":\"Y\",\"recommend_post\":\"Y\",\"set_top_post\":\"Y\",\"modify_answer\":\"Y\",\"remove_answer\":\"Y\",\"lock_topic\":\"Y\",\"remove_topic\":\"Y\"}', 1);
INSERT INTO `dask_admin_group` (`id`, `title`, `status`, `rules`, `permission`, `system`) VALUES (5, '游客', 1, '0', '{"visit_website":"Y"}', 1);

-- ----------------------------
-- Table structure for dask_attach
-- ----------------------------
DROP TABLE IF EXISTS `dask_attach`;
CREATE TABLE `dask_attach` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户表id',
      `name` varchar(255) DEFAULT NULL COMMENT '文件名',
      `thumb` varchar(255) DEFAULT NULL COMMENT '缩略图',
      `path` varchar(255) DEFAULT NULL COMMENT '路径',
      `url` varchar(255) DEFAULT NULL COMMENT '完整地址',
      `ext` varchar(5) DEFAULT NULL COMMENT '后缀',
      `size` int(11) DEFAULT '0' COMMENT '大小',
      `width` varchar(30) DEFAULT '0' COMMENT '宽度',
      `height` varchar(30) DEFAULT '0' COMMENT '高度',
      `md5` char(32) DEFAULT NULL,
      `sha1` varchar(64) DEFAULT NULL,
      `mime` varchar(80) DEFAULT NULL,
      `driver` varchar(20) DEFAULT 'local',
      `status` tinyint(1) NOT NULL DEFAULT '1',
      `sort` int(5) NOT NULL DEFAULT '50',
      `item_type` varchar(255) DEFAULT NULL COMMENT '内容类型',
      `item_id` int(10) unsigned DEFAULT '0' COMMENT '内容ID',
      `access_key` varchar(255) DEFAULT NULL COMMENT '批次ID',
      `extends` text COMMENT '附属字段',
      `create_time` int(11) DEFAULT NULL,
      `update_time` int(11) DEFAULT NULL,
      PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='附件表';

-- ----------------------------
-- Table structure for dask_config
-- ----------------------------
DROP TABLE IF EXISTS `dask_config`;
CREATE TABLE `dask_config` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '变量名',
      `group` varchar(30) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '分组',
      `title` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '变量标题',
      `tips` varchar(500) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '变量描述',
      `type` varchar(30) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '类型:string,text,int,bool,array,datetime,date,file',
      `value` text COLLATE utf8mb4_general_ci NOT NULL COMMENT '变量值',
      `option` text COLLATE utf8mb4_general_ci NOT NULL COMMENT '变量字典数据',
      `sort` int(10) unsigned NOT NULL DEFAULT '50' COMMENT '排序值,数字越小越靠前',
      `settings` text COLLATE utf8mb4_general_ci COMMENT '附属信息',
      `system` tinyint(1) unsigned DEFAULT '0' COMMENT '系统配置',
      `source` tinyint(1) UNSIGNED NULL DEFAULT '0' COMMENT '配置来源',
      PRIMARY KEY (`id`) USING BTREE,
      UNIQUE KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4  ROW_FORMAT=DYNAMIC COMMENT='系统配置';

-- ----------------------------
-- Records of dask_config
-- ----------------------------
INSERT INTO `dask_config` VALUES (1,'site_name','1','站点名称','请填写站点名称','text','','null',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (2,'site_logo','1','网站logo','请上传网站LOGO','image','','null',2,NULL,1,0);
INSERT INTO `dask_config` VALUES (3,'site_close','1','关闭站点','开启或关闭站点','radio','N','{\"Y\":\"关闭\",\"N\":\"开启\"}',2,NULL,1,0);
INSERT INTO `dask_config` VALUES (4,'icp','1','备案号','输入网站备案号','text','','null',3,NULL,1,0);
INSERT INTO `dask_config` VALUES (5,'email_enable','3','邮箱功能','','radio','Y','{\"Y\":\"开启\",\"N\":\"关闭\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (6,'email_host','3','SMTP地址','设置SMTP服务地址,如smtp.mail.com','text','','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (7,'email_username','3','邮箱用户名','邮箱用户名,如admin@admin.com','text','','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (8,'email_password','3','邮箱密码','邮箱密码','password','','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (9,'email_secure','3','安全链接(SSL)','验证模式','radio','ssl','{\"tls\":\"否\",\"ssl\":\"是\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (10,'upload_file_size','4','文件限制','单位：KB，0表示不限制上传大小,直接填写数字,不需要带单位','text','1024','null',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (11,'upload_file_ext','4','文件格式','多个格式请用英文逗号（,）隔开 ','text','rar,zip,avi,rmvb,3gp,flv,mp3,mp4,txt,doc,xls,ppt,pdf,xls,docx,xlsx,doc','null',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (12,'upload_image_size','4','图片限制','单位：KB，0表示不限制上传大小,直接填写数字,不需要带单位','text','2048','null',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (13,'upload_image_ext','4','图片格式','多个格式请用英文逗号（,）隔开 ','text','jpg,png,gif,jpeg,webp','null',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (15,'seo_title','1','站点标题','站点标题','text','','null',1,NULL,1,0);
INSERT INTO `dask_config` VALUES (16,'seo_keywords','1','站点关键词','站点关键词','textarea','','null',1,NULL,1,0);
INSERT INTO `dask_config` VALUES (17,'seo_description','1','站点描述','站点描述','textarea','','null',1,NULL,1,0);
INSERT INTO `dask_config` VALUES (19,'register_valid_type','5','验证类型','新用户注册验证类型','radio','email','{\"email\":\"邮箱验证\",\"admin\":\"后台审核\",\"N\":\"不验证\"}',50,NULL,1,0);
INSERT INTO `dask_config` VALUES (20,'register_type','5','注册类型','注册类型','radio','email','{\"email\":\"邮箱注册\",\"mobile\":\"手机注册\",\"all\":\"手机+邮箱注册\",\"close\":\"关闭注册\"}',50,NULL,1,0);
INSERT INTO `dask_config` VALUES (21,'password_min_length','5','密码最小长度','密码最小长度','number','6','[]',50,NULL,1,0);
INSERT INTO `dask_config` VALUES (22,'password_max_length','5','密码最大长度','密码最大长度','number','30','[]',50,NULL,1,0);
INSERT INTO `dask_config` VALUES (23,'password_type','5','密码类型','密码类型，密码必须是选择的类型才可使用，不选则不限制','checkbox','','{\"number\":\"数字\",\"special\":\"特殊字符\",\"letter\":\"字母\"}',50,NULL,1,0);
INSERT INTO `dask_config` VALUES (24,'username_min_length','5','用户名最小长度','用户名最小长度','number','0','[]',50,NULL,1,0);
INSERT INTO `dask_config` VALUES (25,'username_max_length','5','用户名最大长度','用户名最大长度','number','15','[]',50,NULL,1,0);
INSERT INTO `dask_config` VALUES (26,'errors_exceeds_limit_password','5','密码最大重试次数','密码最大重试次数','number','3','[]',50,NULL,1,0);
INSERT INTO `dask_config` VALUES (27,'password_error_limit_time','5','密码错误限制时长','密码错误限制时长，单位（分钟）','number','10','[]',50,NULL,1,0);
INSERT INTO `dask_config` VALUES (28,'register_agreement','5','注册协议','','textarea','当您申请用户时，表示您已经同意遵守本规章。\r\n欢迎您加入本站点参与交流和讨论，本站点为社区，为维护网上公共秩序和社会稳定，请您自觉遵守以下条款：\r\n\r\n一、不得利用本站危害国家安全、泄露国家秘密，不得侵犯国家社会集体的和公民的合法权益，不得利用本站制作、复制和传播下列信息：\r\n　（一）煽动抗拒、破坏宪法和法律、行政法规实施的；\r\n　（二）煽动颠覆国家政权，推翻社会主义制度的；\r\n　（三）煽动分裂国家、破坏国家统一的；\r\n　（四）煽动民族仇恨、民族歧视，破坏民族团结的；\r\n　（五）捏造或者歪曲事实，散布谣言，扰乱社会秩序的；\r\n　（六）宣扬封建迷信、淫秽、色情、赌博、暴力、凶杀、恐怖、教唆犯罪的；\r\n　（七）公然侮辱他人或者捏造事实诽谤他人的，或者进行其他恶意攻击的；\r\n　（八）损害国家机关信誉的；\r\n　（九）其他违反宪法和法律行政法规的；\r\n　（十）进行商业广告行为的。\r\n\r\n二、互相尊重，对自己的言论和行为负责。\r\n三、禁止在申请用户时使用相关本站的词汇，或是带有侮辱、毁谤、造谣类的或是有其含义的各种语言进行注册用户，否则我们会将其删除。\r\n四、禁止以任何方式对本站进行各种破坏行为。\r\n五、如果您有违反国家相关法律法规的行为，本站概不负责，您的登录信息均被记录无疑，必要时，我们会向相关的国家管理部门提供此类信息。','[]',50,NULL,1,0);
INSERT INTO `dask_config` VALUES (29,'enable_column','7','启用专栏','','radio','Y','{\"Y\":\"启用\",\"N\":\"禁用\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (30,'enable_category','7','启用分类','','radio','Y','{\"Y\":\"启用\",\"N\":\"禁用\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (34,'popular_gravity','6','热门-刷新速度','内容变得不再热门的速度，热门刷新速度越大，一个内容刷新的就越快','number','1.5','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (35,'popular_agree_ratio','6','热门-赞同比例','计算热门时点赞数所占比例','number','2','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (36,'popular_against_ratio','6','热门-反对比例','计算热门时反对数所占比例','number','1','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (37,'popular_view_ratio','6','热门-浏览比例','计算热门时浏览数所占比例','number','2','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (38,'popular_comment_ratio','6','热门-评论比例','计算热门时评论/回复数所占比例','number','2','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (39,'popular_quality_init_value','6','热门-初始质量','内容初始质量','number','1','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (46,'cdn_url','4','资源URL','默认为空，代表当前服务器，其他服务器填写域名或IP,不带/','text','','null',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (47,'upload_type','4','上传方式','默认为TP上传','radio','tp','{\"tp\":\"默认方式\",\"big\":\"分片上传\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (48,'footer_html','1','公共底部代码','','textarea','','null',53,NULL,1,0);
INSERT INTO `dask_config` VALUES (49,'header_html','1','公共头部代码','','textarea','','null',52,NULL,1,0);
INSERT INTO `dask_config` VALUES (50,'online_check','5','在线检测','在线检测用户在线状态','radio','N','{\"N\":\"关闭\",\"Y\":\"开启\"}',50,NULL,1,0);
INSERT INTO `dask_config` VALUES (51,'online_check_time','5','检测间隔','在线检测用户在线状态时间间隔，单位（分钟）','number','5','null',50,NULL,1,0);
INSERT INTO `dask_config` VALUES (55,'sensitive_words','7','敏感词列表','内容中如出现敏感词将进入审核，支持普通字符串和正则表达式，每行一个。正则表达式请以 <code>{</code> 开始、以 <code>}</code> 结束，且需符合 <a href=\"http://cn2.php.net/manual/zh/pcre.pattern.php\" target=\"_blank\">PCRE 模式</a>，如 <code>{/敏s*感s*词/i}</code>。','textarea','','null',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (57,'email_port','3','邮箱端口','留空时默认服务器端口为 25，使用 SSL 协议默认端口为 465，详细参数请询问邮箱服务商','text','465','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (58,'email_from','3','显示来源','请保持和邮箱用户名同一主域下','text','','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (59,'email_show_name','3','来源名称','显示来源名称','text','','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (62,'search_handle','10','搜索引擎','搜索引擎选择,未安装其他搜索引擎时默认为本地数据库搜索','radio','regexp','{\"regexp\":\"系统默认\",\"ElasticSearch\":\"ElasticSearch\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (63,'remove_xss','7','启用XSS过滤','启用XSS过滤可能会导致某些内容被过滤','radio','Y','{\"Y\":\"启用\",\"N\":\"禁用\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (64,'date_friendly_enable','7','时间格式','','radio','friendly','{\"friendly\":\"友好格式\",\"normal\":\"标准格式\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (65,'attach_enable','9','发布内容支持附件','发布内容(问题、文章)时是否支持附件','radio','Y','{\"Y\":\"是\",\"N\":\"否\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (66,'topic_enable','9','发布内容必选话题','发布内容(问题、文章)时是否必选话题','radio','Y','{\"Y\":\"是\",\"N\":\"否\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (67,'download_local_enable','4','本地化图片','远程图片本地化开关','radio','Y','{\"Y\":\"开启\",\"N\":\"关闭\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (68,'record_admin_log','7','记录管理员日志','记录管理员后台操作日志','radio','Y','{\"Y\":\"开启\",\"N\":\"关闭\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (69,'open_content_enable','9','默认展开内容','默认是否显示全部内容','radio','Y','{\"Y\":\"开启\",\"N\":\"关闭\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (70,'cache_explore_time','8','首页缓存周期','首页数据缓存周期0代表不缓存，单位：分钟','number','0','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (71,'cache_list_time','8','列表缓存周期','问题/文章列表数据缓存周期0代表不缓存，单位：分钟','number','0','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (72,'contents_per_page','9','内容显示条数','通用内容列表显示条数','number','20','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (73,'publish_content_verify_time','9','发文验证周期','xx分钟内连续发文(问题/文章/回答)开启填写验证码，0代表不验证；单位：分钟','number','0','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (74,'publish_content_verify_num','9','发文验证','连续发文多少次开启填写验证码','number','5','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (75,'notify_type','10','通知类型','系统通知类型','checkbox','site,email','{\"site\":\"站内通知\",\"email\":\"邮件通知\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (76,'enable_frontend_captcha','7','启用前台验证码','是否启用前台登录验证码','radio','N','{\"Y\":\"启用\",\"N\":\"禁用\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (77,'enable_backend_captcha','7','启用后台验证码','是否启用后台登录验证码','radio','N','{\"Y\":\"启用\",\"N\":\"禁用\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (79,'cache_relation_list_time','8','相关数据缓存','问题/文章详情页相关数据/推荐内容缓存周期0代表不缓存，单位：分钟','number','0','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (80,'unique_login','5','单一登录','只允许同一个用户相同ip相同浏览器登录','radio','N','{\"N\":\"关闭\",\"Y\":\"开启\"}',50,NULL,1,0);
INSERT INTO `dask_config` VALUES (81,'auto_save_draft','7','自动保存草稿','是否开启自动保存草稿','radio','Y','{\"N\":\"关闭\",\"Y\":\"开启\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (82,'auto_save_draft_time','7','自动保存时间','草稿自动保存间隔时间,单位：秒','number','30','null',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (83,'search_engine_user','10','搜索引擎用户名','搜索引擎用户名,没有请留空','text','','null',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (84,'search_engine_password','10','搜索引擎密码','搜索引擎密码,没有请留空','text','','null',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (85,'search_engine_host','10','搜索引擎地址','搜索引擎服务地址,请填写完整的服务器地址，包括端口等,如es搜索引擎需要配置','text','','null',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (86,'search_engine_app','10','搜索引擎项目名称','搜索引擎项目名称,如es搜索引擎需要配置','text','','null',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (87,'answer_unique','9','用户对每个问题的回复限制','用户对每个问题的回复限制','radio','Y','{\"Y\":\"只允许回复一次\",\"N\":\"不限制\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (89,'invitation_expire_time','5','注册邀请码有效期','注册邀请时邀请码过期时间,单位:小时','number','72','[]',50,NULL,1,0);
INSERT INTO `dask_config` VALUES (91,'show_answer_user_ip','9','显示回答用户IP地址','是否显示回答用户IP地址','radio','Y','{\"Y\":\"显示\",\"N\":\"不显示\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (92,'invite_register_enable','5','开启邀请注册','是否开启邀请注册','radio','N','{\"Y\":\"开启\",\"N\":\"关闭\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (93,'enable_anonymous','7','是否允许匿名','是否允许匿名提问/回答问题','radio','Y','{\"Y\":\"允许\",\"N\":\"不允许\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (95,'remember_login_time','5','记住登录有效期','记住登录有效期，单位：天','number','7','null',50,NULL,1,0);
INSERT INTO `dask_config` VALUES (96,'email_test','3','发送测试邮件','','html','','',51,'',1,0);
INSERT INTO `dask_config` VALUES (97,'report_bug_email','1','BUG反馈接收提醒邮箱','BUG反馈接收提醒邮箱','text','','null',51,'',1,0);
INSERT INTO `dask_config` VALUES (98,'max_topic_select','9','话题最多选择数量','话题最多选择数量','number','4','null',51,'',1,0);
INSERT INTO `dask_config` VALUES (99,'cache_type','8','缓存方式','选择缓存方式,当选择非文件缓存时，请点击下方“检查并配置缓存链接”','radio','file','{\"file\":\"文件缓存\",\"redis\":\"Redis缓存\",\"memcached\":\"Memcached缓存\",\"memcache\":\"Memcache缓存\"}',51,'',1,0);
INSERT INTO `dask_config` VALUES (101,'cache_host','8','链接地址','链接地址默认127.0.0.1','hidden','127.0.0.1','null',51,'',1,0);
INSERT INTO `dask_config` VALUES (102,'cache_port','8','链接端口','链接端口','hidden','11211','null',51,'',1,0);
INSERT INTO `dask_config` VALUES (103,'cache_password','8','链接密码','链接密码','hidden','','null',51,'',1,0);
INSERT INTO `dask_config` VALUES (106,'pjax_enable','1','是否启用pjax','网站是否启用pjax请求','radio','Y','{\"Y\":\"启用\",\"N\":\"不启用\"}',50,'',1,0);
INSERT INTO `dask_config` VALUES (108,'answer_sort_type','9','默认回答排序','选择默认回答排序方式','radio','new','{\"new\":\"最新排序\",\"hot\":\"热门排序\",\"publish\":\"只看楼主\",\"focus\":\"关注的人\"}',50,'',1,0);
INSERT INTO `dask_config` VALUES (110,'auto_question_lock_day','9','自动锁定问题天数','自动锁定问题天数 0 代表不自动锁定，单位：天','number','60','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (111,'auto_set_best_answer_day','9','自动设定最佳回答天数','自动设定最佳回答天数 0 代表不自动设定，单位：天','number','7','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (112,'best_answer_min_count','9','自动设定最佳回答时该问题最小回答数','自动设定最佳回答时该问题最小回答数','number','1','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (113,'enable_multilingual','7','启用多语言','启用前台多语言','radio','Y','{\"Y\":\"启用\",\"N\":\"不启用\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (114,'visitor_view_answer_count','5','游客可浏览回答数量','游客可浏览回答数量，0代表不限制','number','0','null',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (115,'uninterested_fold','9','“不感兴趣”数量达到多少个时自动折叠回复','“不感兴趣”数量达到多少个时自动折叠回复','number','10','null',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (116,'remember_login_enable','5','是否启用记住登录状态','是否启用记住登录状态,为了防止用户通过保存cookie实现自动登录，建议不启用','radio','Y','{\"Y\":\"启用\",\"N\":\"不启用\"}',50,NULL,1,0);
INSERT INTO `dask_config` VALUES (117,'default_language','7','默认站点语言','站点默认使用语言','radio','zh-cn','{\"zh-cn\":\"中文\",\"en-us\":\"英文\",\"zh-tw\":\"繁體中文\",\"es-es\":\"西班牙语\"}',50,'',0,1);
INSERT INTO `dask_config` VALUES (118,'best_agree_min_count','9','自动设定最佳回答时该回答至少获赞数','自动设定最佳回答时该回答至少获赞数','number','3','null',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (119,'content_popular_value_show','9','热度值大于多少参与热度排序','热度值达到多少参与热度排序,默认大于0即参与热门排序','number','0','null',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (120,'upload_image_thumb_enable','4','是否启用图片压缩','是否启用图片压缩，PNG图片压缩会失去透明效果','radio','Y','{\"Y\":\"启用\",\"N\":\"不启用\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (121,'upload_image_thumb_percent','4','上传图片默认压缩比例','默认为0.7','text','0.7','null',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (122,'db_version','1','数据库版本','','hidden','100','null',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (124,'sub_dir','1','二级目录地址','二级目录安装时path地址，不包含域名如二级目录域名为http://www.xxx.com/sub,只需填写/sub/即可','text','/','null',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (125,'api_doc_public','7','公开展示API文档','开启后所有访客可访问 /api-docs；关闭后仅管理员可预览','radio','Y','{\"Y\":\"公开\",\"N\":\"仅管理员\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (126,'system_version','1','系统版本','当前代码与数据库结构版本','hidden','1.0.0','null',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (127,'site_logo_text','1','后台文字Logo','无图片Logo时展示，最多显示5个字；留空则使用站点名称','text','','null',3,NULL,1,0);
INSERT INTO `dask_config` VALUES (128,'site_slogan','1','品牌中文文案','站点页脚展示','text','','null',1,NULL,1,0);
INSERT INTO `dask_config` VALUES (154,'site_tagline','1','站点短标语','题图副标题、侧栏引导与静态页描述共用；留空则使用代码默认文案','text','','null',1,NULL,1,0);
INSERT INTO `dask_config` VALUES (302,'t_hm_search_title_text','12','题图主标题','留空时使用“站点名称 - 当前频道”','text','','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (303,'t_hm_search_min_text','12','题图副标题','留空时使用后台“站点短标语”','textarea','','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (304,'t_hm_sidebar_show_items','12','侧边栏项目','侧边栏显示的项目','checkbox','write_nav,announce,focus_topic,column,hot_topic,hot_users,side_footer','{\"write_nav\":\"快速发起\",\"announce\":\"网站公告\",\"focus_topic\":\"关注话题\",\"column\":\"热门专栏\",\"hot_topic\":\"热门话题\",\"hot_users\":\"热门用户\",\"diy_content\":\"自定义内容\",\"side_footer\":\"边栏页脚\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (305,'t_hm_sidebar_diy_content','12','自定义内容','侧边栏自定义内容,支持富文本','editor','&lt;p class=&quot;p-3 bg-info mt-2&quot;&gt;这是自定义内容&lt;/p&gt;','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (306,'t_hm_links_show_type','12','友情链接展示方式','侧边栏自定义内容,支持富文本','radio','text','{\"text\":\"文字链接\",\"image\":\"图片链接\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (307,'t_qs_sidebar_show_items','12','侧边栏项目','侧边栏显示的项目','checkbox','write_nav,announce,focus_topic,column,hot_topic,hot_users','{\"write_nav\":\"快速发起\",\"announce\":\"网站公告\",\"focus_topic\":\"关注话题\",\"column\":\"热门专栏\",\"hot_topic\":\"热门话题\",\"hot_users\":\"热门用户\",\"diy_content\":\"自定义内容\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (308,'t_qs_sidebar_diy_content','12','自定义内容','侧边栏自定义内容,支持富文本','editor','&lt;p class=&quot;p-3 bg-info mt-2&quot;&gt;这是自定义内容&lt;/p&gt;','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (309,'t_art_sidebar_show_items','12','侧边栏项目','侧边栏显示的项目','checkbox','write_nav,announce,focus_topic,column,hot_topic,hot_users','{\"write_nav\":\"快速发起\",\"announce\":\"网站公告\",\"focus_topic\":\"关注话题\",\"column\":\"热门专栏\",\"hot_topic\":\"热门话题\",\"hot_users\":\"热门用户\",\"diy_content\":\"自定义内容\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (310,'t_art_sidebar_diy_content','12','自定义内容','侧边栏自定义内容,支持富文本','editor','&lt;p class=&quot;p-3 bg-info mt-2&quot;&gt;这是自定义内容&lt;/p&gt;','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (312,'t_col_navbar_text','12','标题文字','','text','','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (314,'t_cm_list_show_image','12','列表显示图片','','radio','Y','{\"N\":\"不显示\",\"Y\":\"显示\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (315,'t_cm_fixed_navbar','12','固定导航','','radio','Y','{\"N\":\"不固定\",\"Y\":\"固定\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (316,'t_cm_login_type','12','登录方式','','radio','page','{\"page\":\"新页面\",\"dialog\":\"弹窗\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (317,'t_cm_enable_mathjax','12','启用Mathjax支持','','radio','Y','{\"N\":\"不支持\",\"Y\":\"支持\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (318,'t_cm_filter_grey','12','网站置灰','','radio','N','{\"N\":\"不启用\",\"Y\":\"启用\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (319,'t_qd_sidebar_show_relation_question','12','侧边栏显示相关问题','侧边栏显示相关问题','radio','Y','{\"N\":\"不显示\",\"Y\":\"显示\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (320,'t_qd_sidebar_show_recommend_post','12','侧边栏显示推荐内容','侧边栏显示推荐内容','radio','Y','{\"N\":\"不显示\",\"Y\":\"显示\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (321,'t_qd_sidebar_show_items','12','侧边栏项目','侧边栏显示的项目','checkbox','write_nav,announce,focus_topic,column,hot_topic,hot_users,diy_content','{\"write_nav\":\"快速发起\",\"announce\":\"网站公告\",\"focus_topic\":\"关注话题\",\"column\":\"热门专栏\",\"hot_topic\":\"热门话题\",\"hot_users\":\"热门用户\",\"diy_content\":\"自定义内容\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (322,'t_qd_sidebar_diy_content','12','自定义内容','侧边栏自定义内容,支持富文本','editor','&lt;p class=&quot;p-3 bg-info mt-2&quot;&gt;这是自定义内容&lt;/p&gt;','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (323,'t_ad_sidebar_show_relation_article','12','侧边栏显示相关文章','侧边栏显示相关文章','radio','Y','{\"N\":\"不显示\",\"Y\":\"显示\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (324,'t_ad_sidebar_show_recommend_post','12','侧边栏显示推荐内容','侧边栏显示推荐内容','radio','Y','{\"N\":\"不显示\",\"Y\":\"显示\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (325,'t_ad_sidebar_show_items','12','侧边栏项目','侧边栏显示的项目','checkbox','write_nav,announce,focus_topic,column,hot_topic,hot_users,diy_content','{\"write_nav\":\"快速发起\",\"announce\":\"网站公告\",\"focus_topic\":\"关注话题\",\"column\":\"热门专栏\",\"hot_topic\":\"热门话题\",\"hot_users\":\"热门用户\",\"diy_content\":\"自定义内容\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (326,'t_ad_sidebar_diy_content','12','自定义内容','侧边栏自定义内容,支持富文本','editor','&lt;p class=&quot;p-3 bg-info mt-2&quot;&gt;这是自定义内容&lt;/p&gt;','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (327,'t_mb_index_hot_banner_enable','12','是否启用首页热门','是否启用首页热门','radio','Y','{\"N\":\"不开启\",\"Y\":\"开启\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (329,'t_mb_index_hot_user_url','12','首页热门用户URL','首页热门用户URL','text','','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (331,'t_mb_index_hot_topic_url','12','首页热门话题URL','首页热门用户URL','text','','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (400,'editor_mode_default','13','默认编辑模式','桌面端默认模式；手机端自动使用即时渲染','radio','wysiwyg','{\"wysiwyg\":\"所见即所得\",\"ir\":\"即时渲染\",\"sv\":\"分屏预览\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (401,'editor_upload_max_image','13','图片上传上限','单位：MB；最终以“上传配置”中的服务端限制为准','number','10','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (402,'editor_upload_max_video','13','视频上传上限','单位：MB；最终以“上传配置”中的服务端限制为准','number','50','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (403,'editor_code_highlight','13','编辑器代码高亮','仅影响编辑器内预览；正文前台高亮由站点 highlight.js 统一处理','radio','Y','{\"Y\":\"开启\",\"N\":\"关闭\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (404,'editor_video_enable','13','允许视频上传','关闭后工具栏不再显示上传按钮中的视频类型','radio','Y','{\"Y\":\"开启\",\"N\":\"关闭\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (405,'editor_toolbar_preset','13','工具栏预设','full 为完整工具栏，simple 仅保留常用排版与上传','radio','full','{\"full\":\"完整\",\"simple\":\"简洁\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (406,'editor_content_theme','13','正文预览主题','auto 跟随站点深浅色；light/dark/ant-design 为固定主题','radio','auto','{\"auto\":\"跟随站点\",\"light\":\"浅色\",\"dark\":\"深色\",\"ant-design\":\"Ant Design\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (407,'editor_image_enable','13','允许图片上传','关闭后上传按钮不再接受图片类型','radio','Y','{\"Y\":\"开启\",\"N\":\"关闭\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (408,'editor_upload_enable','13','允许编辑器上传','关闭后工具栏不显示上传按钮','radio','Y','{\"Y\":\"开启\",\"N\":\"关闭\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (409,'editor_float_toolbar_enable','13','编辑态浮层','开启后显示块级上移/下移/删除浮层；默认关闭','radio','N','{\"Y\":\"开启\",\"N\":\"关闭\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (410,'editor_cache_enable','13','本地草稿缓存','开启后使用浏览器本地缓存，可能与服务端草稿叠加','radio','N','{\"Y\":\"开启\",\"N\":\"关闭\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (411,'editor_counter_enable','13','显示字数统计','在工具栏右侧显示编辑器字数','radio','Y','{\"Y\":\"开启\",\"N\":\"关闭\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (413,'editor_upload_timeout','13','上传超时时间','单位：毫秒','number','30000','[]',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (414,'third_enable','11','启用的登录方式','勾选后前台登录页显示对应入口；回调地址由系统按当前域名自动组装为 /ThirdAuth/callback.html?platform=平台','checkbox','qq,wechat,weibo,github,google,twitter','{\"qq\":\"QQ\",\"wechat\":\"微信\",\"weibo\":\"微博\",\"github\":\"GitHub\",\"google\":\"Google\",\"twitter\":\"Twitter/X\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (416,'third_qq_app_id','11','QQ AppID','QQ互联申请的 AppID','text','','',10,NULL,1,0);
INSERT INTO `dask_config` VALUES (417,'third_qq_app_secret','11','QQ AppKey','QQ互联申请的 AppKey','text','','',11,NULL,1,0);
INSERT INTO `dask_config` VALUES (418,'third_qq_scope','11','QQ 授权范围','回调地址：/ThirdAuth/callback.html?platform=qq（按实际域名补全）','text','get_user_info','',12,NULL,1,0);
INSERT INTO `dask_config` VALUES (419,'third_wechat_app_id','11','微信 AppID','微信开放平台申请的 AppID','text','','',20,NULL,1,0);
INSERT INTO `dask_config` VALUES (420,'third_wechat_app_secret','11','微信 AppSecret','微信开放平台申请的 AppSecret','text','','',21,NULL,1,0);
INSERT INTO `dask_config` VALUES (421,'third_wechat_scope','11','微信授权范围','回调地址：/ThirdAuth/callback.html?platform=wechat（按实际域名补全）','text','snsapi_userinfo','',22,NULL,1,0);
INSERT INTO `dask_config` VALUES (422,'third_weibo_app_id','11','微博 AppKey','微博开放平台申请的 AppKey','text','','',30,NULL,1,0);
INSERT INTO `dask_config` VALUES (423,'third_weibo_app_secret','11','微博 AppSecret','微博开放平台申请的 AppSecret','text','','',31,NULL,1,0);
INSERT INTO `dask_config` VALUES (424,'third_weibo_scope','11','微博授权范围','回调地址：/ThirdAuth/callback.html?platform=weibo（按实际域名补全）','text','','',32,NULL,1,0);
INSERT INTO `dask_config` VALUES (425,'third_github_app_id','11','GitHub Client ID','GitHub OAuth App 的 Client ID','text','','',40,NULL,1,0);
INSERT INTO `dask_config` VALUES (426,'third_github_app_secret','11','GitHub Client Secret','GitHub OAuth App 的 Client Secret','text','','',41,NULL,1,0);
INSERT INTO `dask_config` VALUES (427,'third_github_scope','11','GitHub 授权范围','回调地址：/ThirdAuth/callback.html?platform=github（按实际域名补全）','text','read:user user:email','',42,NULL,1,0);
INSERT INTO `dask_config` VALUES (428,'third_google_app_id','11','Google Client ID','Google OAuth 客户端的 Client ID','text','','',50,NULL,1,0);
INSERT INTO `dask_config` VALUES (429,'third_google_app_secret','11','Google Client Secret','Google OAuth 客户端的 Client Secret','text','','',51,NULL,1,0);
INSERT INTO `dask_config` VALUES (430,'third_google_scope','11','Google 授权范围','回调地址：/ThirdAuth/callback.html?platform=google（按实际域名补全）','text','openid email profile','',52,NULL,1,0);
INSERT INTO `dask_config` VALUES (431,'third_twitter_app_id','11','Twitter/X Client ID','X 开发者平台 OAuth 2.0 的 Client ID','text','','',60,NULL,1,0);
INSERT INTO `dask_config` VALUES (432,'third_twitter_app_secret','11','Twitter/X Client Secret','X 开发者平台 OAuth 2.0 的 Client Secret','text','','',61,NULL,1,0);
INSERT INTO `dask_config` VALUES (433,'third_twitter_scope','11','Twitter/X 授权范围','回调地址：/ThirdAuth/callback.html?platform=twitter（按实际域名补全）','text','tweet.read users.read offline.access','',62,NULL,1,0);
INSERT INTO `dask_config` VALUES (434,'watermark_enable','4','上传图片文字水印','开启后所有上传图片自动叠加文字水印，GIF 动画除外','radio','N','{\"Y\":\"启用\",\"N\":\"不启用\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (435,'watermark_text','4','水印文字','可自定义；留空则不叠加。超长文字自动折行，最多 3 行','text','DudaCMS','null',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (500,'sms_enable','14','短信服务','N 为关闭；ali 阿里云短信；tencent 腾讯云短信','radio','N','{\"N\":\"不启用\",\"ali\":\"阿里云短信\",\"tencent\":\"腾讯云短信\"}',0,NULL,1,0);
INSERT INTO `dask_config` VALUES (501,'sms_ali_access_key_id','14','阿里云 AccessKey ID','仅在使用阿里云短信时填写','text','','[]',1,NULL,1,0);
INSERT INTO `dask_config` VALUES (502,'sms_ali_access_key_secret','14','阿里云 AccessKey Secret','仅在使用阿里云短信时填写','password','','[]',2,NULL,1,0);
INSERT INTO `dask_config` VALUES (503,'sms_ali_sign_name','14','阿里云短信签名','控制台签名管理页的签名名称','text','','[]',3,NULL,1,0);
INSERT INTO `dask_config` VALUES (504,'sms_ali_template_code','14','阿里云短信模板 CODE','控制台模板管理页的模板 CODE','text','','[]',4,NULL,1,0);
INSERT INTO `dask_config` VALUES (505,'sms_ali_endpoint','14','阿里云节点域名','仅允许 *.aliyuncs.com，非法值自动回退默认节点','text','dysmsapi.aliyuncs.com','[]',5,NULL,1,0);
INSERT INTO `dask_config` VALUES (506,'sms_tencent_app_id','14','腾讯云 SmsSdkAppid','仅在使用腾讯云短信时填写','text','','[]',10,NULL,1,0);
INSERT INTO `dask_config` VALUES (507,'sms_tencent_secret_id','14','腾讯云 SecretId','仅在使用腾讯云短信时填写','text','','[]',11,NULL,1,0);
INSERT INTO `dask_config` VALUES (508,'sms_tencent_access_key_secret','14','腾讯云 SecretKey','仅在使用腾讯云短信时填写','password','','[]',12,NULL,1,0);
INSERT INTO `dask_config` VALUES (509,'sms_tencent_sign_name','14','腾讯云短信签名','控制台签名管理页的签名名称','text','','[]',13,NULL,1,0);
INSERT INTO `dask_config` VALUES (510,'sms_tencent_template_code','14','腾讯云短信模板 ID','控制台模板管理页的模板 ID','text','','[]',14,NULL,1,0);
INSERT INTO `dask_config` VALUES (511,'credit_unit','6','成长值单位','前台成长值单位','text','成长值','[]',0,NULL,1,0);
-- ----------------------------
-- Table structure for dask_menu_rule
-- ----------------------------
DROP TABLE IF EXISTS `dask_menu_rule`;
CREATE TABLE `dask_menu_rule`  (
      `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
      `pid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父ID',
      `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '控制器/方法',
      `title` char(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
      `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1站内链接2站外链接',
      `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '菜单状态',
      `sort` mediumint(8) NOT NULL DEFAULT 0 COMMENT '排序',
      `auth_open` tinyint(2) NULL DEFAULT 1,
      `icon` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '菜单图标',
      `param` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '参数',
      `is_home` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否为默认首页',
      `group` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '权限分组',
      PRIMARY KEY (`id`) USING BTREE,
      UNIQUE INDEX `name`(`name`,`param`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC  COMMENT = '菜单表';

-- ----------------------------
-- Records of dask_menu_rule
-- ----------------------------
INSERT INTO `dask_menu_rule` VALUES (1,0,'index/index','首页',1,1,50,0,'','',1,'nav');
INSERT INTO `dask_menu_rule` VALUES (2,0,'question/index','问答',1,1,50,0,'','',0,'nav');
INSERT INTO `dask_menu_rule` VALUES (3,0,'article/index','文章',1,1,50,0,'','',0,'nav');
INSERT INTO `dask_menu_rule` VALUES (4,0,'column/index','专栏',1,0,50,0,'','',0,'nav');
INSERT INTO `dask_menu_rule` VALUES (5,0,'people/lists','大咖',1,0,50,0,'','',0,'nav');
INSERT INTO `dask_menu_rule` VALUES (6,0,'topic/index','话题',1,1,50,0,'','',0,'nav');
INSERT INTO `dask_menu_rule` VALUES (7,0,'page/index','关于我们',1,1,50,0,'','url_name=about',0,'footer');
INSERT INTO `dask_menu_rule` VALUES (8,0,'page/index','社区规范',1,1,50,0,'','url_name=rule',0,'footer');
INSERT INTO `dask_menu_rule` VALUES (9,0,'help/index','帮助',1,0,50,0,'','',0,'nav');
INSERT INTO `dask_menu_rule` VALUES (10,0,'feature/index','专题',1,0,50,0,'','',0,'nav');

DROP TABLE IF EXISTS `dask_users`;
CREATE TABLE `dask_users` (
     `uid` int(11) NOT NULL AUTO_INCREMENT,
     `nick_name` varchar(50) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '昵称',
     `user_name` varchar(50) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '用户名',
     `password` varchar(64) DEFAULT NULL COMMENT '用户密码',
     `salt` varchar(32) DEFAULT NULL COMMENT '用户附加混淆码',
     `email` varchar(255) NOT NULL DEFAULT '' COMMENT '邮箱',
     `mobile` char(16) DEFAULT NULL COMMENT '手机号',
     `sex` tinyint(1) unsigned DEFAULT '0' COMMENT '0保密1男2女',
     `is_first_login` tinyint(1) unsigned DEFAULT '0' COMMENT '是否首次登陆',
     `inbox_unread` int(10) unsigned DEFAULT '0' COMMENT '未读私信',
     `notify_unread` int(10) unsigned DEFAULT '0' COMMENT '未读通知',
     `fans_count` int(10) unsigned DEFAULT '0' COMMENT '粉丝数量',
     `friend_count` int(10) unsigned DEFAULT '0' COMMENT '关注数量',
     `available_invite_count` int(10) unsigned DEFAULT '0' COMMENT '可用邀请数量',
     `is_valid_email` tinyint(1) unsigned DEFAULT '0' COMMENT '是否验证邮箱',
     `is_valid_mobile` tinyint(1) unsigned DEFAULT '0' COMMENT '是否验证手机号',
     `credit` int(10) DEFAULT '0' COMMENT '统一成长值',
     `avatar` varchar(255) DEFAULT NULL COMMENT '用户头像',
     `signature` varchar(255) DEFAULT NULL COMMENT '签名',
     `user_banner` varchar(255) DEFAULT NULL COMMENT '用户封面',
     `verified` varchar(255) DEFAULT NULL COMMENT '认证类型',
     `group_id` int(10) unsigned DEFAULT '0' COMMENT '系统分组ID',
     `credit_group_id` int(10) unsigned DEFAULT '0' COMMENT '成长等级组ID',
     `last_login_time` int(10) unsigned DEFAULT '0' COMMENT '最后登录时间',
     `last_login_ip` varchar(50) DEFAULT '' COMMENT '最后登录IP',
     `reg_ip` varchar(50) DEFAULT NULL COMMENT '注册IP',
     `birthday` BIGINT DEFAULT NULL COMMENT '生日',
     `url_token` varchar(255) DEFAULT NULL COMMENT '自定义URL',
     `url_token_modified` tinyint(1) unsigned DEFAULT '0' COMMENT '是否可修改个性域名',
     `views_count` int(255) unsigned DEFAULT '0' COMMENT '个人主页浏览量',
     `agree_count` int(10) unsigned DEFAULT '0' COMMENT '赞同数量',
     `question_count` int(255) unsigned DEFAULT '0' COMMENT '问题数量',
     `answer_count` int(255) unsigned DEFAULT '0' COMMENT '回答数量',
     `article_count` int(10) unsigned DEFAULT '0' COMMENT '发布文章数量',
     `draft_count` int(10) DEFAULT NULL COMMENT '草稿数量',
     `topic_focus_count` int(10) NOT NULL DEFAULT '0' COMMENT '关注话题数量',
     `column_count` int(10) NOT NULL DEFAULT '0' COMMENT '专栏数量',
     `extend` text DEFAULT NULL COMMENT '拓展信息',
     `forbidden_ip` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1-已封禁ip',
     `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '用户状态0已删除1正常2待审核3已封禁',
     `client` varchar(32) NOT NULL DEFAULT 'pc',
     `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
     `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '修改时间',
     PRIMARY KEY (`uid`) USING BTREE,
     UNIQUE KEY `user_name` (`user_name`) USING BTREE,
     UNIQUE KEY `url_token` (`url_token`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='用户表';
-- ----------------------------
-- Table structure for dask_users_extends
-- ----------------------------
DROP TABLE IF EXISTS `dask_users_extends`;
CREATE TABLE `dask_users_extends`  (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `uid` int(8) UNSIGNED NOT NULL,
      `notify_setting` text COMMENT '通知设置',
      `inbox_setting` varchar(255) DEFAULT NULL COMMENT '私信设置',
      PRIMARY KEY (`id`) USING BTREE,
      UNIQUE INDEX `uid`(`uid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COMMENT = '用户信息拓展表' ROW_FORMAT = DYNAMIC;

DROP TABLE IF EXISTS `dask_credit_group`;
CREATE TABLE `dask_credit_group`  (
     `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
     `title` char(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '组名称',
     `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
     `permission` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '分组权限',
     `group_icon` varchar(255) DEFAULT NULL COMMENT '用户组图标',
     `min_credit` int unsigned DEFAULT '0' COMMENT '最小成长值',
     `max_credit` int unsigned DEFAULT '0' COMMENT '最大成长值（不含）',
     `remark` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '备注',
     `system` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '修改时间',
     PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC COMMENT = '用户成长等级组' ;
INSERT INTO `dask_credit_group` VALUES (1,'萌新',1,'{\"visit_website\":\"Y\",\"publish_question_enable\":\"Y\",\"publish_question_approval\":\"Y\",\"publish_article_enable\":\"Y\",\"publish_article_approval\":\"Y\",\"publish_answer_enable\":\"Y\",\"publish_answer_approval\":\"Y\",\"modify_answer_approval\":\"Y\",\"modify_article_approval\":\"Y\",\"modify_question_approval\":\"Y\",\"available_invite_count\":\"5\",\"create_topic_enable\":\"N\",\"publish_approval_time_start\":\"23\",\"publish_approval_time_end\":\"5\",\"publish_url\":\"Y\",\"publish_question_num\":\"3\",\"publish_article_num\":\"3\",\"topic_manager\":\"N\"}',NULL,0,300,'',1);
INSERT INTO `dask_credit_group` VALUES (2,'童子',1,'{\"visit_website\":\"Y\",\"publish_question_enable\":\"Y\",\"publish_question_approval\":\"N\",\"publish_article_enable\":\"Y\",\"publish_article_approval\":\"N\",\"publish_answer_enable\":\"Y\",\"publish_answer_approval\":\"N\",\"modify_answer_approval\":\"N\",\"modify_article_approval\":\"N\",\"modify_question_approval\":\"N\",\"available_invite_count\":\"5\",\"create_topic_enable\":\"N\",\"publish_approval_time_start\":\"23\",\"publish_approval_time_end\":\"5\",\"publish_url\":\"Y\",\"publish_question_num\":\"3\",\"publish_article_num\":\"3\",\"topic_manager\":\"N\"}',NULL,300,1500,'',1);
INSERT INTO `dask_credit_group` VALUES (3,'道士',1,'{\"visit_website\":\"Y\",\"publish_question_enable\":\"Y\",\"publish_question_approval\":\"N\",\"publish_article_enable\":\"Y\",\"publish_article_approval\":\"N\",\"publish_answer_enable\":\"Y\",\"publish_answer_approval\":\"N\",\"modify_answer_approval\":\"N\",\"modify_article_approval\":\"N\",\"modify_question_approval\":\"N\",\"available_invite_count\":\"10\",\"create_topic_enable\":\"Y\",\"publish_approval_time_start\":\"23\",\"publish_approval_time_end\":\"5\",\"publish_url\":\"Y\",\"publish_question_num\":\"3\",\"publish_article_num\":\"3\",\"topic_manager\":\"N\"}',NULL,1500,5000,'',1);
INSERT INTO `dask_credit_group` VALUES (4,'真人',1,'{\"visit_website\":\"Y\",\"publish_question_enable\":\"Y\",\"publish_question_approval\":\"N\",\"publish_article_enable\":\"Y\",\"publish_article_approval\":\"N\",\"publish_answer_enable\":\"Y\",\"publish_answer_approval\":\"N\",\"modify_answer_approval\":\"N\",\"modify_article_approval\":\"N\",\"modify_question_approval\":\"N\",\"available_invite_count\":\"20\",\"create_topic_enable\":\"N\",\"publish_approval_time_start\":\"23\",\"publish_approval_time_end\":\"5\",\"publish_url\":\"Y\",\"publish_question_num\":\"3\",\"publish_article_num\":\"3\",\"topic_manager\":\"Y\"}',NULL,5000,10000,'',1);
INSERT INTO `dask_credit_group` VALUES (5,'天师',1,'{\"visit_website\":\"Y\",\"publish_question_enable\":\"Y\",\"publish_question_approval\":\"N\",\"publish_article_enable\":\"Y\",\"publish_article_approval\":\"N\",\"publish_answer_enable\":\"Y\",\"publish_answer_approval\":\"N\",\"modify_answer_approval\":\"N\",\"modify_article_approval\":\"N\",\"modify_question_approval\":\"N\",\"available_invite_count\":\"35\",\"create_topic_enable\":\"Y\",\"publish_approval_time_start\":\"23\",\"publish_approval_time_end\":\"5\",\"publish_url\":\"Y\",\"publish_question_num\":\"3\",\"publish_article_num\":\"3\",\"topic_manager\":\"Y\"}',NULL,10000,30000,'',1);
INSERT INTO `dask_credit_group` VALUES (6,'仙人',1,'{\"visit_website\":\"Y\",\"publish_question_enable\":\"Y\",\"publish_question_approval\":\"N\",\"publish_article_enable\":\"Y\",\"publish_article_approval\":\"N\",\"publish_answer_enable\":\"Y\",\"publish_answer_approval\":\"N\",\"modify_answer_approval\":\"N\",\"modify_article_approval\":\"N\",\"modify_question_approval\":\"N\",\"available_invite_count\":\"50\",\"create_topic_enable\":\"Y\",\"publish_approval_time_start\":\"23\",\"publish_approval_time_end\":\"5\",\"publish_url\":\"Y\",\"publish_question_num\":\"3\",\"publish_article_num\":\"3\",\"topic_manager\":\"Y\"}',NULL,30000,100000,'',1);
DROP TABLE IF EXISTS `dask_users_permission`;
CREATE TABLE `dask_users_permission`  (
     `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
     `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '权限字段',
     `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '权限标题',
     `tips` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '权限描述',
     `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '类型:string,text,int,bool,array,datetime,date,file',
     `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '权限值',
     `option` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '权限字典数据',
     `sort` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '排序',
     `extend` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '自定义规则配置',
     `group` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '权限分组',
     PRIMARY KEY (`id`) USING BTREE,
     UNIQUE INDEX `name`(`name`,`group`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户权限配置' ROW_FORMAT = DYNAMIC;

INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`,`group`) VALUES ('visit_website', '允许浏览网站', '', 'radio', 'Y', '{"N":"否","Y":"是"}', 0,'','common');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`,`group`) VALUES ('publish_question_enable', '发布问题', '是否允许发起问题', 'radio', 'Y', '{"N":"否","Y":"是"}', 0,'','common');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`,`group`) VALUES ('publish_question_approval', '提问审核', '发起问题时是否需要审核', 'radio', 'Y', '{"N":"否","Y":"是"}',  0,'','common');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`,`group`) VALUES ('publish_article_enable', '发起文章', '是否允许发起文章', 'radio', 'N', '{"N":"否","Y":"是"}',  0,'','common');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`,`group`) VALUES ('publish_article_approval', '发文审核', '发起文章时是否需要审核', 'radio', 'N', '{"N":"否","Y":"是"}',  0,'','common');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`,`group`) VALUES ('publish_answer_enable', '允许回答问题', '是否允许发起回答', 'radio', 'N', '{"N":"否","Y":"是"}',  0,'','common');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`,`group`) VALUES ('publish_answer_approval', '回答审核', '发起回答时是否需要审核', 'radio', 'N', '{"N":"否","Y":"是"}',  0,'','common');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`,`group`) VALUES ('modify_answer_approval', '修改回答审核', '修改回答时是否需要审核', 'radio', 'N', '{"N":"否","Y":"是"}',  0,'','common');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`,`group`) VALUES ('modify_article_approval', '修改文章审核', '修改文章时是否需要审核', 'radio', 'N', '{"N":"否","Y":"是"}',  0,'','common');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`,`group`) VALUES ('modify_question_approval', '修改提问审核', '修改提问时是否需要审核', 'radio', 'N', '{"N":"否","Y":"是"}',  0,'','common');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`,`group`) VALUES ('available_invite_count', '可邀请用户数量', '可邀请用户数量', 'number', '5', '',  0,'','common');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`,`group`) VALUES ('create_topic_enable', '创建话题', '', 'radio', 'N', '{"N":"否","Y":"是"}', 0,'','common');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`, `group`) VALUES ('publish_approval_time_start', '审核开始时间', '审核开始时间(请填写24小时制的标准时间格式,如希望5:30-凌晨23:00点，则开始时间为5:30),0代表全时间,只有开启审核此配置才生效', 'time', 0, '[]', 0, NULL, 'common');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`, `group`) VALUES ('publish_approval_time_end', '审核结束时间', '审核结束时间(请填写24小时制的标准时间格式,如希望5:00-凌晨23:00点，则结束时间为23:00),0代表全时间,只有开启审核此配置才生效', 'time', 0, '[]', 0, NULL, 'common');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`, `group`) VALUES ('publish_url', '发布站外链接', '', 'radio', 'Y', '{"N":"否","Y":"是"}', 0, NULL, 'common');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`, `group`) VALUES ('publish_question_num', '当天发布问题数量', '当天可发布问题数量', 'text', '3', '[]', 0, NULL, 'common');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`, `group`) VALUES ('publish_article_num', '当天发布文章数量', '当天可发布文章数量', 'text', '3', '[]', 0, NULL, 'common');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`, `group`) VALUES ('topic_manager', '允许编辑话题', '是否允许用户编辑话题', 'radio', 'N', '{"N":"否","Y":"是"}', 0, NULL, 'common');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`, `group`) VALUES ('modify_article', '编辑文章', '', 'radio', 'Y', '{"N":"否","Y":"是"}', 0, NULL, 'system');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`, `group`) VALUES ('remove_article', '删除文章', '', 'radio', 'Y', '{"N":"否","Y":"是"}', 0, NULL, 'system');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`, `group`) VALUES ('modify_question', '修改问题', '', 'radio', 'Y', '{"N":"否","Y":"是"}', 0, NULL, 'system');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`, `group`) VALUES ('remove_question', '删除问题', '', 'radio', 'Y', '{"N":"否","Y":"是"}', 0, NULL, 'system');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`, `group`) VALUES ('set_best_answer', '设置最佳回复', '', 'radio', 'Y', '{"N":"否","Y":"是"}', 0, NULL, 'system');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`, `group`) VALUES ('edit_content_topic', '编辑内容话题', '', 'radio', 'Y', '{"N":"否","Y":"是"}', 0, NULL, 'common');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`, `group`) VALUES ('recommend_post', '推荐内容', '', 'radio', 'Y', '{"N":"否","Y":"是"}', 0, NULL, 'system');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`, `group`) VALUES ('set_top_post', '设置置顶内容', '', 'radio', 'Y', '{"N":"否","Y":"是"}', 0, NULL, 'system');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`, `group`) VALUES ('modify_answer', '编辑回答', '', 'radio', 'Y', '{"N":"否","Y":"是"}', 0, NULL, 'system');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`, `group`) VALUES ('remove_answer', '删除回答', '', 'radio', 'Y', '{"N":"否","Y":"是"}', 0, NULL, 'system');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`, `group`) VALUES ('lock_topic', '锁定话题', '', 'radio', 'Y', '{"N":"否","Y":"是"}', 0, NULL, 'system');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`, `group`) VALUES ('remove_topic', '删除话题', '', 'radio', 'Y', '{"N":"否","Y":"是"}', 0, NULL, 'system');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`, `group`) VALUES ('redirect_question', '允许重定向问题', '', 'radio', 'N', '{\"N\":\"否\",\"Y\":\"是\"}', 0, NULL, 'system');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`, `group`) VALUES ('lock_question', '允许锁定问题', '', 'radio', 'N', '{\"N\":\"否\",\"Y\":\"是\"}', 0, NULL, 'system');
INSERT INTO `dask_users_permission` (`name`, `title`, `tips`, `type`, `value`, `option`, `sort`, `extend`, `group`) VALUES ('merge_topic', '允许合并话题', '', 'radio', 'N', '{\"N\":\"否\",\"Y\":\"是\"}', 0, NULL, 'system');

DROP TABLE IF EXISTS `dask_links`;
CREATE TABLE `dask_links` (
     `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
     `name` varchar(255) NOT NULL DEFAULT '' COMMENT '网站名称',
     `url` varchar(255) NOT NULL DEFAULT '' COMMENT '网站地址',
     `logo` varchar(80) NOT NULL DEFAULT '' COMMENT '网站logo',
     `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
     `sort` int(10) unsigned NOT NULL DEFAULT '50' COMMENT '排序',
     `status` tinyint(10) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
     `create_time` int(11) NOT NULL DEFAULT 0,
     `update_time` int(11) NOT NULL DEFAULT 0,
     PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='友情链接';


DROP TABLE IF EXISTS `dask_action`;
CREATE TABLE `dask_action` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
      `name` char(30) NOT NULL DEFAULT '' COMMENT '行为唯一标识',
      `title` char(80) NOT NULL DEFAULT '' COMMENT '行为说明',
      `remark` varchar (255) NOT NULL DEFAULT '' COMMENT '行为描述',
      `log_rule` text COMMENT '日志规则',
      `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态',
      PRIMARY KEY (`id`) USING BTREE,
      UNIQUE KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='用户行为类型';

INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (1, 'user_login', '用户登录', '用户登录系统','[user] 在 [time] 登录了系统', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (2, 'publish_question', '发起提问', '发起提问','[user] 在 [time] 发起了提问', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (3, 'publish_article', '发表文章', '发表文章','[user] 在 [time] 发表了文章', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (4, 'publish_answer', '发表回答', '发表回答','[user] 在 [time] 回答了问题', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (5, 'publish_article_comment', '发表文章评论', '发表文章评论','[user] 在 [time] 评论了文章', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (6, 'agree_question', '点赞问题', '点赞问题','[user] 在 [time] 点赞了问题', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (7, 'agree_article', '点赞文章', '点赞文章','[user] 在 [time] 点赞了文章', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (8, 'agree_answer', '点赞回答', '点赞回答','[user] 在 [time] 点赞了回答', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (9, 'focus_user', '关注用户', '关注用户','[user] 在 [time] 关注了用户', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (10, 'focus_question', '关注问题', '关注问题','[user] 在 [time] 关注了问题', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (11, 'modify_answer', '修改回答', '修改回答','[user] 在 [time] 修改了回答', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (12, 'modify_question_title', '修改问题标题', '修改问题标题','[user] 在 [time] 修改了问题标题', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (13, 'modify_article_title', '修改文章标题', '修改文章标题','[user] 在 [time] 修改了文章标题', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (14, 'modify_question_detail', '修改问题描述', '修改问题描述','[user] 在 [time] 修改了问题描述', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (15, 'modify_article_detail', '修改文章详情', '修改文章详情','[user] 在 [time] 修改了文章详情', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (16, 'modify_question_topic', '向话题添加问题', '向话题添加问题','[user] 在 [time] 向话题添加了问题', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (17, 'modify_article_topic', '向话题添加文章', '向话题添加文章','[user] 在 [time] 向话题添加了文章', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (18, 'create_topic', '创建话题', '新建话题','[user] 在 [time] 创建了话题', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (19, 'focus_column', '关注专栏', '关注专栏','[user] 在 [time] 关注了专栏', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (20, 'focus_topic', '关注话题', '关注话题','[user] 在 [time] 关注了话题', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (21, 'focus_favorite', '关注收藏夹', '关注收藏夹','[user] 在 [time] 关注了收藏夹', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (22, 'agree_article_comment', '点赞文章评论', '点赞文章评论','[user] 在 [time] 点赞了文章评论', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (23, 'agree_answer_comment', '点赞回答评论', '点赞回答评论','[user] 在 [time] 点赞了回答评论', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (24, 'create_column_article', '发表专栏文章', '发表专栏文章','[user] [time] 发表了文章,在专栏 [name]', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (25, 'modify_column_article', '修改专栏文章', '修改专栏文章','[user] [time] 修改了文章,在专栏 [name]', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (26, 'publish_column', '新建专栏', '新建专栏','[user] [time] 创建了专栏 [name]', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (27, 'update_topic', '编辑话题', '编辑话题','[user] 在 [time] 编辑了话题', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (28, 'lock_topic', '锁定话题', '锁定话题','[user] 在 [time] 锁定了话题', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (29, 'unlock_topic', '取消锁定话题', '取消锁定话题','[user] 在 [time] 取消了话题的锁定', 1);
INSERT INTO `dask_action` (`id`, `name`, `title`, `remark`, `log_rule`, `status`) VALUES (30, 'modify_log', '内容修改记录', '内容修改记录', '[user] 在 [time] 修改了内容', 1);
DROP TABLE IF EXISTS `dask_action_log`;
CREATE TABLE `dask_action_log` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
      `action_id` int(10) NOT NULL DEFAULT '0' COMMENT '行为id',
      `uid` int(10) NOT NULL DEFAULT '0' COMMENT '执行用户id',
      `action_ip` varchar(50) NOT NULL COMMENT '执行行为者ip',
      `record_type` varchar(50) NOT NULL DEFAULT '' COMMENT '触发行为的表类型',
      `record_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '触发行为的数据id',
      `relation_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联数据id',
      `relation_type` varchar(50) NOT NULL DEFAULT '' COMMENT '关联数据类型',
      `anonymous` tinyint(1) DEFAULT '0' COMMENT '是否匿名',
      `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态',
      `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行行为的时间',
      PRIMARY KEY (`id`) USING BTREE,
      KEY `action_ip` (`action_ip`) USING BTREE,
      KEY `action_id` (`action_id`) USING BTREE,
      KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='行为日志表';

DROP TABLE IF EXISTS `dask_action_log_all`;
CREATE TABLE `dask_action_log_all` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `action_id` int(10) NOT NULL DEFAULT '0' COMMENT '行为id',
  `uid` int(10) NOT NULL DEFAULT '0' COMMENT '执行用户id',
  `action_ip` varchar(50) NOT NULL COMMENT '执行行为者ip',
  `record_type` varchar(50) NOT NULL DEFAULT '' COMMENT '触发行为的表类型',
  `record_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '触发行为的数据id',
  `relation_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联数据id',
  `relation_type` varchar(50) NOT NULL DEFAULT '' COMMENT '关联数据类型',
  `anonymous` tinyint(1) DEFAULT '0' COMMENT '是否匿名',
  `data` text COMMENT '附加数据',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行行为的时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `action_ip` (`action_ip`) USING BTREE,
  KEY `action_id` (`action_id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='行为日志表';

DROP TABLE IF EXISTS `dask_action_log_data`;
CREATE TABLE `dask_action_log_data` (
    `log_id` int(11) unsigned NOT NULL,
    `data` text COMMENT '附加数据',
    `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态',
    PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='行为日志数据';

DROP TABLE IF EXISTS `dask_credit_rule`;
CREATE TABLE `dask_credit_rule` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
      `name` char(100) NOT NULL DEFAULT '' COMMENT '唯一标识',
      `title` varchar(255) NOT NULL DEFAULT '' COMMENT '规则说明',
      `cycle` int(11) NOT NULL DEFAULT '0' COMMENT '执行次数',
      `cycle_type` char(10) NOT NULL DEFAULT '' COMMENT '执行单位;month月,week周,day天,hour小时,minute分钟,second秒数',
      `max` int(10) DEFAULT '0' COMMENT '最大执行次数',
      `credit` int(11) DEFAULT '0' COMMENT '成长值变动',
      `log` text COMMENT '日志规则',
      `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态',
      PRIMARY KEY (`id`) USING BTREE,
      UNIQUE KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='成长值规则表';

BEGIN;
INSERT INTO `dask_credit_rule` VALUES (1, 'REGISTER', '用户注册', 1, 'day', 0, 200, '用户注册奖励成长值', 1);
INSERT INTO `dask_credit_rule` VALUES (2, 'LOGIN', '每日登录', 1, 'day', 1, 10, '登录系统,获得成长值奖励', 1);
INSERT INTO `dask_credit_rule` VALUES (3, 'NEW_QUESTION', '发起提问', 1, 'day', 0, -5, '发表提问,扣除成长值 #[record]', 1);
INSERT INTO `dask_credit_rule` VALUES (4, 'NEW_ARTICLE', '发表文章', 1, 'day', 3, 10, '发表文章,奖励成长值奖励 #[record]', 1);
INSERT INTO `dask_credit_rule` VALUES (5, 'ANSWER_QUESTION', '回答问题', 0, 'day', 0, 5, '回答问题,获得成长值奖励 #[record]', 1);
INSERT INTO `dask_credit_rule` VALUES (6, 'BEST_ANSWER', '被设为最佳回答', 1, 'day', 0, 20, '回答被设为最佳,获得成长值奖励 #[record]', 1);
INSERT INTO `dask_credit_rule` VALUES (7, 'INVITE_ANSWER', '邀请用户回答问题', 1, 'day', 0, -10, '邀请用户回答问题,扣除成长值 #[record]', 1);
INSERT INTO `dask_credit_rule` VALUES (8, 'ANSWER_INVITE', '被邀请用户回答问题', 1, 'day', 0, 10, '被邀请回答问题并回答,获得成长值奖励 #[record]', 1);
INSERT INTO `dask_credit_rule` VALUES (9, 'AWARD', '系统操作', 1, 'day', 0, 0, '系统操作成长值 #[time]', 1);
INSERT INTO `dask_credit_rule` VALUES (10, 'QUESTION_THANKS', '感谢问题', 1, 'day', 0, -10, '感谢了问题 #[record]', 1);
INSERT INTO `dask_credit_rule` VALUES (11, 'THANKS_QUESTION', '问题被感谢', 1, 'day', 0, 10, '问题被感谢 #[record]', 1);
INSERT INTO `dask_credit_rule` VALUES (12, 'ANSWER_THANKS', '感谢回复', 1, 'day', 0, -10, '感谢了回复 #[record]', 1);
INSERT INTO `dask_credit_rule` VALUES (13, 'THANKS_ANSWER', '回复被感谢', 1, 'day', 0, 10, '回复被感谢 #[record]', 1);
INSERT INTO `dask_credit_rule` VALUES (14, 'QUESTION_ANSWER', '问题被回答', 1, 'day', 0, -5, '问题被回答 #[record]', 1);
INSERT INTO `dask_credit_rule` VALUES (15, 'UPDATE_SIGNATURE', '完善一句话介绍', 1, 'day', 1, 20, '完善一句话介绍', 1);
INSERT INTO `dask_credit_rule` VALUES (16, 'UPLOAD_AVATAR', '上传头像', 1, 'day', 1, 20, '上传头像', 1);
INSERT INTO `dask_credit_rule` VALUES (17, 'UPDATE_CONTACT', '完善联系资料', 1, 'day', 1, 20, '完善联系资料', 1);
INSERT INTO `dask_credit_rule` VALUES (18, 'ANSWER_FOLD', '回复被折叠', 0, 'day', 1, -10, '回复被折叠 #[record]', 1);
INSERT INTO `dask_credit_rule` VALUES (19, 'UPDATE_EDU', '完善教育经历', 1, 'day', 1, 20, '完善教育经历', 1);
INSERT INTO `dask_credit_rule` VALUES (20, 'BIND_OPENID', '绑定第三方账号', 0, 'day', 1, 20, '绑定第三方账号', 1);
INSERT INTO `dask_credit_rule` VALUES (22, 'AGREE_QUESTION', '问题被赞同', 0, 'day', 0, 2, '问题被赞同 #[record]', 1);
INSERT INTO `dask_credit_rule` VALUES (23, 'AGREE_ARTICLE', '文章被赞同', 0, 'day', 0, 2, '文章被赞同 #[record]', 1);
INSERT INTO `dask_credit_rule` VALUES (24, 'AGREE_ANSWER', '回答被赞同', 0, 'day', 0, 2, '回答被赞同 #[record]', 1);
INSERT INTO `dask_credit_rule` VALUES (25, 'CREDIT_RECONCILE', '成长值校正', 0, 'day', 0, 0, '管理员手动校正成长值', 1);
INSERT INTO `dask_credit_rule` VALUES (21, 'INVITE_REGISTER', '邀请注册', 0, 'day', 0, 100, '邀请注册成功', 1);
COMMIT;

DROP TABLE IF EXISTS `dask_credit_log`;
CREATE TABLE `dask_credit_log` (
     `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
     `uid` int(11) DEFAULT '0' COMMENT '触发行为用户id',
     `record_id` char(16) DEFAULT NULL COMMENT '触发行为的数据id',
     `action_type` varchar(100) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '触发行为的类型',
     `credit` int(11) DEFAULT NULL COMMENT '成长值变动',
     `remark` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '积分说明',
     `balance` int(11) DEFAULT '0' COMMENT '成长值余额',
     `create_time` int(11) DEFAULT '0',
     `record_db` varchar(100) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '记录数据表',
     PRIMARY KEY (`id`) USING BTREE,
     KEY `uid` (`uid`) USING BTREE,
     KEY `action_type` (`action_type`) USING BTREE,
     KEY `create_time` (`create_time`) USING BTREE,
     KEY `credit` (`credit`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='成长值流水表';

DROP TABLE IF EXISTS `dask_users_favorite`;
CREATE TABLE `dask_users_favorite` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `uid` int(11) DEFAULT '0',
      `item_id` int(11) unsigned NOT NULL DEFAULT '0',
      `item_type` varchar(16) NOT NULL DEFAULT '',
      `tag_id` int(11) unsigned NOT NULL DEFAULT '0',
      `create_time` int(10) unsigned DEFAULT '0',
      PRIMARY KEY (`id`) USING BTREE,
      KEY `uid` (`uid`) USING BTREE,
      KEY `create_time` (`create_time`) USING BTREE,
      KEY `item_id` (`item_id`) USING BTREE,
      KEY `item_type` (`item_type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='用户收藏表';

DROP TABLE IF EXISTS `dask_users_favorite_tag`;
CREATE TABLE `dask_users_favorite_tag` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `uid` int(11) unsigned DEFAULT '0' COMMENT '创建用户',
      `title` varchar(128) DEFAULT NULL COMMENT '收藏标签',
      `description` varchar(255) NULL COMMENT '收藏夹描述',
      `post_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '内容数',
      `focus_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '关注数',
      `comment_count` int(11) NOT NULL DEFAULT '0' COMMENT '评论数',
      `is_public` tinyint(1) unsigned DEFAULT '0' COMMENT '1公开，0私密',
      `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`) USING BTREE,
      KEY `uid` (`uid`) USING BTREE,
      KEY `title` (`title`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='用户收藏标签表';

DROP TABLE IF EXISTS `dask_users_inbox`;
CREATE TABLE `dask_users_inbox` (
       `id` int(11) NOT NULL AUTO_INCREMENT,
       `uid` int(11) DEFAULT NULL COMMENT '发送者 ID',
       `dialog_id` int(11) DEFAULT NULL COMMENT '对话id',
       `message` mediumtext COMMENT '私信内容',
       `sender_remove` tinyint(1) DEFAULT '0' COMMENT '发送者删除消息',
       `recipient_remove` tinyint(1) DEFAULT '0' COMMENT '接受者删除消息',
       `send_time` int(10) DEFAULT '0' COMMENT '发送时间',
       `read_time` int(10) DEFAULT NULL COMMENT '读取时间',
       `status` tinyint(1) UNSIGNED NULL DEFAULT 1,
       PRIMARY KEY (`id`) USING BTREE,
       KEY `dialog_id` (`dialog_id`) USING BTREE,
       KEY `uid` (`uid`) USING BTREE,
       KEY `sender_remove` (`sender_remove`) USING BTREE,
       KEY `recipient_remove` (`recipient_remove`) USING BTREE,
       KEY `send_time` (`send_time`) USING BTREE,
       KEY `read_time` (`read_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='用户私信表';

DROP TABLE IF EXISTS `dask_users_inbox_dialog`;
CREATE TABLE `dask_users_inbox_dialog` (
      `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '对话ID',
      `sender_uid` int(11) DEFAULT NULL COMMENT '发送者UID',
      `sender_unread` int(11) DEFAULT NULL COMMENT '发送者未读',
      `recipient_uid` int(11) DEFAULT NULL COMMENT '接收者UID',
      `recipient_unread` int(11) DEFAULT NULL COMMENT '接收者未读',
      `create_time` int(11) DEFAULT NULL COMMENT '添加时间',
      `update_time` int(11) DEFAULT NULL COMMENT '最后更新时间',
      `sender_count` int(11) DEFAULT NULL COMMENT '发送者显示对话条数',
      `recipient_count` int(11) DEFAULT NULL COMMENT '接收者显示对话条数',
      `status` tinyint(1) UNSIGNED NULL DEFAULT 1,
      PRIMARY KEY (`id`) USING BTREE,
      KEY `recipient_uid` (`recipient_uid`) USING BTREE,
      KEY `sender_uid` (`sender_uid`) USING BTREE,
      KEY `update_time` (`update_time`) USING BTREE,
      KEY `create_time` (`create_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='用户私信对话表';

DROP TABLE IF EXISTS `dask_users_notify`;
CREATE TABLE `dask_users_notify` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
    `sender_uid` int(11) DEFAULT NULL COMMENT '发送者ID',
    `recipient_uid` int(11) DEFAULT '0' COMMENT '接收者ID',
    `action_type` varchar(100) DEFAULT NULL COMMENT '操作类型',
    `model_type` varchar(100) DEFAULT NULL COMMENT '分组类型',
    `item_id` varchar(16) NOT NULL DEFAULT '0' COMMENT '关联ID',
    `item_type` varchar(255) DEFAULT NULL COMMENT '关联类型',
    `subject` varchar(255) NOT NULL DEFAULT '' COMMENT '通知标题',
    `content` mediumtext COMMENT '通知内容',
    `anonymous` tinyint(1) DEFAULT '0' COMMENT '是否匿名',
    `create_time` int(10) DEFAULT NULL COMMENT '添加时间',
    `read_flag` tinyint(1) unsigned DEFAULT '0' COMMENT '阅读状态',
    `status` tinyint(1) unsigned DEFAULT '1' COMMENT '删除状态',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `recipient_read_flag` (`recipient_uid`,`read_flag`) USING BTREE,
    KEY `sender_uid` (`sender_uid`) USING BTREE,
    KEY `item_id` (`item_id`) USING BTREE,
    KEY `action_type` (`action_type`) USING BTREE,
    KEY `create_time` (`create_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='系统通知';

DROP TABLE IF EXISTS `dask_users_follow`;
CREATE TABLE `dask_users_follow` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
    `fans_uid` int(11) DEFAULT NULL COMMENT '关注人的UID',
    `friend_uid` int(11) DEFAULT NULL COMMENT '被关注人的uid',
    `create_time` int(10) DEFAULT NULL COMMENT '添加时间',
    `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否删除1正常0删除',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `fans_uid` (`fans_uid`) USING BTREE,
    KEY `friend_uid` (`friend_uid`) USING BTREE,
    KEY `user_follow` (`fans_uid`,`friend_uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='用户关注表';

DROP TABLE IF EXISTS `dask_users_forbidden`;
CREATE TABLE `dask_users_forbidden` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uid` int unsigned NOT NULL DEFAULT '0',
  `forbidden_time` int unsigned DEFAULT '0' COMMENT '封禁时长',
  `forbidden_reason` varchar(255) DEFAULT NULL COMMENT '封禁原因',
  `status` tinyint unsigned DEFAULT '0' COMMENT '是否删除',
  `create_time` int unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户封禁记录表';

DROP TABLE IF EXISTS `dask_users_online`;
CREATE TABLE `dask_users_online` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL COMMENT '用户 ID',
    `last_login_time` int(11) DEFAULT '0' COMMENT '上次活动时间',
    `last_login_ip` varchar(50) DEFAULT NULL COMMENT '客户端ip',
    `last_url` varchar(255) DEFAULT NULL COMMENT '停留页面',
    `user_agent` mediumtext DEFAULT NULL COMMENT '用户客户端信息',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `uid` (`uid`) USING BTREE,
    KEY `last_login_time` (`last_login_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='在线用户列表';

DROP TABLE IF EXISTS `dask_oauth`;
CREATE TABLE `dask_oauth` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `uid` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '会员ID',
  `platform` varchar(30) NOT NULL DEFAULT '' COMMENT '第三方应用',
  `openid` varchar(50) NOT NULL DEFAULT '' COMMENT '第三方唯一ID',
  `unionid` varchar(50) NOT NULL DEFAULT '' COMMENT '第三方唯一ID',
  `open_username` varchar(50) NOT NULL DEFAULT '' COMMENT '第三方会员昵称',
  `access_token` varchar(255) NOT NULL DEFAULT '' COMMENT 'AccessToken',
  `token` varchar(50) NOT NULL DEFAULT '' COMMENT '自定义Token',
  `refresh_token` varchar(255) NOT NULL DEFAULT '' COMMENT 'RefreshToken',
  `expires_in` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '有效期',
  `create_time` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `login_time` int(10) unsigned DEFAULT NULL COMMENT '登录时间',
  `expire_time` int(10) unsigned DEFAULT NULL COMMENT '过期时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `platform` (`platform`,`openid`),
  KEY `uid` (`uid`,`platform`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='第三方登录表';

DROP TABLE IF EXISTS `dask_users_token`;
CREATE TABLE `dask_users_token` (
       `token` varchar(64) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Token摘要（HMAC-SHA256）',
       `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
       `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
       `expire_time` int(10) DEFAULT NULL COMMENT '过期时间',
       PRIMARY KEY (`token`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4  ROW_FORMAT=DYNAMIC COMMENT='会员Token表';

DROP TABLE IF EXISTS `dask_approval`;
CREATE TABLE `dask_approval` (
    `id` int(10) NOT NULL AUTO_INCREMENT,
    `type` varchar(16) DEFAULT NULL COMMENT '审核类型',
    `item_id` int(10) unsigned DEFAULT '0' COMMENT '审核内容ID',
    `data` longtext NOT NULL COMMENT '审核数据',
    `uid` int(11) NOT NULL DEFAULT '0',
    `reason` varchar(255) DEFAULT NULL COMMENT '拒绝原因',
    `status` int(11) NOT NULL DEFAULT '0' COMMENT '0待审核,已审核,2已拒绝',
    `access_key` varchar(255) DEFAULT NULL COMMENT '批次ID',
    `create_time` int(10) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `type` (`type`) USING BTREE,
    KEY `uid` (`uid`) USING BTREE,
    KEY `create_time` (`create_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='审核表';

DROP TABLE IF EXISTS `dask_answer`;
CREATE TABLE IF NOT EXISTS `dask_answer` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '回答id',
    `question_id` int(11) unsigned NOT NULL COMMENT '问题id',
    `content` longtext COMMENT '回答内容',
    `against_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '反对人数',
    `agree_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '支持人数',
    `uid` int(11) DEFAULT '0' COMMENT '发布问题用户ID',
    `comment_count` int(11) unsigned DEFAULT '0' COMMENT '评论总数',
    `uninterested_count` int(11) unsigned DEFAULT '0' COMMENT '不感兴趣',
    `thanks_count` int(11) unsigned DEFAULT '0' COMMENT '感谢数量',
    `answer_user_ip` varchar(50) DEFAULT NULL COMMENT '回答用户的来源IP',
    `answer_user_local` varchar(255) DEFAULT NULL COMMENT '回答用户的来源地址',
    `is_anonymous` tinyint(1) unsigned DEFAULT '0' COMMENT '是否匿名回答',
    `publish_source` varchar(16) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '回答内容来源',
    `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '内容状态',
    `is_best` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为最佳回复1是',
    `best_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最佳答案的设定人id',
    `best_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最佳答案的设定时间',
    `force_fold` tinyint(1) DEFAULT '0' COMMENT '强制折叠',
    `search_text` text COMMENT '搜索文本',
    `popular_value` double NOT NULL DEFAULT '0' COMMENT '热度值',
    `popular_value_update` int(10) NOT NULL DEFAULT '0' COMMENT '热度值更新时间',
    `create_time` int(10) unsigned DEFAULT '0' COMMENT '添加时间',
    `update_time` int(10) unsigned DEFAULT '0' COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `question_id` (`question_id`) USING BTREE,
    KEY `agree_count` (`agree_count`) USING BTREE,
    KEY `against_count` (`against_count`) USING BTREE,
    KEY `create_time` (`create_time`) USING BTREE,
    KEY `uid` (`uid`) USING BTREE,
    KEY `uninterested_count` (`uninterested_count`) USING BTREE,
    KEY `is_anonymous` (`is_anonymous`) USING BTREE,
    KEY `publish_source` (`publish_source`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4  ROW_FORMAT=DYNAMIC COMMENT='问题回答表';

DROP TABLE IF EXISTS `dask_answer_comment`;
CREATE TABLE IF NOT EXISTS `dask_answer_comment` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `pid` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '回复评论的id',
    `answer_id` int(11) DEFAULT '0' COMMENT '问题id',
    `uid` int(11) DEFAULT '0' COMMENT '评论人',
    `message` text COMMENT '评论内容',
    `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否删除1正常0删除',
    `at_uid` varchar(255) DEFAULT  null COMMENT '被@用户id',
    `create_time` int(10) DEFAULT '0' COMMENT '评论时间',
    `update_time` int(10) unsigned DEFAULT '0' COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `answer_id` (`answer_id`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='回答评论表';

DROP TABLE IF EXISTS `dask_article`;
CREATE TABLE IF NOT EXISTS `dask_article` (
    `id` int(10) NOT NULL AUTO_INCREMENT,
    `uid` int(10) NOT NULL,
    `title` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    `message` longtext CHARACTER SET utf8mb4,
    `comment_count` int(10) DEFAULT '0',
    `view_count` int(10) DEFAULT '0',
    `agree_count` int(11) NOT NULL DEFAULT '0' COMMENT '赞同数总和',
    `against_count` int(11) NOT NULL DEFAULT '0' COMMENT '反对数总和',
    `category_id` int(10) DEFAULT '0',
    `is_recommend` tinyint(1) DEFAULT '0',
    `sort` tinyint(2) unsigned NOT NULL DEFAULT '0',
    `column_id` int(11) DEFAULT NULL COMMENT '所属专栏id',
    `cover` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '文章封面',
    `status` tinyint(1) unsigned DEFAULT '0' COMMENT '文章状态1正常0删除2待审核3待发布',
    `set_top` tinyint(1) unsigned DEFAULT '0' COMMENT '是否置顶 0不置顶1置顶',
    `set_top_time` int(11) unsigned DEFAULT '0' COMMENT '置顶时间',
    `wait_time` int(10) DEFAULT '0' COMMENT '定时时间',
    `popular_value` double NOT NULL DEFAULT '0' COMMENT '热度值',
    `popular_value_update` int(10) NOT NULL DEFAULT '0' COMMENT '热度值更新时间',
    `user_ip` varchar(50) DEFAULT NULL COMMENT '用户的来源IP',
    `article_type` varchar(50) NOT NULL DEFAULT 'normal' COMMENT 'normal普通文章',
    `extends` text COMMENT '附加信息,用于存储附属信息',
    `search_text` text COMMENT '搜索文本',
    `seo_title` varchar(100) DEFAULT NULL COMMENT 'SEO标题',
    `seo_keywords` varchar(255) DEFAULT NULL COMMENT 'SEO关键词',
    `seo_description` varchar(255) DEFAULT NULL COMMENT 'SEO描述',
    `url_token` varchar(255) DEFAULT NULL COMMENT '自定义URL',
    `create_time` int(10) DEFAULT '0' COMMENT '发布时间',
    `update_time` int(10) DEFAULT '0' COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `uid` (`uid`) USING BTREE,
    KEY `comment_count` (`comment_count`) USING BTREE,
    KEY `view_count` (`view_count`) USING BTREE,
    KEY `category_id` (`category_id`) USING BTREE,
    KEY `is_recommend` (`is_recommend`) USING BTREE,
    KEY `sort` (`sort`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='文章表';

DROP TABLE IF EXISTS `dask_article_comment`;
CREATE TABLE IF NOT EXISTS `dask_article_comment` (
    `id` int(10) NOT NULL AUTO_INCREMENT,
    `uid` int(10) NOT NULL,
    `article_id` int(10) NOT NULL,
    `message` text NOT NULL,
    `create_time` int(10) NOT NULL,
    `at_uid` varchar(255) DEFAULT  null COMMENT '被@用户id',
    `pid` int UNSIGNED NULL DEFAULT 0 COMMENT '父级评论',
    `agree_count` int(11) NOT NULL DEFAULT '0' COMMENT '赞同数总和',
    `against_count` int(11) NOT NULL DEFAULT '0' COMMENT '反对数总和',
    `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '内容状态',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `uid` (`uid`) USING BTREE,
    KEY `article_id` (`article_id`) USING BTREE,
    KEY `create_time` (`create_time`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='文章评论表';

DROP TABLE IF EXISTS `dask_article_vote`;
CREATE TABLE IF NOT EXISTS `dask_article_vote` (
    `id` int(10) NOT NULL AUTO_INCREMENT,
    `uid` int(10) NOT NULL COMMENT '投票用户',
    `item_type` varchar(16) DEFAULT NULL COMMENT '内容类型',
    `item_id` int(10) NOT NULL COMMENT '内容ID',
    `vote_value` tinyint(1) DEFAULT '0' COMMENT '1赞同,-1反对',
    `create_time` int(10) NOT NULL COMMENT '操作时间',
    `weigh_factor` int(10) DEFAULT '0' COMMENT '赞同反对系数',
    `item_uid` int(10) DEFAULT '0' COMMENT '被投票用户',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `uid` (`uid`) USING BTREE,
    KEY `item_type` (`item_type`) USING BTREE,
    KEY `item_id` (`item_id`) USING BTREE,
    KEY `create_time` (`create_time`) USING BTREE,
    KEY `item_uid` (`item_uid`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='文章赞踩表';

DROP TABLE IF EXISTS `dask_category`;
CREATE TABLE IF NOT EXISTS `dask_category` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(128) DEFAULT NULL COMMENT '分类名称',
    `description` varchar(255) DEFAULT NULL COMMENT '分类描述',
    `type` varchar(16) DEFAULT NULL COMMENT '分类类型',
    `icon` varchar(255) DEFAULT NULL COMMENT '分类图标',
    `sort` smallint(6) DEFAULT '0' COMMENT '分类排序',
    `url_token` varchar(32) DEFAULT NULL COMMENT '分类别名',
    `status` tinyint(1) unsigned DEFAULT '0' COMMENT '分类状态',
    `create_time` int(10) unsigned DEFAULT '0' COMMENT '添加时间',
    `update_time` int(10) unsigned DEFAULT '0' COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `url_token` (`url_token`) USING BTREE,
    KEY `title` (`title`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='分类表';

DROP TABLE IF EXISTS `dask_column`;
CREATE TABLE IF NOT EXISTS `dask_column` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '专栏id',
    `name` varchar(64) DEFAULT NULL COMMENT '专栏标题',
    `verify` tinyint(1) DEFAULT '0' COMMENT '是否审核通过 （1通过0审核中-1通过）',
    `focus_count` int(11) DEFAULT '0' COMMENT '关注计数',
    `description` text COMMENT '专栏描述',
    `cover` varchar(255) DEFAULT NULL COMMENT '专栏图片',
    `uid` int(11) DEFAULT NULL COMMENT '用户UID',
    `sort` int(10) DEFAULT '0' COMMENT '排序',
    `reason` varchar(100) DEFAULT NULL COMMENT '拒绝原因',
    `recommend` tinyint(1) unsigned DEFAULT '0' COMMENT '是否推荐',
    `view_count` int(10) unsigned DEFAULT '0' COMMENT '专栏浏览数',
    `post_count` int(10) unsigned DEFAULT '0' COMMENT '专栏文章数',
    `join_count` int(10) unsigned DEFAULT '0' COMMENT '专栏签约用户数',
    `popular_value` double NOT NULL DEFAULT '0' COMMENT '热度值',
    `popular_value_update` int(10) NOT NULL DEFAULT '0' COMMENT '热度值更新时间',
    `create_time` int(10) DEFAULT NULL COMMENT '添加时间',
    `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='专栏表';

DROP TABLE IF EXISTS `dask_column_focus`;
CREATE TABLE IF NOT EXISTS `dask_column_focus` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
    `column_id` int(11) DEFAULT NULL COMMENT '问题ID',
    `uid` int(11) DEFAULT NULL COMMENT '用户UID',
    `create_time` int(10) DEFAULT NULL COMMENT '添加时间',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `uid` (`uid`) USING BTREE,
    KEY `column_id` (`column_id`) USING BTREE,
    KEY `column_uid` (`column_id`,`uid`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='专栏关注表';

DROP TABLE IF EXISTS `dask_draft`;
CREATE TABLE IF NOT EXISTS `dask_draft` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `uid` int(11) DEFAULT '0',
    `item_type` varchar(16) DEFAULT NULL,
    `item_id` char(16) NOT NULL,
    `data` longtext,
    `create_time` int(10) DEFAULT '0',
    `update_time` int(10) unsigned DEFAULT '0',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `uid` (`uid`) USING BTREE,
    KEY `item_id` (`item_id`) USING BTREE,
    KEY `create_time` (`create_time`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='草稿表';

DROP TABLE IF EXISTS `dask_post_relation`;
CREATE TABLE IF NOT EXISTS `dask_post_relation` (
    `id` int(10) NOT NULL AUTO_INCREMENT,
    `item_id` int(10) NOT NULL COMMENT '类型ID',
    `item_type` varchar(16) NOT NULL DEFAULT '' COMMENT '内容类型',
    `category_id` int(10) DEFAULT '0' COMMENT '分类ID',
    `is_recommend` tinyint(1) DEFAULT '0' COMMENT '是否推荐',
    `view_count` int(10) DEFAULT '0' COMMENT '浏览量',
    `is_anonymous` tinyint(1) DEFAULT '0' COMMENT '是否匿名',
    `popular_value` double NOT NULL DEFAULT '0' COMMENT '热度值',
    `uid` int(10) NOT NULL COMMENT '内容uid',
    `agree_count` int(10) DEFAULT '0' COMMENT '赞同数',
    `answer_count` int(10) DEFAULT '0' COMMENT '回答数/评论数',
    `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除1正常0删除',
    `set_top` tinyint(1) unsigned DEFAULT '0' COMMENT '是否置顶 0不置顶1置顶',
    `set_top_time` int(11) unsigned DEFAULT '0' COMMENT '置顶时间',
    `relation_type` varchar(100) DEFAULT NULL COMMENT '关联类型',
    `relation_id` int(10) UNSIGNED NULL DEFAULT '0' COMMENT '关联类型ID',
    `create_time` int(10) NOT NULL COMMENT '创建时间',
    `update_time` int(10) DEFAULT '0' COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `item_id` (`item_id`) USING BTREE,
    KEY `item_type` (`item_type`) USING BTREE,
    KEY `create_time` (`create_time`) USING BTREE,
    KEY `update_time` (`update_time`) USING BTREE,
    KEY `category_id` (`category_id`) USING BTREE,
    KEY `recommend` (`is_recommend`) USING BTREE,
    KEY `anonymous` (`is_anonymous`) USING BTREE,
    KEY `popular_value` (`popular_value`) USING BTREE,
    KEY `uid` (`uid`) USING BTREE,
    KEY `agree_count` (`agree_count`) USING BTREE,
    KEY `answer_count` (`answer_count`) USING BTREE,
    KEY `view_count` (`view_count`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='内容聚合表';

DROP TABLE IF EXISTS `dask_question`;
CREATE TABLE `dask_question` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL DEFAULT '' COMMENT '问题内容',
    `detail` text CHARACTER SET utf8mb4 COMMENT '问题说明',
    `uid` int(11) DEFAULT NULL COMMENT '发布用户UID',
    `answer_count` int(11) NOT NULL DEFAULT '0' COMMENT '回答计数',
    `answer_users` int(11) NOT NULL DEFAULT '0' COMMENT '回答人数',
    `view_count` int(11) NOT NULL DEFAULT '0' COMMENT '浏览次数',
    `focus_count` int(11) NOT NULL DEFAULT '0' COMMENT '关注数',
    `comment_count` int(11) NOT NULL DEFAULT '0' COMMENT '评论数',
    `thanks_count` int(10) DEFAULT '0' COMMENT '感谢数量',
    `category_id` int(11) NOT NULL DEFAULT '0' COMMENT '分类 ID',
    `user_ip` varchar(50) DEFAULT NULL COMMENT '用户的来源IP',
    `agree_count` int(11) NOT NULL DEFAULT '0' COMMENT '回复赞同数总和',
    `against_count` int(11) NOT NULL DEFAULT '0' COMMENT '回复反对数总和',
    `best_answer` int(11) NOT NULL DEFAULT '0' COMMENT '最佳回复ID',
    `modify_count` int(10) NOT NULL DEFAULT '0' COMMENT '修改次数',
    `last_answer` int(11) NOT NULL DEFAULT '0' COMMENT '最后回答 ID',
    `popular_value` double NOT NULL DEFAULT '0' COMMENT '热度值',
    `popular_value_update` int(10) NOT NULL DEFAULT '0' COMMENT '热度值更新时间',
    `is_lock` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否锁定',
    `is_anonymous` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否匿名提问',
    `is_recommend` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否推荐问题',
    `sort` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '排序值',
    `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '问题状态1正常0删除2待审核',
    `set_top` tinyint(1) unsigned DEFAULT '0' COMMENT '是否置顶 0不置顶1置顶',
    `set_top_time` int(11) unsigned DEFAULT '0' COMMENT '置顶时间',
    `question_type` varchar(50) NOT NULL DEFAULT 'normal' COMMENT 'normal普通问题',
    `best_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最佳答案的设定人id',
    `best_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最佳答案的设定时间',
    `seo_title` varchar(100) DEFAULT NULL COMMENT 'SEO标题',
    `seo_keywords` varchar(255) DEFAULT NULL COMMENT 'SEO关键词',
    `seo_description` varchar(255) DEFAULT NULL COMMENT 'SEO描述',
    `url_token` varchar(255) DEFAULT NULL COMMENT '自定义URL',
    `extends` text COMMENT '附加信息,用于存储附属信息',
    `search_text` text COMMENT '搜索文本',
    `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
    `update_time` int(11) unsigned DEFAULT '0' COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `category_id` (`category_id`) USING BTREE,
    KEY `update_time` (`update_time`) USING BTREE,
    KEY `uid` (`uid`) USING BTREE,
    KEY `answer_count` (`answer_count`) USING BTREE,
    KEY `agree_count` (`agree_count`) USING BTREE,
    KEY `title` (`title`) USING BTREE,
    KEY `is_lock` (`is_lock`) USING BTREE,
    KEY `is_anonymous` (`is_anonymous`) USING BTREE,
    KEY `popular_value` (`popular_value`) USING BTREE,
    KEY `best_answer` (`best_answer`) USING BTREE,
    KEY `popular_value_update` (`popular_value_update`) USING BTREE,
    KEY `against_count` (`against_count`) USING BTREE,
    KEY `is_recommend` (`is_recommend`) USING BTREE,
    KEY `modify_count` (`modify_count`) USING BTREE,
    KEY `sort` (`sort`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4  ROW_FORMAT=DYNAMIC COMMENT='问题表';

DROP TABLE IF EXISTS `dask_question_comment`;
CREATE TABLE IF NOT EXISTS `dask_question_comment` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `pid` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '回复评论的id',
    `question_id` int(11) DEFAULT '0' COMMENT '问题id',
    `uid` int(11) DEFAULT '0' COMMENT '评论人',
    `message` text COMMENT '评论内容',
    `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否删除1正常0删除',
    `at_uid` varchar(255) DEFAULT  null COMMENT '被@用户id',
    `create_time` int(10) DEFAULT '0' COMMENT '评论时间',
    `update_time` int(10) unsigned DEFAULT '0' COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `question_id` (`question_id`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='问题评论表';

DROP TABLE IF EXISTS `dask_question_focus`;
CREATE TABLE IF NOT EXISTS `dask_question_focus` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
    `question_id` int(11) DEFAULT NULL COMMENT '问题ID',
    `uid` int(11) DEFAULT NULL COMMENT '用户UID',
    `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否删除1正常0删除',
    `create_time` int(10) DEFAULT NULL COMMENT '添加时间',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `uid` (`uid`) USING BTREE,
    KEY `question_id` (`question_id`) USING BTREE,
    KEY `question_uid` (`question_id`,`uid`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='问题关注表';

DROP TABLE IF EXISTS `dask_question_invite`;
CREATE TABLE IF NOT EXISTS `dask_question_invite` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
    `question_id` int(11) NOT NULL COMMENT '问题ID',
    `sender_uid` int(11) NOT NULL,
    `recipient_uid` int(11) DEFAULT NULL,
    `create_time` int(10) DEFAULT '0' COMMENT '添加时间',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `question_id` (`question_id`) USING BTREE,
    KEY `sender_uid` (`sender_uid`) USING BTREE,
    KEY `recipient_uid` (`recipient_uid`) USING BTREE,
    KEY `create_time` (`create_time`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='邀请问答';

DROP TABLE IF EXISTS `dask_question_vote`;
CREATE TABLE IF NOT EXISTS `dask_question_vote` (
    `id` int(10) NOT NULL AUTO_INCREMENT,
    `uid` int(10) NOT NULL COMMENT '投票用户',
    `item_type` varchar(16) DEFAULT NULL COMMENT '内容类型',
    `item_id` int(10) NOT NULL COMMENT '内容ID',
    `vote_value` tinyint(1) DEFAULT '0' COMMENT '1赞同,-1反对',
    `create_time` int(10) NOT NULL COMMENT '操作时间',
    `weigh_factor` int(10) DEFAULT '0' COMMENT '赞同反对系数',
    `item_uid` int(10) DEFAULT '0' COMMENT '被投票用户',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `uid` (`uid`) USING BTREE,
    KEY `item_type` (`item_type`) USING BTREE,
    KEY `item_id` (`item_id`) USING BTREE,
    KEY `create_time` (`create_time`) USING BTREE,
    KEY `item_uid` (`item_uid`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='赞踩表';

DROP TABLE IF EXISTS `dask_report`;
CREATE TABLE `dask_report` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `uid` int(11) DEFAULT '0' COMMENT '用户id',
      `item_type` varchar(50) DEFAULT NULL COMMENT '内容类型',
      `item_id` int(11) DEFAULT '0' COMMENT '举报内容id',
      `handle_type` tinyint(1) DEFAULT NULL COMMENT '处理方式，0拒绝处理，1删除内容，2修改内容',
      `handle_reason` varchar(255) DEFAULT NULL COMMENT '处理说明',
      `reason` text DEFAULT NULL COMMENT '举报理由',
      `url` varchar(255) DEFAULT NULL COMMENT '举报内容URL',
      `create_time` int(11) DEFAULT '0' COMMENT '举报时间',
      `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否处理',
      PRIMARY KEY (`id`) USING BTREE,
      KEY `create_time` (`create_time`) USING BTREE,
      KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='内容举报表';

DROP TABLE IF EXISTS `dask_topic`;
CREATE TABLE `dask_topic` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '话题id',
    `pid` int(10) DEFAULT '0' COMMENT '父级话题id',
    `is_parent` tinyint(1) unsigned DEFAULT '0' COMMENT '是否是根话题',
    `uid` int(10) DEFAULT '0' COMMENT '话题创建者',
    `title` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '话题标题',
    `discuss` int(11) DEFAULT '0' COMMENT '讨论计数',
    `description` longtext CHARACTER SET utf8mb4 COMMENT '话题描述',
    `pic` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '话题图片',
    `lock` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否锁定',
    `top` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否推荐',
    `focus` int(11) DEFAULT '0' COMMENT '关注计数',
    `related` tinyint(1) DEFAULT '0' COMMENT '是否被用户关联',
    `url_token` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
    `seo_title` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
    `seo_keywords` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `seo_description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `discuss_week` int(10) DEFAULT '0' COMMENT '一周讨论数',
    `discuss_month` int(10) DEFAULT '0' COMMENT '一月讨论数',
    `discuss_update` int(10) DEFAULT '0' COMMENT '讨论更新时间',
    `create_time` int(10) DEFAULT NULL COMMENT '添加时间',
    `status` tinyint(1) UNSIGNED NULL DEFAULT 1 COMMENT '审核状态',
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE KEY `title` (`title`) USING BTREE,
    KEY `url_token` (`url_token`) USING BTREE,
    KEY `discuss` (`discuss`) USING BTREE,
    KEY `create_time` (`create_time`) USING BTREE,
    KEY `related` (`related`) USING BTREE,
    KEY `focus` (`focus`) USING BTREE,
    KEY `lock` (`lock`) USING BTREE,
    KEY `pid` (`pid`) USING BTREE,
    KEY `discuss_week` (`discuss_week`) USING BTREE,
    KEY `discuss_month` (`discuss_month`) USING BTREE,
    KEY `discuss_update` (`discuss_update`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='话题';


DROP TABLE IF EXISTS `dask_topic_focus`;
CREATE TABLE `dask_topic_focus` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
    `topic_id` int(11) DEFAULT NULL COMMENT '话题ID',
    `uid` int(11) DEFAULT NULL COMMENT '用户UID',
    `create_time` int(10) DEFAULT NULL COMMENT '添加时间',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `uid` (`uid`) USING BTREE,
    KEY `topic_id` (`topic_id`) USING BTREE,
    KEY `topic_uid` (`topic_id`,`uid`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='话题关注表';

DROP TABLE IF EXISTS `dask_topic_relation`;
CREATE TABLE `dask_topic_relation` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增 ID',
    `topic_id` int(11) DEFAULT '0' COMMENT '话题id',
    `uid` int(11) DEFAULT '0' COMMENT '用户ID',
    `item_id` int(11) DEFAULT '0',
    `item_type` varchar(16) DEFAULT NULL,
    `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否删除',
    `create_time` int(10) DEFAULT '0' COMMENT '添加时间',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `topic_id` (`topic_id`) USING BTREE,
    KEY `uid` (`uid`) USING BTREE,
    KEY `item_type` (`item_type`) USING BTREE,
    KEY `item_id` (`item_id`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='内容聚合表';

DROP TABLE IF EXISTS `dask_answer_thanks`;
CREATE TABLE `dask_answer_thanks` (
     `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     `uid` int(11) DEFAULT '0' COMMENT '用户ID',
     `answer_id` int(11) DEFAULT '0' COMMENT '回答ID',
     `create_time` int(11) DEFAULT '0',
     PRIMARY KEY (`id`) USING BTREE,
     KEY `answer_id` (`answer_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='回答感谢表';

DROP TABLE IF EXISTS `dask_uninterested`;
CREATE TABLE `dask_uninterested` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `uid` int(11) DEFAULT '0' COMMENT '用户ID',
        `item_id` int(11) DEFAULT '0' COMMENT '内容ID',
        `item_type` varchar(100) DEFAULT '' COMMENT '内容类型',
        `create_time` int(11) DEFAULT '0',
        PRIMARY KEY (`id`) USING BTREE,
        KEY `item_id` (`item_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='内容不感兴趣表';

DROP TABLE IF EXISTS `dask_users_verify`;
CREATE TABLE `dask_users_verify` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL COMMENT '用户ID',
    `data` longtext COMMENT '审核数据',
    `status` tinyint(1) DEFAULT '0' COMMENT '审核状态0未提交,1待审核2已审核3拒绝审核',
    `type` varchar(32) DEFAULT '' COMMENT '审核类型',
    `reason` varchar(255) NOT NULL COMMENT '审核理由',
    `create_time` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `uid` (`uid`),
    KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户认证资料表';

DROP TABLE IF EXISTS `dask_users_verify_type`;
CREATE TABLE `dask_users_verify_type` (
 `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
 `name` char(100) NOT NULL DEFAULT '' COMMENT '认证标识',
 `title` char(100) NOT NULL DEFAULT '' COMMENT '认证名称',
 `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
 `icon` varchar(255) DEFAULT NULL COMMENT '认证图标',
 `remark` text NOT NULL COMMENT '备注',
 PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='用户认证类型';
INSERT INTO `dask_users_verify_type` (`id`, `name`, `title`, `status`, `icon`, `remark`) VALUES (1, 'personal', '个人认证', 1, 'static/common/image/people.svg', '个人认证');
INSERT INTO `dask_users_verify_type` (`id`, `name`, `title`, `status`, `icon`, `remark`) VALUES (2, 'enterprise', '机构认证', 1, 'static/common/image/company.svg', '机构认证');

DROP TABLE IF EXISTS `dask_verify_field`;
CREATE TABLE `dask_verify_field` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `name` varchar(30) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '变量名',
   `verify_type` varchar(30) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '认证类型',
   `title` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '变量标题',
   `tips` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '变量描述',
   `type` varchar(30) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '类型:string,text,int,bool,array,datetime,date,file',
   `value` text COLLATE utf8mb4_general_ci NOT NULL COMMENT '变量值',
   `option` text COLLATE utf8mb4_general_ci NOT NULL COMMENT '变量字典数据',
   `sort` int(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0'  COMMENT '排序字段',
   `validate` tinyint(1) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '1'  COMMENT '是否必填',
   `verify_show` tinyint(1) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '1'  COMMENT '是否认证显示',
   `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
   `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
   PRIMARY KEY (`id`) USING BTREE,
   UNIQUE KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4  ROW_FORMAT=DYNAMIC COMMENT='认证字段管理';

INSERT INTO `dask_verify_field` (`id`, `name`, `verify_type`, `title`, `tips`, `type`, `value`, `option`, `sort`, `create_time`, `update_time`,`validate`) VALUES (1, 'real_name', 'personal', '真实姓名', '填写您的真实姓名', 'text', '', '[]', 0, 1621141649, 0, 1);
INSERT INTO `dask_verify_field` (`id`, `name`, `verify_type`, `title`, `tips`, `type`, `value`, `option`, `sort`, `create_time`, `update_time`,`validate`) VALUES (2, 'card', 'personal', '身份证', '填写身份证号码', 'text', '', '[]', 0, 1621141718, 0, 1);
INSERT INTO `dask_verify_field` (`id`, `name`, `verify_type`, `title`, `tips`, `type`, `value`, `option`, `sort`, `create_time`, `update_time`,`validate`) VALUES (3, 'mobile', 'personal', '联系方式', '', 'text', '', '[]', 0, 1621141759, 0, 1);
INSERT INTO `dask_verify_field` (`id`, `name`, `verify_type`, `title`, `tips`, `type`, `value`, `option`, `sort`, `create_time`, `update_time`,`validate`) VALUES (4, 'remark_tag', 'personal', '认证标签', '', 'text', '', '[]', 0, 1621141784, 0, 1);
INSERT INTO `dask_verify_field` (`id`, `name`, `verify_type`, `title`, `tips`, `type`, `value`, `option`, `sort`, `create_time`, `update_time`,`validate`) VALUES (5, 'remark', 'personal', '认证说明', '', 'textarea', '', '[]', 0, 1621141784, 0, 1);
INSERT INTO `dask_verify_field` (`id`, `name`, `verify_type`, `title`, `tips`, `type`, `value`, `option`, `sort`, `create_time`, `update_time`,`validate`) VALUES (6, 'company_name', 'enterprise', '机构名称', '', 'text', '', '[]', 0, 1621143505, 0, 1);
INSERT INTO `dask_verify_field` (`id`, `name`, `verify_type`, `title`, `tips`, `type`, `value`, `option`, `sort`, `create_time`, `update_time`,`validate`) VALUES (7, 'company_code', 'enterprise', '组织代码', '', 'text', '', '[]', 0, 1621143524, 0, 1);
INSERT INTO `dask_verify_field` (`id`, `name`, `verify_type`, `title`, `tips`, `type`, `value`, `option`, `sort`, `create_time`, `update_time`,`validate`) VALUES (8, 'company_mobile', 'enterprise', '联系电话', '', 'text', '', '[]', 0, 1621143553, 0, 1);
INSERT INTO `dask_verify_field` (`id`, `name`, `verify_type`, `title`, `tips`, `type`, `value`, `option`, `sort`, `create_time`, `update_time`,`validate`) VALUES (9, 'company_image', 'enterprise', '证件执照', '', 'image', '', '[]', 0, 1621143630, 0, 1);
INSERT INTO `dask_verify_field` (`id`, `name`, `verify_type`, `title`, `tips`, `type`, `value`, `option`, `sort`, `create_time`, `update_time`,`validate`) VALUES (10, 'company_remark', 'enterprise', '认证说明', '', 'textarea', '', '[]', 0, 1621143655, 0, 1);

DROP TABLE IF EXISTS `dask_page`;
CREATE TABLE `dask_page` (
   `id` int NOT NULL AUTO_INCREMENT,
   `title` varchar(255) DEFAULT NULL,
   `keywords` varchar(255) DEFAULT NULL,
   `description` varchar(255) DEFAULT NULL,
   `contents` longtext,
   `url_name` varchar(32) NOT NULL,
   `status` tinyint(1) NOT NULL DEFAULT '1',
   `create_time` int DEFAULT '0' COMMENT '发布时间',
   `update_time` int DEFAULT '0' COMMENT '更新时间',
   PRIMARY KEY (`id`),
   UNIQUE KEY `url_name` (`url_name`),
   KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='单页表';

DROP TABLE IF EXISTS `dask_users_active`;
CREATE TABLE `dask_users_active` (
       `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
       `uid` int(11) DEFAULT '0',
       `expire_time` int(10) DEFAULT NULL,
       `active_code` varchar(32) DEFAULT NULL,
       `active_type` varchar(50) DEFAULT NULL,
       `create_time` int(10) DEFAULT NULL,
       `create_valid_ip` varchar(50) DEFAULT NULL COMMENT '验证IP',
       `active_time` int(10) DEFAULT NULL,
       `active_ip` varchar(50) DEFAULT NULL COMMENT '激活IP',
       PRIMARY KEY (`id`),
       KEY `active_code` (`active_code`),
       KEY `active_type` (`active_type`),
       KEY `uid` (`uid`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='用户激活码';

DROP TABLE IF EXISTS `dask_email_log`;
CREATE TABLE `dask_email_log` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `send_to` varchar(255) NOT NULL COMMENT '发送给',
    `subject` varchar(255) NOT NULL COMMENT '邮件主题',
    `message` text NOT NULL COMMENT '邮件内容',
    `error_message` text DEFAULT NULL COMMENT '错误信息',
    `create_time` int(10) DEFAULT '0',
    `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0发送失败，1发送成功',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `send_to` (`send_to`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='邮箱记录表';

DROP TABLE IF EXISTS `dask_announce`;
CREATE TABLE `dask_announce` (
    `id` int(10) NOT NULL AUTO_INCREMENT,
    `uid` int(10) NOT NULL,
    `title` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    `message` text CHARACTER SET utf8mb4,
    `view_count` int(10) DEFAULT '0',
    `sort` tinyint(2) unsigned NOT NULL DEFAULT '0',
    `status` tinyint(1) unsigned DEFAULT '0' COMMENT '公告状态1正常0删除',
    `set_top` tinyint(1) unsigned DEFAULT '0' COMMENT '是否置顶 0不置顶1置顶',
    `set_top_time` int(11) unsigned DEFAULT '0' COMMENT '置顶时间',
    `create_time` int(10) DEFAULT '0' COMMENT '发布时间',
    `update_time` int(10) DEFAULT '0' COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `uid` (`uid`) USING BTREE,
    KEY `view_count` (`view_count`) USING BTREE,
    KEY `sort` (`sort`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4  ROW_FORMAT=DYNAMIC COMMENT='公告表';

DROP TABLE IF EXISTS `dask_admin_log`;
CREATE TABLE `dask_admin_log` (
    `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
    `uid` text NOT NULL COMMENT '管理员',
    `url` varchar(255) NOT NULL DEFAULT '' COMMENT '操作页面	',
    `title` varchar(100) NOT NULL DEFAULT '' COMMENT '日志标题',
    `content` text NOT NULL COMMENT '日志内容',
    `ip` varchar(50) NOT NULL DEFAULT '' COMMENT '操作IP',
    `user_agent` text NOT NULL COMMENT 'User-Agent',
    `create_time` int(11) NOT NULL,
    `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='管理员日志';





DROP TABLE IF EXISTS `dask_search_engine`;
CREATE TABLE `dask_search_engine` (
     `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
     `name` varchar(16) DEFAULT NULL COMMENT '索引名称/数据库名称',
     `title` varchar(32) DEFAULT NULL COMMENT '搜索标题',
     `pk` varchar(255) DEFAULT NULL COMMENT '主键字段',
     `union_sql` text COMMENT '聚合返回sql，聚合查询时需要，确保多个表字段统一；如user_name as detail',
     `search_field` varchar(255) DEFAULT NULL COMMENT '查询字段，多个用|分割',
     `search_engine` varchar(32) DEFAULT NULL COMMENT '搜索引擎，默认本地数据库',
     `result_field` text COMMENT '查询返回字段',
     `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
     `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
     PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='搜索引擎配置';

INSERT INTO `dask_search_engine` (`id`, `name`, `title`, `pk`, `union_sql`, `search_field`, `search_engine`, `result_field`, `status`, `create_time`) VALUES (1, 'question', '问题', 'id', 'title,search_text as detail', 'title|search_text', 'regexp', 'id,uid', 1, NULL);
INSERT INTO `dask_search_engine` (`id`, `name`, `title`, `pk`, `union_sql`, `search_field`, `search_engine`, `result_field`, `status`, `create_time`) VALUES (2, 'article', '文章', 'id', 'title,search_text as detail', 'title|search_text', 'regexp', 'id,uid', 1, NULL);
INSERT INTO `dask_search_engine` (`id`, `name`, `title`, `pk`, `union_sql`, `search_field`, `search_engine`, `result_field`, `status`, `create_time`) VALUES (3, 'users', '用户', 'uid', 'nick_name as title,user_name as detail', 'nick_name|user_name', 'regexp', 'uid', 1, NULL);
INSERT INTO `dask_search_engine` (`id`, `name`, `title`, `pk`, `union_sql`, `search_field`, `search_engine`, `result_field`, `status`, `create_time`) VALUES (4, 'topic', '话题', 'id', 'title,description as detail', 'title|description', 'regexp', 'id', 1, NULL);

DROP TABLE IF EXISTS `dask_forbidden_ip`;
CREATE TABLE `dask_forbidden_ip` (
   `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
   `uid` int(10) unsigned DEFAULT NULL COMMENT '用户ID',
   `ip` varchar(50) NOT NULL DEFAULT '' COMMENT '禁止IP',
   `time` int(10) unsigned DEFAULT NULL COMMENT '封禁时间',
   PRIMARY KEY (`id`),
   UNIQUE KEY `ip` (`ip`),
   KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='封禁列表';

DROP TABLE IF EXISTS `dask_users_notify_setting`;
CREATE TABLE `dask_users_notify_setting` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '通知标识',
    `title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '通知标题',
    `group` varchar(255) COLLATE utf8mb4_general_ci NOT NULL COMMENT '通知分组ID',
    `subject` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '通知主题',
    `message` mediumtext COLLATE utf8mb4_general_ci COMMENT '通知详情',
    `type` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '通知类型:site站内通知，email邮件通知,dingTalk钉钉通知,mobile短信通知,wechat微信通知,template微信模板消息,min小程序通知',
    `user_setting` tinyint(1) unsigned DEFAULT '1' COMMENT '是否允许用户配置',
    `extends` mediumtext COLLATE utf8mb4_general_ci COMMENT '其他通知相关附属设置',
    `status` tinyint(1) unsigned DEFAULT '1' COMMENT '是否启用',
    `system` tinyint(1) unsigned DEFAULT 0 COMMENT '是否系统内置',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='消息通知配置表';

INSERT INTO `dask_users_notify_setting` VALUES (1, 'BEST_ANSWER', '回答被设为最佳', 'TYPE_ANSWER_COMMENT', '您的回答被设为了最佳', '您在问题 [#title#] 中的回答被设为了最佳回复', 'site,email', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (2, 'TYPE_PEOPLE_FOCUS_ME', '有用户关注了我', 'TYPE_PEOPLE_FOCUS_ME', '[#from_username#] 关注了你', NULL, 'site,email', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (3, 'TYPE_SYSTEM_NOTIFY', '新用户注册欢迎', 'TYPE_SYSTEM_NOTIFY', '亲爱的用户您好,欢迎注册[#site_name#]', '尊敬的[#user_name#]，您已经注册成为[#site_name#]的会员，请您在发表言论时，遵守当地法律法规。如果您有什么疑问可以联系管理员。', 'site,email', 0, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (4, 'QUESTION_ANSWER', '问题有新的回复', 'TYPE_ANSWER_COMMENT', '[#from_username#]回答了您的问题', '您发表的问题 [#title#] 有新的回答', 'site,email', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (5, 'QUESTION_COMMENT_AT_ME', '有人在问题评论中@我', 'TYPE_PEOPLE_AT_ME', '[#from_username#]在问题评论中@到了您', '[#from_username#]在问题 [#title#] 评论中@到了您', 'site,email', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (6, 'QUESTION_ANSWER_COMMENT_AT_ME', '有人在问题回答评论中@我', 'TYPE_PEOPLE_AT_ME', '[#from_username#]在问题回答评论中@到了您', '[#from_username#]在问题 [#title#] 回答评论中@到了您', 'site,email', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (7, 'NEW_ANSWER_COMMENT', '有人评论了您的回答', 'TYPE_ANSWER_COMMENT', '[#from_username#]评论了您的回答', '[#from_username#]在问题 [#title#] 中评论了您的回答', 'site,email', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (8, 'INVITE_ANSWER', '有人邀请您回答问题', 'TYPE_INVITE', '[#from_username#]邀请您回答问题', '[#from_username#]邀请您回答问题 [#title#] ', 'site,email', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (9, 'NEW_ARTICLE_COMMENT', '有人评论了您的文章', 'TYPE_ANSWER_COMMENT', '[#from_username#]评论了您的文章', '[#from_username#]在文章 [#title#] 中评论了您', 'site,email', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (10, 'ARTICLE_COMMENT_AT_ME', '有人在文章评论中@我', 'TYPE_PEOPLE_AT_ME', '[#from_username#]在文章评论中@到了您', '[#from_username#]在文章 [#title#] 评论中@到了您', 'site,email', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (11, 'NEW_QUESTION_COMMENT', '有人评论了您的问题', 'TYPE_ANSWER_COMMENT', '[#from_username#]评论了您的问题', '[#from_username#]在问题 [#title#] 中评论了您', 'site,email', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (12, 'REPORT_QUESTION_HANDLE_SUCCESS', '问题举报成功反馈', 'TYPE_SYSTEM_NOTIFY', '您举报的问题已处理反馈', '亲爱的用户您好,您举报的问题 [#title#] 经审核确认存在违规行为,现已对该问题进行删除处理！ [#site_name#] 非常感谢您的反馈！', 'site,email', 0, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (13, 'QUESTION_REPORT_HANDLE_SUCCESS', '问题被举报成功反馈', 'TYPE_SYSTEM_NOTIFY', '您的问题被举报反馈', '亲爱的用户您好,您发表的问题 [#title#] 经审核确认存在违规行为,现已对该问题进行删除处理！[#site_name#] 希望能够遵守社区规则进行发问', 'site,email', 0, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (14, 'REPORT_ANSWER_HANDLE_SUCCESS', '回答举报成功反馈', 'TYPE_SYSTEM_NOTIFY', '您举报的回答已处理反馈', '亲爱的用户您好,您举报在问题 [#title#] 中的回答 经审核确认存在违规行为,现已对该问题回答进行删除处理！ [#site_name#] 非常感谢您的反馈！', 'site,email', 0, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (15, 'ANSWER_REPORT_HANDLE_SUCCESS', '回答被举报成功反馈', 'TYPE_SYSTEM_NOTIFY', '您的回答被举报反馈', '亲爱的用户您好,您在问题 [#title#] 中发表的回答 经审核确认存在违规行为,现已对该问题回答进行删除处理！[#site_name#] 希望能够遵守社区规则进行回答', 'site,email', 0, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (16, 'REPORT_ARTICLE_HANDLE_SUCCESS', '文章举报成功反馈', 'TYPE_SYSTEM_NOTIFY', '您举报的文章处理反馈', '亲爱的用户您好,您举报的文章 [#title#] 经审核确认存在违规行为,现已对该文章进行删除处理！ [#site_name#] 非常感谢您的反馈！', 'site,email', 0, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (17, 'ARTICLE_REPORT_HANDLE_SUCCESS', '文章被举报成功反馈', 'TYPE_SYSTEM_NOTIFY', '您的文章被举报删除反馈', '亲爱的用户您好,您发表的文章 [#title#] 经审核确认存在违规行为,现已对该文章进行删除处理！[#site_name#] 希望能够遵守社区规则进行回答', 'site,email', 0, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (18, 'REPORT_ARTICLE_COMMENT_HANDLE_SUCCESS', '文章评论举报成功反馈', 'TYPE_SYSTEM_NOTIFY', '您举报的文章评论处理反馈', '亲爱的用户您好,您举报在文章中 [#title#] 的评论 经审核确认存在违规行为,现已对该文章评论进行删除处理！ [#site_name#] 非常感谢您的反馈！', 'site,email', 0, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (19, 'ARTICLE_COMMENT_REPORT_HANDLE_SUCCESS', '文章评论被举报成功反馈', 'TYPE_SYSTEM_NOTIFY', '您的文章评论被举报反馈', '亲爱的用户您好,您在文章中 [#title#] 的评论 经审核确认存在违规行为,现已对该文章评论进行删除处理！[#site_name#] 希望能够遵守社区规则进行回答', 'site,email', 0, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (20, 'REPORT_QUESTION_HANDLE_MODIFY', '问题举报修改反馈', 'TYPE_SYSTEM_NOTIFY', '您举报的问题已处理反馈', '亲爱的用户您好,您举报的问题 [#title#] 经审核确认存在违规行为,现已对该问题进行修改处理！ [#site_name#] 非常感谢您的反馈！', 'site,email', 0, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (21, 'QUESTION_REPORT_HANDLE_MODIFY', '问题被举报修改反馈', 'TYPE_SYSTEM_NOTIFY', '您的问题被举报反馈', '亲爱的用户您好,您发表的问题 [#title#] 经审核确认存在违规行为,现已对该问题进行修改处理！[#site_name#] 希望能够遵守社区规则进行发问', 'site,email', 0, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (22, 'REPORT_ANSWER_HANDLE_MODIFY', '回答举报修改反馈', 'TYPE_SYSTEM_NOTIFY', '您举报的回答已处理反馈', '亲爱的用户您好,您举报在问题 [#title#] 中的回答 经审核确认存在违规行为,现已对该问题回答进行修改处理！ [#site_name#] 非常感谢您的反馈！', 'site,email', 0, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (23, 'ANSWER_REPORT_HANDLE_MODIFY', '回答被举报修改反馈', 'TYPE_SYSTEM_NOTIFY', '您的回答被举报反馈', '亲爱的用户您好,您在问题 [#title#] 中发表的回答 经审核确认存在违规行为,现已对该问题回答进行修改处理！[#site_name#] 希望能够遵守社区规则进行回答', 'site,email', 0, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (24, 'REPORT_ARTICLE_HANDLE_MODIFY', '文章举报修改反馈', 'TYPE_SYSTEM_NOTIFY', '您举报的文章已处理反馈', '亲爱的用户您好,您举报的文章 [#title#] 经审核确认存在违规行为,现已对该文章进行修改处理！ [#site_name#] 非常感谢您的反馈！', 'site,email', 0, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (25, 'ARTICLE_REPORT_HANDLE_MODIFY', '文章被举报修改反馈', 'TYPE_SYSTEM_NOTIFY', '您的文章被举报反馈', '亲爱的用户您好,您发表的文章 [#title#] 经审核确认存在违规行为,现已对该文章进行修改处理！[#site_name#] 希望能够遵守社区规则进行回答', 'site,email', 0, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (26, 'REPORT_ARTICLE_COMMENT_HANDLE_MODIFY', '文章评论举报修改反馈', 'TYPE_SYSTEM_NOTIFY', '您举报的文章评论已处理反馈', '亲爱的用户您好,您举报在文章中 [#title#] 的评论 经审核确认存在违规行为,现已对该文章评论进行修改处理！ [#site_name#] 非常感谢您的反馈！', 'site,email', 0, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (27, 'ARTICLE_COMMENT_REPORT_HANDLE_MODIFY', '文章评论被举报修改反馈', 'TYPE_SYSTEM_NOTIFY', '您的文章评论被举报反馈', '亲爱的用户您好,您在文章中 [#title#] 的评论 经审核确认存在违规行为,现已对该文章评论进行修改处理！[#site_name#] 希望能够遵守社区规则进行回答', 'site,email', 0, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (28, 'REPORT_HANDLE_DECLINE', '举报失败提醒', 'TYPE_SYSTEM_NOTIFY', '您举报处理反馈', '', 'site,email', 0, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (29, 'AGREE_CONTENT', '有用户点赞了我', 'TYPE_AGREE', '有用户点赞了您的内容', '[#from_username#] 点赞了您的内容 [#title#] ', 'site,email', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (30, 'TYPE_QUESTION_APPROVAL', '问题审核通过', 'TYPE_SYSTEM_NOTIFY', '我发表的问题审核通过', '亲爱的用户您好,您发表的问题 [#title#] 已审核通过！', 'site', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (31, 'TYPE_ARTICLE_APPROVAL', '文章审核通过', 'TYPE_SYSTEM_NOTIFY', '我发表的文章审核通过', '亲爱的用户您好,您发表的文章 [#title#] 已审核通过！', 'site', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (32, 'TYPE_QUESTION_DECLINE', '问题审核拒绝', 'TYPE_SYSTEM_NOTIFY', '我发表的问题审核未通过', '亲爱的用户您好,您发表的问题 [#title#] 未审核通过！', 'site', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (33, 'TYPE_ARTICLE_DECLINE', '文章审核拒绝', 'TYPE_SYSTEM_NOTIFY', '我发表的文章审核未通过', '亲爱的用户您好,您发表的文章 [#title#] 未审核通过！', 'site', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (34, 'TYPE_QUESTION_MODIFY_APPROVAL', '问题修改审核通过', 'TYPE_SYSTEM_NOTIFY', '我修改的问题审核通过', '亲爱的用户您好,您修改的问题 [#title#] 已审核通过！', 'site', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (35, 'TYPE_ARTICLE_MODIFY_APPROVAL', '文章修改审核通过', 'TYPE_SYSTEM_NOTIFY', '我修改的文章审核通过', '亲爱的用户您好,您修改的文章 [#title#] 已审核通过！', 'site', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (36, 'TYPE_ANSWER_APPROVAL', '回答审核通过', 'TYPE_SYSTEM_NOTIFY', '我发表的回答审核通过', '亲爱的用户您好,您在问题 [#title#] 发表的回答已审核通过！', 'site', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (37, 'TYPE_ANSWER_MODIFY_APPROVAL', '回答修改审核通过', 'TYPE_SYSTEM_NOTIFY', '我修改的回答审核通过', '亲爱的用户您好,您在问题 [#title#] 修改的回答已审核通过！', 'site', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (38, 'TYPE_QUESTION_MODIFY_DECLINE', '问题修改审核未通过', 'TYPE_SYSTEM_NOTIFY', '我修改的问题审核未通过', '亲爱的用户您好,您修改的问题 [#title#] 未审核通过！', 'site', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (39, 'TYPE_ARTICLE_MODIFY_DECLINE', '文章修改审核未通过', 'TYPE_SYSTEM_NOTIFY', '我修改的文章审核未通过', '亲爱的用户您好,您修改的文章 [#title#] 未审核通过！', 'site', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (40, 'TYPE_ANSWER_DECLINE', '回答审核未通过', 'TYPE_SYSTEM_NOTIFY', '我发表的回答审核未通过', '亲爱的用户您好,您在问题 [#title#] 发表的回答未审核通过！', 'site', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (41, 'TYPE_ANSWER_MODIFY_DECLINE', '回答修改审核未通过', 'TYPE_SYSTEM_NOTIFY', '我修改的回答审核未通过', '亲爱的用户您好,您在问题 [#title#] 修改的回答未审核通过！', 'site', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (42, 'TYPE_COLUMN_APPROVAL', '申请专栏审核通过', 'TYPE_SYSTEM_NOTIFY', '我申请专栏审核通过', '亲爱的用户您好,您申请的专栏 [#title#] 已审核通过！', 'site', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (43, 'TYPE_COLUMN_DECLINE', '申请专栏审核未通过', 'TYPE_SYSTEM_NOTIFY', '我申请专栏审核未通过', '亲爱的用户您好,您申请的专栏 [#title#] 审核未通过！', 'site', 1, NULL, 1, 1);
INSERT INTO `dask_users_notify_setting` VALUES (44, 'DIY_NOTIFY', '自定义通知', 'TYPE_SYSTEM_NOTIFY', '', '', 'site', 0, NULL, 1, 0);


DROP TABLE IF EXISTS `dask_search_log`;
CREATE TABLE `dask_search_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned DEFAULT NULL COMMENT '搜索用户id',
  `ip` varchar(255) DEFAULT NULL COMMENT '搜索IP地址',
  `keyword` varchar(255) DEFAULT NULL COMMENT '搜索关键词',
  `user_agent` varchar(500) DEFAULT NULL COMMENT '浏览器标识',
  `from` varchar(255) DEFAULT NULL COMMENT '来源页面',
  `create_time` int(11) DEFAULT NULL COMMENT '搜索时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='搜索记录表';

DROP TABLE IF EXISTS `dask_users_favorite_focus`;
CREATE TABLE `dask_users_favorite_focus` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
    `tag_id` int(11) DEFAULT NULL COMMENT '收藏夹ID',
    `uid` int(11) DEFAULT NULL COMMENT '用户UID',
    `create_time` int(11) DEFAULT NULL COMMENT '添加时间',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `uid` (`uid`) USING BTREE,
    KEY `tag_id` (`tag_id`) USING BTREE,
    KEY `tag_uid` (`tag_id`,`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='收藏关注表';

DROP TABLE IF EXISTS `dask_app_token`;
CREATE TABLE `dask_app_token` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `title` varchar(255) DEFAULT NULL COMMENT '客户端名称',
     `token` varchar(32) DEFAULT NULL COMMENT '客户端Token',
     `version` varchar(32) DEFAULT NULL COMMENT '模块客户端版本',
     `plugin` varchar(255) NULL COMMENT '对应插件',
     `type` tinyint(1) UNSIGNED NULL DEFAULT 1 COMMENT '1系统模块，2插件',
     `create_time` int(11) DEFAULT '0' COMMENT '创建时间',
     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='接口请求表';

# 2022-06-15

DROP TABLE IF EXISTS `dask_topic_related`;
CREATE TABLE `dask_topic_related` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `source_id` int(11) NOT NULL DEFAULT '0' COMMENT '原话题ID',
   `target_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联话题ID',
   PRIMARY KEY (`id`) USING BTREE,
   KEY `source_id` (`source_id`) USING BTREE,
   KEY `target_id` (`target_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='话题关联表';

DROP TABLE IF EXISTS `dask_topic_merge`;
CREATE TABLE `dask_topic_merge` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `source_id` int(11) NOT NULL DEFAULT '0' COMMENT '原话题ID',
    `target_id` int(11) NOT NULL DEFAULT '0' COMMENT '目标话题ID',
    `uid` int(11) DEFAULT '0' COMMENT '合并用户',
    `create_time` int(10) DEFAULT '0',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `source_id` (`source_id`) USING BTREE,
    KEY `target_id` (`target_id`) USING BTREE,
    KEY `uid` (`uid`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='话题合并表';

DROP TABLE IF EXISTS `dask_browse_records`;
CREATE TABLE `dask_browse_records` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `uid` int(10) unsigned DEFAULT '0' COMMENT '用户UID',
      `item_id` int(10) unsigned DEFAULT '0' COMMENT '内容ID',
      `item_type` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '内容类型，question问题，article文章，topic话题,column专栏',
      `status` tinyint(10) unsigned DEFAULT '1' COMMENT '1正常0删除',
      `create_time` int(10) unsigned DEFAULT '0' COMMENT '添加时间',
      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='浏览记录表';

DROP TABLE IF EXISTS `dask_question_redirect`;
CREATE TABLE `dask_question_redirect` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `item_id` int(11) DEFAULT '0',
    `target_id` int(11) DEFAULT '0',
    `uid` int(11) DEFAULT NULL,
    `create_time` int(10) DEFAULT '0',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `item_id` (`item_id`) USING BTREE,
    KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='重定向表';

DROP TABLE IF EXISTS `dask_column_recommend_article`;
CREATE TABLE `dask_column_recommend_article` (
    `id` int(10) NOT NULL AUTO_INCREMENT,
    `uid` int(10) unsigned DEFAULT '0' COMMENT '推荐用户',
    `column_id` int(10) unsigned DEFAULT '0' COMMENT '专栏id',
    `article_id` int(10) unsigned DEFAULT '0' COMMENT '推荐文章id',
    `status` tinyint(255) unsigned DEFAULT '0' COMMENT '审核状态0待审核1已审核2已拒绝',
    `reason` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '拒绝理由',
    `create_time` int(10) unsigned DEFAULT NULL,
    `update_time` int(10) unsigned DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='专栏推荐文章表';


DROP TABLE IF EXISTS `dask_help_chapter`;
CREATE TABLE `dask_help_chapter` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(100) NOT NULL COMMENT '章节标题',
    `description` text COMMENT '章节描述',
    `url_token` varchar(32) DEFAULT NULL COMMENT '章节别名',
    `image` varchar(255) DEFAULT NULL COMMENT '章节图标',
    `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '章节排序',
    `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `title` (`title`) USING BTREE,
    KEY `url_token` (`url_token`) USING BTREE,
    KEY `sort` (`sort`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='帮助章节';

DROP TABLE IF EXISTS `dask_help_chapter_relation`;
CREATE TABLE `dask_help_chapter_relation` (
     `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     `chapter_id` int(10) unsigned DEFAULT '0' COMMENT '章节ID',
     `item_type` varchar(100) NOT NULL COMMENT '关联类型',
     `item_id` int(10) unsigned DEFAULT '0' COMMENT '关联ID',
     `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
     `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '章节排序',
     PRIMARY KEY (`id`) USING BTREE,
     KEY `chapter_id` (`chapter_id`) USING BTREE,
     KEY `sort` (`sort`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='帮助内容关联表';

DROP TABLE IF EXISTS `dask_feature`;
CREATE TABLE `dask_feature` (
       `id` int(11) NOT NULL AUTO_INCREMENT,
       `title` varchar(200) DEFAULT NULL COMMENT '专题标题',
       `description` varchar(255) DEFAULT NULL COMMENT '专题描述',
       `image` varchar(255) DEFAULT NULL COMMENT '专题封面',
       `topic_count` int(11) DEFAULT '0' COMMENT '话题数量',
       `css` text COMMENT '自定义css样式文件',
       `url_token` varchar(32) DEFAULT NULL COMMENT '专题别名',
       `seo_title` varchar(255) DEFAULT NULL COMMENT '专题SEO标题',
       `seo_keywords` varchar(255) DEFAULT NULL COMMENT '专题SEO关键词',
       `seo_description` varchar(255) DEFAULT NULL COMMENT '专题SEO描述',
       `status` tinyint(1) NOT NULL DEFAULT '0',
       PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='专题表';

DROP TABLE IF EXISTS `dask_feature_topic`;
CREATE TABLE `dask_feature_topic` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `feature_id` int(11) DEFAULT '0' COMMENT '专题ID',
     `topic_id` int(11) DEFAULT '0' COMMENT '话题ID',
     PRIMARY KEY (`id`) USING BTREE,
     KEY `feature_id` (`feature_id`) USING BTREE,
     KEY `topic_id` (`topic_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='专题话题关联表';



-- 主题风格（group 12）：前台展示配置项，后台「系统配置 → 主题风格」维护


-- 短信配置（group 14）：由 plugins/sms 迁入核心配置，默认关闭。
CREATE TABLE IF NOT EXISTS `dask_sms_log` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `mobile` char(11) NOT NULL COMMENT '手机号',
  `send_type` varchar(32) NOT NULL COMMENT '短信商',
  `template_code` varchar(32) NOT NULL COMMENT '模板id',
  `content` text COMMENT '短信内容',
  `ip` varchar(32) NOT NULL COMMENT 'ip',
  `create_time` int DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='短信记录表';

-- 编辑器（group 13）：系统内置 Vditor 行为全部由后台配置驱动

ALTER TABLE `dask_question` ADD INDEX `idx_q_status_type_time` (`status`,`question_type`,`create_time`);
ALTER TABLE `dask_question` ADD INDEX `idx_q_status_cat_time` (`status`,`category_id`,`create_time`);
ALTER TABLE `dask_question` ADD INDEX `idx_q_status_popular` (`status`,`popular_value`);
ALTER TABLE `dask_question` ADD INDEX `idx_q_uid_status_time` (`uid`,`status`,`create_time`);
ALTER TABLE `dask_answer` ADD INDEX `idx_a_q_hot` (`question_id`,`status`,`is_best`,`agree_count`,`comment_count`);
ALTER TABLE `dask_answer` ADD INDEX `idx_a_uid_status_time` (`uid`,`status`,`create_time`);
ALTER TABLE `dask_topic_relation` ADD INDEX `idx_tr_topic` (`topic_id`,`item_type`,`status`);
ALTER TABLE `dask_topic_relation` ADD INDEX `idx_tr_item` (`item_type`,`item_id`,`status`);

ALTER TABLE `dask_users_notify` ADD INDEX `idx_n_recipient` (`recipient_uid`,`read_flag`,`status`,`create_time`);
ALTER TABLE `dask_action_log` ADD INDEX `idx_al_uid_action` (`uid`,`action_id`,`status`);
ALTER TABLE `dask_users_favorite` ADD INDEX `idx_fav_uid_item` (`uid`,`item_type`,`item_id`);
ALTER TABLE `dask_post_relation` ADD INDEX `idx_pr_type_status_time` (`item_type`,`status`,`create_time`);
ALTER TABLE `dask_post_relation` ADD INDEX `idx_pr_item` (`item_id`,`item_type`);
ALTER TABLE `dask_search_log` ADD INDEX `idx_sl_time` (`create_time`);

-- 删除被复合索引左前缀覆盖的冗余单列索引，保持新装与升级后结构一致。
DROP PROCEDURE IF EXISTS `dask_drop_index_600`;
DELIMITER $$
CREATE PROCEDURE `dask_drop_index_600`(IN t VARCHAR(64), IN idx VARCHAR(64))
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = DATABASE() AND table_name = t AND index_name = idx
    ) THEN
        SET @ddl = CONCAT('ALTER TABLE `', t, '` DROP INDEX `', idx, '`');
        PREPARE stmt FROM @ddl;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

CALL dask_drop_index_600('dask_action_log', 'uid');
CALL dask_drop_index_600('dask_answer', 'question_id');
CALL dask_drop_index_600('dask_answer', 'uid');
CALL dask_drop_index_600('dask_column_focus', 'column_id');
CALL dask_drop_index_600('dask_post_relation', 'item_id');
CALL dask_drop_index_600('dask_post_relation', 'item_type');
CALL dask_drop_index_600('dask_question', 'uid');
CALL dask_drop_index_600('dask_question_focus', 'question_id');
CALL dask_drop_index_600('dask_topic_focus', 'topic_id');
CALL dask_drop_index_600('dask_topic_relation', 'item_type');
CALL dask_drop_index_600('dask_topic_relation', 'topic_id');
CALL dask_drop_index_600('dask_users_favorite', 'uid');
CALL dask_drop_index_600('dask_users_favorite_focus', 'tag_id');
CALL dask_drop_index_600('dask_users_follow', 'fans_uid');
CALL dask_drop_index_600('dask_users_notify', 'recipient_read_flag');
DROP PROCEDURE IF EXISTS `dask_drop_index_600`;

-- 14. 第三方登录转为系统功能：配置项统一存入 dask_config，后台「系统配置 → 第三方登录」维护。

-- 主题风格升级为系统管理一级入口；第三方登录绑定记录并入用户管理模块（不再单独一级入口）。
INSERT IGNORE INTO `dask_admin_auth` (`id`, `pid`, `name`, `title`, `type`, `status`, `condition`, `sort`, `auth_open`, `icon`, `create_time`, `update_time`, `param`, `group`) VALUES
(410, 2, 'admin.Config/theme', '主题风格', 1, 1, '', 0, 1, 'fas fa-palette', 0, 0, '', NULL),
(411, 2, 'admin.Config/editor', '编辑器', 1, 1, '', 1, 1, 'fas fa-code', 0, 0, '', NULL),
(412, 2, 'admin.Config/sms', '短信配置', 1, 1, '', 2, 1, 'fas fa-comment-sms', 0, 0, '', NULL),
(413, 50, 'member.Users/third', 'OAuth登录', 1, 0, '', 10, 1, 'fas fa-key', 0, 0, '', NULL),
(414, 413, 'member.Users/third_delete', '操作-删除', 1, 0, '', 0, 1, 'fa fa-trash', 0, 0, '', NULL);


-- 15. 初始化展示类与安全审计类功能开关，便于呈现完整站点（站点开关保持默认）。
UPDATE `dask_config` SET value = 'Y' WHERE name IN (
  'enable_column', 'enable_multilingual', 'attach_enable', 'remove_xss', 'record_admin_log'
);
UPDATE `dask_config` SET value = 'qq,wechat,weibo,github,google,twitter' WHERE name = 'third_enable';

SET FOREIGN_KEY_CHECKS = 1;


