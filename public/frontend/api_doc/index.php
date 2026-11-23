{extend name="$theme_block" /}

{block name="meta_title"}API v1 文档 - {$setting.site_name}{/block}
{block name="meta_keywords"}{$setting.site_name},API,接口文档,OpenAPI,JWT{/block}
{block name="meta_description"}{$setting.site_name} API v1 接口文档，支持 App、PWA、H5 接入。{/block}

{block name="meta_style"}
<link rel="stylesheet" href="{$assetBase}api-doc/apidoc.css?v={:asset_version('api-doc/apidoc.css')}">
<script defer src="{$assetBase}api-doc/apidoc.js?v={:asset_version('api-doc/apidoc.js')}"></script>
{/block}

{block name="main"}
<section class="apidoc-hero">
    <div class="container position-relative">
        <div class="apidoc-kicker">Developer · API v1</div>
        <h1>API v1 接口文档</h1>
        <p>独立入口、JWT 认证、统一响应结构。供 App、PWA、H5 与第三方客户端接入，与 Web 控制器解耦。</p>
        <div class="apidoc-actions">
            <a class="btn btn-light" href="{$api_base}/common/health" target="_blank" rel="noopener">健康检查</a>
            <a class="btn btn-outline-light" href="/api-docs/openapi.yaml">下载 OpenAPI</a>
            {if !$api_doc_public}
            <span class="apidoc-badge">当前仅管理员可见</span>
            {/if}
        </div>
    </div>
</section>

<div class="container apidoc-layout">
    <aside class="apidoc-sidebar" id="apidoc-sidebar">
        <button type="button" class="apidoc-mobile-toggle" id="apidoc-menu-toggle">显示目录</button>
        <input class="apidoc-search" id="apidoc-filter" type="search" placeholder="搜索目录…" aria-label="搜索目录">
        <nav aria-label="API 文档目录">
            <ul class="apidoc-toc" id="apidoc-toc"></ul>
        </nav>
    </aside>
    <article class="apidoc-content" id="apidoc-content">
        {$content|raw}
    </article>
</div>
{/block}
