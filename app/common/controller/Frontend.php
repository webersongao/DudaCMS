<?php
namespace app\common\controller;
use app\common\library\helper\CheckHelper;
use app\common\library\helper\StringHelper;
use app\common\library\helper\UserAuthHelper;
use app\common\traits\Common;
use app\model\Users;
use think\App;
use think\exception\HttpResponseException;
use think\facade\Config;
use think\facade\Request;
use think\helper\Str;

/**
 * 前台控制器基类
 */
class Frontend extends Base
{
    use Common;
    protected $user_id;
    protected $user_info;
    protected $model;
    protected $needLogin;
    protected $returnUrl;

    //控制器名称
    protected $controller;
    //方法名称
    protected $action;
    protected $isMobile;
    protected $settings;
    protected $userAuth;
    protected $baseUrl;

    public function __construct(App $app)
    {
        parent::__construct($app);

        //检查IP封禁
        $this->checkIpAllowed();

        $this->user_info = Users::getUserInfo(intval(getLoginUid()));
        $this->user_id = $this->user_info['uid'] ?? 0;
        $this->baseUrl = rtrim(Request::domain(), '/');

        $this->settings = get_setting();

        //防止用户不存在session清除 BUG
        if(!$this->user_id && getLoginUid())
        {
            Users::logout();
        }

        //全局前台钩子（只可用于逻辑处理）

        $this->userAuth = UserAuthHelper::instance();

        //是否是手机端
        $this->isMobile = Request::isMobile();

        $controller = $this->request->controller();
        if (strpos($controller, '.')) {
            $pos        = strrpos($controller, '.');
            $controller = substr($controller, 0, $pos) . '.' . Str::snake(substr($controller, $pos + 1));
        } else {
            $controller = Str::snake($controller);
        }

        $this->controller = $controller;

        $this->action = Str::snake($this->request->action());
        // 主导航列表页共享题图：切换首页/问答/文章/话题时题图始终存在，避免页面上下抖动
        // 题图只在首页显示；问答/文章/话题等二级列表页不再占用 260px 题图空白。
        $heroPages = ['index'];
        $heroLabels = [
            'index' => '- 说出你装逼的故事',
            'question' => '- 问答',
            'article' => '- 文章',
            'topic' => '- 话题'
        ];
        $heroKey = strtolower(str_replace('_', '', $this->controller));
        // 题图区块固定展示（首页/问答/文章/话题的 index 页恒显示题图）
        $showHero = in_array($heroKey, $heroPages, true)
            && strtolower($this->action) === 'index';
        $heroTitle = $this->settings['site_name'] . ' ' . ($heroLabels[$heroKey] ?? '');
        // 题图副标题使用固定默认文案（原「站点短标语」配置项已移除）
        $heroSubtitle = '与世界分享你的装逼技巧与水平';
        $this->assign([
            'baseUrl'=> $this->baseUrl,
            'thisController'       => parse_name($this->controller),
            'thisAction'           => $this->action,
            'thisRequest'          => parse_name("{$this->controller}/{$this->action}"),
            'cdnUrl' => get_setting('cdn_url',$this->baseUrl),
            'assetBase' => asset('', false),
            '_ajax' =>$this->request->param('_ajax', 0),
            '_ajax_open'=>$this->request->param('_ajax_open', 0),
            '_pjax'=>$this->request->isPjax() ? 1 : 0,
            'setting'=>$this->settings,
            // 单一响应式前台静态资源目录（原 templates/default/static 已迁入 public/static/frontend）
            'static_url'=>asset('frontend/', false),
            'user_info'=>$this->user_info,
            'user_id'=>$this->user_id,
            'version'=>env('APP_DEBUG') ? time() : config('version.version'),
            'isMobile'=>$this->isMobile,
            'navMenu'=>$this->userAuth->getNav($this->controller.'/'.$this->action),//顶部导航
            'footerMenu'=>$this->userAuth->getNav($this->controller.'/'.$this->action,'footer'),//底部导航
            'credit_rule'=>db('credit_rule')->where(['status'=>1])->cache(true)->column('credit','name'),
            'theme_config'=>get_theme_setting(),
            'showHero'=>$showHero,
            'heroTitle'=>$heroTitle,
            'heroSubtitle'=>$heroSubtitle,
            'thirdConfig'=>\app\common\library\helper\ThirdAuthHelper::config()
        ]);

        //站点关闭检查
        if(!CheckHelper::checkSiteStatus())
        {
            $this->siteCloseNotify();
        }

        if ($this->user_id && $this->user_info['status'] === 3) {
            $users_forbidden = db('users_forbidden')->where(['uid' => $this->user_info['uid'], 'status' => 1])->find();
            if($users_forbidden && $users_forbidden['forbidden_time']>time())
            {
                $this->error('您的账号因触发社区封禁规则现已被管理员封禁！封禁原因：' . $users_forbidden['forbidden_reason'] . ';解封时间：' . date('Y-m-d H:i:s', $users_forbidden['forbidden_time']));
            }
        }

        //检测是否登录
        if (UserAuthHelper::instance()->match($this->needLogin) && !$this->user_id) {
            $url = $this->request->url();
            if ($url == '/') {
                $this->redirect(url('account/login'));
            }
            $this->error('请先登录后进行操作', url('account/login'));
        }

        //检查用户在线状态
        CheckHelper::checkOnline();

        //检查游客权限 和 浏览网站权限
        if($this->user_info['permission']['visit_website']!='Y' && request()->controller()!='Account' && request()->action()!='login')
        {
            $this->error('您所在组已禁止浏览网站',url('account/login'));
        }

        // 检测当前用户是在其他地方登录
        if ($this->user_id && get_setting('unique_login')=='Y' && !CheckHelper::checkUserIsLoginOtherPlatform($this->user_id))
        {
            Users::logout();
            $this->error('您已在其他地方登录','/');
        }

        // 控制器初始化
        $this->initialize();
    }

