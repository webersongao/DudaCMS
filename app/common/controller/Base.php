<?php
declare (strict_types = 1);

namespace app\common\controller;

use app\common\traits\Jump;
use Psr\SimpleCache\InvalidArgumentException;
use think\App;
use think\exception\ValidateException;
use think\Request;
use think\Validate;

/**
 * 控制器基础类
 */
abstract class Base
{
    use Jump;
    /**
     * Request实例
     * @var Request
     */
    protected $request;

    /**
     * 应用实例
     * @var App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    protected $beforeActionList=[];
    protected $view;

    /**
     * 构造方法
     * @access public
     * @param App $app 应用对象
     * @throws InvalidArgumentException
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;
        $this->view = $this->app->view;

        //前置操作
        if ($this->beforeActionList) {
            foreach ($this->beforeActionList as $method => $options) {
                is_numeric($method) ? $this->beforeAction($options) : $this->beforeAction($method, $options);
            }
        }

        // 控制器初始化
        //$this->initialize();
    }

    // 初始化
    protected function initialize()
    {}

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

    /**
     * 前置操作
     * @access protected
     * @param string $method  前置操作方法名
     * @param array $options 调用参数 ['only'=>[...]] 或者 ['except'=>[...]]
     * @return void
     */
    protected function beforeAction(string $method, array $options = [])
    {
        if (isset($options['only'])) {
            if (is_string($options['only'])) {
                $options['only'] = explode(',', $options['only']);
            }

            if (!in_array($this->request->action(), $options['only'])) {
                return;
            }
        } elseif (isset($options['except'])) {
            if (is_string($options['except'])) {
                $options['except'] = explode(',', $options['except']);
            }

            if (in_array($this->request->action(), $options['except'])) {
                return;
            }
        }

        call_user_func([$this, $method]);
    }

    /**
     * 模板变量赋值
     * @param string|array $name 模板变量
     * @param mixed $value 变量值
     * @return mixed
     */
    public function assign($name, $value = null)
    {
        return $this->view->assign($name, $value);
    }

    /**
     * 解析和获取模板内容 用于输出
     * @param string $template
     * @param array $vars
     * @return mixed
     */
    public function fetch(string $template = '', array $vars = [])
    {
        // ThinkPHP 8 在多级控制器（如 extend.Upgrade、member.Users）下会按
        // layer 截断控制器名，导致默认模板定位到 view/upgrade/index.php 这类错误路径。
        // 这里显式使用完整控制器路径，保持与 TP6 一致的模板解析规则。
        if ($template === '') {
            $template = \think\helper\Str::snake($this->request->action());
        }

        if (strpos($template, '/') !== 0
            && strpos($template, '@') === false
            && pathinfo($template, PATHINFO_EXTENSION) === ''
        ) {
            // 控制器名按 TP 的蛇形命名规则映射到模板目录（兼容 Linux 大小写敏感）
            $controller = implode('/', array_map(
                [\think\helper\Str::class, 'snake'],
                explode('.', $this->request->controller())
            ));
            $template   = '/' . $controller . '/' . $template;
        }

        return $this->view->fetch($template, $vars);
    }


    /**
     * 渲染内容输出
     * @access protected
     * @param  string $content 模板内容
     * @param  array  $vars    模板输出变量
     * @return mixed
     */
    protected function display(string $content = '',array $vars = [])
    {
        return $this->view->display($content, $vars);
    }
}
