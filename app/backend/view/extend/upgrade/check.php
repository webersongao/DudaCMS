{extend name="block" /}
{block name="main"}
<div class="container-fluid p-3">
    <div class="card card-rounded">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-search text-primary me-1"></i> 本地升级包检测</h3>
        </div>
        <div class="card-body">
            <table class="table table-hover border-0 mb-4">
                <tbody>
                <tr>
                    <td style="width: 180px">升级包文件</td>
                    <td><code>{$package.file}</code></td>
                </tr>
                <tr>
                    <td>目标版本</td>
                    <td><span class="badge text-bg-primary">V{$package.version}</span>
                        {if $package.build}<span class="text-muted ms-2">Build {$package.build}</span>{/if}</td>
                </tr>
                <tr>
                    <td>当前版本</td>
                    <td><span class="badge text-bg-info">V{$info.version}</span></td>
                </tr>
                <tr>
                    <td>升级内容</td>
                    <td>
                        {if $package.has_sql}<span class="badge text-bg-info">数据库脚本</span>{/if}
                        {if $package.has_script}<span class="badge text-bg-info">程序文件</span>{/if}
                        {if $package.has_program}<span class="badge text-bg-warning">升级程序</span>{/if}
                        {if !$package.has_sql && !$package.has_script && !$package.has_program}
                        <span class="text-muted">仅更新版本标记</span>
                        {/if}
                    </td>
                </tr>
                {if $package.description}
                <tr>
                    <td>版本说明</td>
                    <td>{$package.description}</td>
                </tr>
                {/if}
                </tbody>
            </table>

            <div class="alert alert-warning">
                <strong>升级前请确认：</strong>已备份数据库和 <code>public/storage</code>；升级过程中不要关闭浏览器。
                升级会按 SQL → 升级程序 → 程序文件 → 版本文件的顺序执行，失败时数据库变更会回滚。
            </div>

            <form method="post" action="{:url('apply')}">
                <input type="hidden" name="file" value="{$package.file}">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="force" value="1" id="force-upgrade">
                    <label class="form-check-label" for="force-upgrade">
                        强制应用（目标版本不高于当前版本时勾选）
                    </label>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-arrow-circle-up me-1"></i>确认升级</button>
                <a class="btn btn-outline-secondary ms-2" href="{:url('index')}">返回升级管理</a>
            </form>
        </div>
    </div>
</div>
{/block}