    public function initialize()
    {
        $this->TDK();
        $this->script();
        $this->style();
        //记录来源页面地址
        $return_url = $_SERVER['HTTP_REFERER'] ?? '/';
        session('return_url',base64_encode($return_url));
        $this->returnUrl = $return_url;
        $this->assign('return_url',$return_url);
    }

    public function fetch(string $template = '', array $vars = [])
    {
        // 单一响应式前台，固定视图目录。
        $view_path = config('view.frontend_path');

        $depr = Config::get('view.view_depr');
        if($this->controller=='index' && $this->action=='index')
        {
            $template = '/index';
        }

        $template = str_replace(['/', ':'], $depr, $template);
        if ('' == $template) {
            $template = str_replace('.', DIRECTORY_SEPARATOR, $this->controller) . $depr . $this->action;
        } elseif (false === strpos($template, $depr)) {
            $template = str_replace('.', DIRECTORY_SEPARATOR, $this->controller) . $depr . $template;
        }

        $this->assign([
            'theme_config'=>get_theme_setting(),
            'theme_block'=>config('view.frontend_path') . 'block.php'
        ]);

        $this->view->config([
            'view_path' => $view_path,
            'view_suffix'=>'php'
        ]);
        return $this->view->fetch($template, $vars);
    }

    /**
     * 关键词审核
     * @param null $content
     * @param string $approval_type
     * @return bool
     */
    public function publish_approval_valid($content = null,string $approval_type='publish_question_approval'): bool
    {
        //管理员不审核
        if ($this->user_info['group_id']==1 OR $this->user_info['group_id']==2)
        {
            return false;
        }

        //违禁词审核
        if ($content AND StringHelper::sensitive_word_exists($content))
        {
            return true;
        }
        //未开启审核
        if (!$this->user_info['permission']['publish_approval_time_start'] AND !$this->user_info['permission']['publish_approval_time_end'] AND $this->user_info['permission'][$approval_type] == 'N')
        {
            return false;
        }

        //规定时间内审核
        if ($this->user_info['permission']['publish_approval_time_start'] && $this->user_info['permission']['publish_approval_time_end'] && $this->user_info['permission'][$approval_type]=='Y')
        {
            $publish_approval_time_start =str_replace(':','',$this->user_info['permission']['publish_approval_time_start']);
            $publish_approval_time_end =str_replace(':','',$this->user_info['permission']['publish_approval_time_end']);

            //同一天
            if($publish_approval_time_start < $publish_approval_time_end)
            {
                if (time() >= strtotime(date('Y-m-d',time()).$this->user_info['permission']['publish_approval_time_start']) && time() <= strtotime(date('Y-m-d',time()).$this->user_info['permission']['publish_approval_time_end']))
                {
                    return true;
                }
            }

            //跨天
            if($publish_approval_time_start > $publish_approval_time_end)
            {
                if (time() >= strtotime(date('Y-m-d',time()).$this->user_info['permission']['publish_approval_time_start']) && time() <= strtotime(date('Y-m-d',strtotime('+1 day')).$this->user_info['permission']['publish_approval_time_end']))
                {
                    return true;
                }
            }
        }

        if($this->user_info['permission'][$approval_type]=='Y' && !$this->user_info['permission']['publish_approval_time_start'] && !$this->user_info['permission']['publish_approval_time_end'])
        {
            return true;
        }

        return false;
    }

