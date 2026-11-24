<?php
namespace app;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;
use app\api\v1\ApiException;
use app\api\v1\Response as ApiResponse;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // API 独立入口统一返回 JSON，避免 HTML 错误页泄露堆栈
        if (defined('ENTRANCE') && ENTRANCE === 'api') {
            // v1 使用 HttpResponseException 输出 JSON，必须原样返回
            if ($e instanceof HttpResponseException) {
                return $e->getResponse();
            }

            if ($e instanceof ApiException) {
                return ApiResponse::error($e->getApiCode(), $e->getMessage(), $e->getHttpStatus(), $e->getData());
            }

            if ($e instanceof HttpException) {
                $status = $e->getStatusCode();
                $message = $status === 404 ? '接口不存在' : ($status === 405 ? '请求方法不允许' : '请求失败');
                return ApiResponse::error($status === 404 ? 1003 : 1005, $message, $status);
            }

            \think\facade\Log::error('[api] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            $message = env('app_debug', false) ? $e->getMessage() : '服务器繁忙，请稍后重试';
            return ApiResponse::error(5000, $message, 500);
        }

        // 控制器的 success/error/redirect 等响应必须原样返回。
        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        }

        // 后台未捕获异常：返回带诊断信息的卡片，不再跳到泛化错误页。
        if (defined('ENTRANCE') && ENTRANCE === 'backend') {
            $status = $e instanceof HttpException ? $e->getStatusCode() : 500;
            $message = $e instanceof HttpException
                ? $this->adminHttpMessage($status)
                : $e->getMessage();

            \think\facade\Log::error('[backend] ' . $message . ' @ ' . $e->getFile() . ':' . $e->getLine());

            $trace = [];
            foreach (array_slice($e->getTrace(), 0, 5) as $frame) {
                $name = (isset($frame['class']) ? $frame['class'] . '::' : '') . ($frame['function'] ?? '') . '()';
                $trace[] = $name;
            }

            $debug = app()->isDebug();
            $context = [
                'error_message'   => $debug ? $message : '服务暂时不可用，请稍后重试',
                'error_hint'      => $debug ? $this->adminExceptionHint((string) $message) : '详细错误已写入日志',
                'error_module'    => defined('ENTRANCE') ? strtolower((string) ENTRANCE) : 'frontend',
                'error_controller'=> (string) $request->controller(),
                'error_action'    => (string) $request->action(),
                'error_url'       => (string) $request->url(true),
                'error_file'      => $debug ? str_replace(root_path(), '', $e->getFile()) : '',
                'error_line'      => $debug ? $e->getLine() : 0,
                'error_type'      => $debug ? get_class($e) : '',
                'error_trace'     => $debug ? implode(' ← ', $trace) : '',
                'back_url'        => (string) ($_SERVER['HTTP_REFERER'] ?? ''),
                'home_url'        => (string) url('Index/index'),
            ];
            return view(app()->getBasePath() . 'common' . DS . 'tpl' . DS . 'admin_error.tpl', $context, $status);
        }

        // 前台未捕获异常：记录可排查日志（错误页对用户只展示友好提示，
        // 但服务端必须留痕，否则运维无法定位 notify/records 等页面的真实故障）。
        \think\facade\Log::error('[frontend] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine()
            . ' | url=' . (string) $request->url(true));

        // 其他错误交给系统处理
        return parent::render($request, $e);
    }

    private function adminHttpMessage(int $status): string
    {
        return match ($status) {
            403 => '没有访问权限',
            404 => '后台页面不存在',
            405 => '请求方法不允许',
            default => '后台请求失败（HTTP ' . $status . '）',
        };
    }

    private function adminExceptionHint(string $message): string
    {
        if (str_contains($message, '不存在') || str_contains($message, 'not found') || stripos($message, 'Class ') === 0) {
            return '该功能可能已被移除或尚未安装，请从后台菜单重新进入；若仍出现，请把上方诊断信息发给维护人员。';
        }
        if (str_contains($message, '权限') || str_contains($message, '无权')) {
            return '当前账号缺少该功能权限，请联系超级管理员分配后重试。';
        }
        return '请记录上方诊断信息并联系维护人员；也可以返回上一页或后台首页重试。';
    }
}
