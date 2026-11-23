<?php
// +----------------------------------------------------------------------
// | DudaCMS
// | Copyright (c) 2026 https://ask.btbk.org
// | DudaCMS团队一款基于TP8开发的社交化知识付费问答系统、企业内部知识库系统
// | Author: DudaCMS团队 <ask@btbk.org>
// +----------------------------------------------------------------------

namespace app\common\middleware;

use app\common\library\helper\DomainHelper;
use Closure;
use think\facade\Config;
use think\Request;
use think\Response;

/**
 * 域名解耦中间件
 *
 * 在响应阶段把 HTML 中的本站域名重写为当前访问域名，
 * 并把库里的站内相对路径（/storage/...、/article/...）补成完整 URL。
 * 只对 text/html 生效，不影响 API JSON / 图片 / 文件下载。
 */
class DomainRewrite
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (!Config::get('domain.rewrite_enabled', true)) {
            return $response;
        }

        $method = $request->method();
        if (!in_array($method, ['GET', 'HEAD'], true)) {
            return $response;
        }

        // JSON 数组/对象响应（后台列表 AJAX、API 等）在 send() 前 content 仍是数组，
        // 此时 getContent() 会抛 InvalidArgumentException。必须在任何 getContent()
        // 调用之前，用原始 data 判定并放行，绝不能依赖 Content-Type（数组响应头为空）。
        $rawData = $response->getData();
        if (is_array($rawData) || is_object($rawData) || is_null($rawData)) {
            return $response;
        }

        // 仅处理 HTML 页面响应
        $contentType = strtolower((string) $response->getHeader('Content-Type'));
        if ($contentType !== '' && strpos($contentType, 'text/html') === false) {
            return $response;
        }

        $content = $response->getContent();
        if (!is_string($content) || $content === '') {
            return $response;
        }

        $rewritten = DomainHelper::rewriteHtml($content);
        if ($rewritten !== $content) {
            $response->content($rewritten);
        }

        return $response;
    }
}