    //不存在方法返回404
    public function __call($name, $arguments)
    {
        $this->error404();
    }

    /**
     * 站点关闭
     */
    protected function siteCloseNotify()
    {
        $result = [
            'code' => 1,
            'msg'  => '',
            'url'  => '',
        ];
        $type = $this->getResponseType();
        if ($type === 'html') {
            $response = view(config('view.frontend_global_path') . 'close.php', $result);
        } elseif ($type === 'json') {
            $response = json($result);
        }
        throw new HttpResponseException($response);
    }

    /**
     * 404 页面
     * @param null $url
     */
    protected function error404($url = null)
    {
        $url = $this->getUrl($url);
        $result = [
            'code' => 1,
            'msg'  => '',
            'url'  => $url,
        ];
        $type = $this->getResponseType();
        if ($type === 'html') {
            $view_path = config('view.frontend_global_path') . '404.php';
            $response = view($view_path, $result,404);
        } elseif ($type === 'json') {
            $response = json($result);
        }
        throw new HttpResponseException($response);
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param mixed $msg 提示信息
     * @param null $url 跳转的 URL 地址
     * @param mixed $data 返回的数据
     * @param int $wait 跳转等待时间
     * @return void
     */
    protected function success($msg = '', $url = null, $data = '', int $wait = 3): void
    {
        $url = $this->getUrl($url);
        $result = [
            'code' => 1,
            'msg'  => L($msg),
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];

        $type = $this->getResponseType();
        if ($type === 'html') {
            $view_path = config('view.frontend_global_path') . 'jump.php';
            $view_path = file_exists($view_path) ? $view_path : config('app.dispatch_success_tmpl');
            $response = view($view_path, $result);
        } elseif ($type === 'json') {
            $response = json($result);
        }

        throw new HttpResponseException($response);
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param mixed $msg 提示信息
     * @param null $url 跳转的 URL 地址
     * @param mixed $data 返回的数据
     * @param int $wait 跳转等待时间
     */
    protected function error($msg = '', $url = null, $data = '', int $wait = 3)
    {
        if (is_string($url)) {
            $url = (string)$url;
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : app('route')->buildUrl($url)->__toString();
        }elseif(is_object($url))
        {
            $url = (string)$url;
        }else{
            $url = null;
        }

        $type   = $this->getResponseType();
        $result = [
            'code' => 0,
            'msg'  => L($msg),
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];

        if ($type === 'html') {
            $view_path = config('view.frontend_global_path') . 'jump.php';
            $view_path = file_exists($view_path) ? $view_path : config('app.dispatch_success_tmpl');
            $response = view($view_path, $result);
        } elseif ($type === 'json') {
            $response = json($result);
        }

        throw new HttpResponseException($response);
    }

}