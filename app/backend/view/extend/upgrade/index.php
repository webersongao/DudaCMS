{extend name="block" /}
{block name="main"}
<div class="container-fluid p-3">
    <div class="row">
        <div class="col-xl-8">
            <!-- 当前版本 -->
            <div class="card card-rounded">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle text-primary me-1"></i> 当前版本</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                    <table class="table table-hover border-0 mb-0 dask-soft-lines" style="min-width: 420px">
                        <tbody>
                            <tr>
                                <td style="width: 180px">程序版本</td>
                                <td><span class="badge text-bg-primary">DudaCMS V{$info.version}</span>
                                    {if $info.build}<span class="text-muted ms-2">Build {$info.build}</span>{/if}</td>
                            </tr>
                            <tr>
                                <td>数据库版本</td>
                                <td>
                                    <span class="badge {if $needs_upgrade}text-bg-warning{else/}text-bg-info{/if}">V{$info.db_version}</span>
                                    {if $needs_upgrade}<span class="text-warning ms-2 small"><i class="fas fa-exclamation-triangle me-1"></i>落后于程序版本，建议升级</span>{/if}
                                </td>
                            </tr>
                            <tr>
                                <td>升级方式</td>
                                <td>在线升级：升级 SQL 随代码同步，本页自动检测相邻版本；不连接任何外部服务器。</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>

            <!-- 待升级提示 -->
            <div class="card card-rounded mt-3 {if $pending}border-left border-warning{/if}" {if $pending}style="border-left-width:4px!important"{/if}>
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-arrow-circle-up text-primary me-1"></i> 数据库升级</h3>
                </div>
                <div class="card-body">
                    {if $pending}
                    <div class="alert alert-warning">
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold mb-1">检测到数据库需要升级</div>
                                <div class="small">
                                    从 <span class="badge text-bg-secondary">V{$info.db_version}</span>
                                    升级到 <span class="badge text-bg-warning">V{$pending.version}</span>
                                    {if $pending.description}<div class="mt-2 text-muted">{$pending.description}</div>{/if}
                                    <div class="mt-1 text-muted">SQL 文件：{$pending.file} · 更新于 {$pending.mtime}</div>
                                </div>
                            </div>
                            <div class="mt-2 mt-md-0">
                                <button type="button" class="btn btn-warning" id="btn-run-upgrade" data-version="{$pending.version}" {if ($lock && !$lock.stale) || !$preflight.ok}disabled{/if}>
                                    <i class="fas fa-play me-1"></i>立即升级到 V{$pending.version}
                                </button>
                            </div>
                        </div>
                        <div class="mt-2 mb-0 small text-muted"><i class="fas fa-shield-alt me-1"></i>升级前将自动全库备份；失败自动回滚。请勿在升级过程中关闭页面。</div>
                        {if $lock && !$lock.stale}
                        <div class="alert alert-danger mt-2 mb-0 py-2 small"><i class="fas fa-lock me-1"></i>检测到另一个升级任务正在进行（已持续 {$lock.age} 秒），暂不可升级。</div>
                        {/if}
                        {if !$preflight.ok}
                        <div class="alert alert-warning mt-2 mb-0 py-2 small">
                            <i class="fas fa-exclamation-circle me-1"></i>升级前预检未通过：
                            {volist name="preflight.checks" id="c"}
                                {if !$c.ok}<span class="badge text-bg-danger me-1">{$c.label}</span>{/if}
                            {/volist}
                        </div>
                        {/if}
                    </div>
                    {else/}
                    <div class="alert alert-success mb-0"><i class="fas fa-check-circle me-1"></i>数据库已是最新版本，无需升级。</div>
                    {/if}

                    {if $files}
                    <div class="table-responsive mt-3">
                        <table class="table table-hover mb-0" style="min-width: 560px">
                            <thead>
                                <tr>
                                    <th>版本</th>
                                    <th>说明</th>
                                    <th>SQL 文件</th>
                                    <th>状态</th>
                                </tr>
                            </thead>
                            <tbody>
                                {volist name="files" id="f"}
                                <tr>
                                    <td><span class="badge text-bg-light">V{$key}</span></td>
                                    <td>{$f.description ?: '—'}</td>
                                    <td><code>{$f.file}</code></td>
                                    <td><span class="badge {$f.status_class}">{$f.status_label}</span></td>
                                </tr>
                                {/volist}
                            </tbody>
                        </table>
                    </div>
                    {/if}
                </div>
            </div>

            <!-- 升级记录 -->
            <div class="card card-rounded mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history text-primary me-1"></i> 升级记录</h3>
                </div>
                <div class="card-body">
                    {if $logs}
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="min-width: 480px">
                            <thead>
                                <tr>
                                    <th>版本</th>
                                    <th>状态</th>
                                    <th>说明</th>
                                    <th>时间</th>
                                </tr>
                            </thead>
                            <tbody>
                                {volist name="logs" id="log"}
                                <tr>
                                    <td><span class="badge text-bg-light">V{$log.version}</span></td>
                                    <td>
                                        {if $log.status == 1}<span class="badge text-bg-success">成功</span>
                                        {elseif $log.status == 2 /}<span class="badge text-bg-danger">失败</span>
                                        {else/}<span class="badge text-bg-secondary">进行中</span>{/if}
                                    </td>
                                    <td>{$log.remark}</td>
                                    <td>{$log.add_time|date='Y-m-d H:i:s'}</td>
                                </tr>
                                {/volist}
                            </tbody>
                        </table>
                    </div>
                    {else/}
                    <div class="alert alert-light border mb-0">暂无升级记录。</div>
                    {/if}
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card card-rounded border-left border-primary" style="border-left-width:4px!important">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-circle-info text-primary me-1"></i> 升级说明</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3" style="font-size:13px">
                        SQL 升级文件位于 <code>install/sql/upgrade/</code>，随代码一起同步。同步后刷新本页即可看到待升级版本。
                    </p>
                    <p class="text-muted mb-3" style="font-size:13px">
                        升级按版本逐级进行，不支持跨版本；每次升级前自动全库备份，失败自动回滚。
                    </p>
                    <p class="text-muted mb-3" style="font-size:13px">
                        全新安装使用 <code>install/sql/install.sql</code>；数据库备份与还原可在上方「数据库备份」「数据库还原」页签操作。
                    </p>
                    <div class="text-muted mt-2" style="font-size:12px">升级前建议手动再备份一次数据库与 <code>public/storage</code>。</div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var btn = document.getElementById('btn-run-upgrade');
    if (!btn) { return; }
    btn.addEventListener('click', function () {
        if (typeof layer === 'undefined') {
            if (confirm('确定立即升级到 V' + btn.dataset.version + '？')) { doRun(); }
            return;
        }
        layer.confirm('确定将数据库升级到 V' + btn.dataset.version + '？<br><small>升级前将自动全库备份，失败自动回滚。</small>', {icon: 3, title: '在线升级确认'}, function (index) {
            layer.close(index);
            doRun();
        });
        function doRun() {
            var load = (typeof layer !== 'undefined') ? layer.load(2, {shade: [0.3, '#000']}) : null;
            var fd = new FormData();
            fd.append('version', btn.dataset.version);
            fetch('{:url("extend.Upgrade/run")}', {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            })
                .then(function (r) {
                    // 若登录过期/被权限拦截，服务端会返回 HTML 跳转页。
                    // 直接 r.json() 会得到 Unexpected token '<'，这里提前拦截并给出可读提示。
                    var ct = r.headers.get('content-type') || '';
                    if (ct.indexOf('application/json') === -1 && ct.indexOf('+json') === -1) {
                        return r.text().then(function (txt) {
                            var hint = /login|登录/i.test(txt)
                                ? '登录状态已失效，请刷新页面重新登录后再升级。'
                                : '服务端返回了非预期响应（可能是权限不足或页面异常），请刷新升级页后重试。';
                            throw new Error(hint);
                        });
                    }
                    return r.json();
                })
                .then(function (res) {
                    if (load) { layer.close(load); }
                    var ok = res.code === 1 || res.error === 0 || res.code === 200;
                    if (ok) {
                        var msg = res.msg || '升级成功';
                        if (typeof layer !== 'undefined') { layer.msg(msg, {icon: 1}, function () { location.reload(); }); }
                        else { alert(msg); location.reload(); }
                    } else {
                        var emsg = res.msg || res.info || '升级失败';
                        if (typeof layer !== 'undefined') { layer.alert(emsg, {icon: 2}); }
                        else { alert(emsg); }
                    }
                })
                .catch(function (e) {
                    if (load) { layer.close(load); }
                    var m = '请求失败：' + e.message;
                    if (typeof layer !== 'undefined') { layer.alert(m, {icon: 2}); } else { alert(m); }
                });
        }
    });
})();
</script>
{/block}
