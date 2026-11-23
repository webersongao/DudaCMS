-- +----------------------------------------------------------------------
-- | DudaCMS 1.0.0 -> 后续版本 升级 SQL：合并「系统拓展」三项为「系统运维」
-- +----------------------------------------------------------------------
-- 用途：把后台侧边栏「本地升级 / 数据库备份 / 数据库还原」三项合并为
--       单一「系统运维」页面（extend.System/index），三项作为页内 Tab。
--
-- 使用方式（二选一）：
--   A. 作为本地升级包的一部分：把本文件内容作为升级包 sql/update.sql，
--      后台上传后由升级程序在事务内自动执行（dask_ 前缀会被自动替换）。
--   B. 手动执行：先整库备份，再在线上数据库执行本文件（按实际前缀替换 dask_）。
--
-- 幂等性：全部使用 INSERT ... ON DUPLICATE KEY / 条件 UPDATE，可重复执行。
-- 注意：本 SQL 只改权限/菜单数据，不改动任何业务数据。
-- +----------------------------------------------------------------------

-- 1) 新增「系统运维」菜单（id=389，父菜单=4 系统拓展，status=1 显示）
--    若 id 389 已被占用（极端情况），请将本文件所有 389 替换为其他空闲 id。
INSERT INTO `dask_admin_auth`
    (`id`, `pid`, `name`, `title`, `type`, `status`, `condition`, `sort`, `auth_open`, `icon`, `create_time`, `update_time`, `param`, `group`)
VALUES
    (389, 4, 'extend.System/index', '系统运维', 1, 1, '', 30, 1, 'fa fa-tools', 0, 0, '', 'system')
ON DUPLICATE KEY UPDATE
    `pid` = 4, `name` = 'extend.System/index', `title` = '系统运维',
    `status` = 1, `sort` = 30, `icon` = 'fa fa-tools';

-- 2) 三条旧菜单降为权限项（status=0 不再显示在侧边栏），并挂到新菜单下
UPDATE `dask_admin_auth` SET `pid` = 389, `status` = 0 WHERE `id` IN (77, 81, 118);

-- 3) 修复 downFile 权限规则命名（snake_case 校验匹配）
UPDATE `dask_admin_auth` SET `name` = 'extend.Database/downfile' WHERE `id` = 83 AND `name` = 'extend.Database/downFile';

-- 4) 前台管理员（group id=2）权限：清幽灵ID、补新菜单 389 与还原权限 81
UPDATE `dask_admin_group`
SET `rules` = REPLACE(`rules`, '118,119,120,124,125,121,122,123', '118,119,120,124,389,81')
WHERE `id` = 2 AND `rules` LIKE '%125,121,122,123%';

-- 事后核对（手动执行时可运行，升级包内忽略）：
-- SELECT id,pid,name,title,status FROM `dask_admin_auth` WHERE id IN (77,81,83,118,389) ORDER BY id;
-- 预期：389 显示(status=1)；77/81/118 隐藏(status=0) 且 pid=389；83 name 含 downfile。
