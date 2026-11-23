{extend name="block" /}
{block name="main"}
<div class="container-fluid p-3">
    <div class="row">
        <div class="col-xl-8">
            <div class="card card-rounded">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle text-primary me-1"></i> 当前版本</h3>
                </div>
                <div class="card-body">
                    <table class="table table-hover border-0 mb-0 dask-soft-lines">
                        <tbody>
                            <tr>
                                <td style="width: 180px">程序版本</td>
                                <td><span class="badge text-bg-primary">DudaCMS V{$info.version}</span>
                                    {if $info.build}<span class="text-muted ms-2">Build {$info.build}</span>{/if}</td>
                            </tr>
                            <tr>
                                <td>数据库版本</td>
                                <td><span class="badge text-bg-info">V{$info.db_version}</span></td>
                            </tr>
                            <tr>
                                <td>升级方式</td>
                                <td>仅本地升级包；本页不会连接任何外部服务器。</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card card-rounded mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-box-open text-primary me-1"></i> 本地版本升级包</h3>
                </div>
                <div class="card-body">
                    {if $packages}
                    <div class="table-responsive">
                        <table class="table table-hover mb-3">
                            <thead>
                                <tr>
                                    <th>版本</th>
                                    <th>说明</th>
                                    <th>内容</th>
                                    <th>上传时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                {volist name="packages" id="pkg"}
                                <tr>
                                    <td><span class="badge text-bg-primary">V{$pkg.version}</span>
                                        {if $pkg.build}<div class="text-muted small">Build {$pkg.build}</div>{/if}</td>
                                    <td>{$pkg.description ?: '—'}</td>
                                    <td>
                                        {if $pkg.has_sql}<span class="badge text-bg-info">SQL</span>{/if}
                                        {if $pkg.has_script}<span class="badge text-bg-info">程序文件</span>{/if}
                                        {if $pkg.has_program}<span class="badge text-bg-warning">升级脚本</span>{/if}
                                    </td>
                                    <td>{$pkg.mtime}</td>
                                    <td><a class="btn btn-sm btn-primary" href="{:url('check',['file'=>$pkg.file])}">检测并升级</a></td>
                                </tr>
                                {/volist}
                            </tbody>
                        </table>
                    </div>
                    {else/}
                    <div class="alert alert-light border mb-3">暂无本地升级包，请上传 ZIP 升级包后再执行升级。</div>
                    {/if}

                    <form class="dask-upload-form" method="post" enctype="multipart/form-data" action="{:url('upload')}">
                        <div class="mb-3 mb-2">
                            <label for="upgrade-package" class="mb-1">上传升级包（ZIP，最大 50MB）</label>
                            <input type="file" class="form-control" id="upgrade-package" name="package" accept=".zip,application/zip" required>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i>上传并检测</button>
                    </form>
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
                        后续升级请通过上传官方 ZIP 升级包完成，全新安装使用 <code>install/sql/install.sql</code>。
                    </p>
                    <p class="text-muted mb-3" style="font-size:13px">
                        数据库备份与还原可在上方「数据库备份」「数据库还原」页签中操作。程序与数据库版本已统一为单一版本源，此处「当前版本」即为数据库版本。
                    </p>
                    <div class="text-muted mt-2" style="font-size:12px">升级前请务必先备份数据库与 <code>public/storage</code>。</div>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}