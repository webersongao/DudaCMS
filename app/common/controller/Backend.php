<?php
namespace app\common\controller;
use app\common\library\builder\FormBuilder;
use app\common\library\builder\MakeBuilder;
use app\common\library\builder\TableBuilder;
use app\common\library\helper\NotifyHelper;
use app\common\library\helper\AuthHelper;
use app\common\library\helper\DataHelper;
use app\common\traits\Curd;
use app\model\admin\AdminLog;
use app\model\Users;
use think\App;
use think\exception\HttpResponseException;
use think\facade\Request;
use think\helper\Str;

/**
 * 后台控制器基类
 */
class Backend extends Base
{
    use Curd;
    protected $tableBuilder;
    protected $formBuilder;
    protected $makeBuilder;
    protected $returnUrl;

    /**
     * 无需登录方法
     * @var
     */
    protected $noNeedLogin=['login'];

    /**
     * 无需鉴权方法
     * @var
     */
    protected $noNeedRight;
    protected $auth;
    protected $user_id;
    protected $user_info;
    protected $table;
    protected $model;
    protected $pk='id';
    protected $baseUrl;

    public function __construct(App $app)
    {
        parent::__construct($app);

        //判断是否入口文件是否一致
        $baseFile = $this->request->baseFile();
        if($baseFile != '/'.config('app.admin'))
        {
            $this->error404();
        }

        //检查IP封禁
        $this->checkIpAllowed();

        $this->view->config([
            'view_path' => config('view.backend_path'),
        ]);

        //数据表格构造器
        $this->tableBuilder = TableBuilder::getInstance();
        //表单构造器
        $this->formBuilder =  FormBuilder::getInstance();

        //CURD页面构造器
        $this->makeBuilder = MakeBuilder::getInstance();

        //权限验证器
        $this->auth =AuthHelper::instance();
        if(session('admin_login_uid') && getLoginUid())
        {
            $this->user_info = Users::getUserInfo(getLoginUid());
        }

        $this->user_id = $this->user_info ? $this->user_info['uid'] : 0;
        $controller = $this->request->controller();
        $actionName = Str::snake(request()->action());

        $root = get_setting('sub_dir','/');
        $root = '/'.ltrim($root,'/');
        $this->baseUrl = rtrim(Request::domain().$root,'/');

        //检验权限地址
        $checkPath = $controller.'/'.strtolower($actionName);
        // 检测是否需要验证登录
        if (!$this->auth->match($this->noNeedLogin)) {
            //检测是否登录
            if (!$this->user_id && !getLoginUid()) {
                $this->loading('/account/login');
            }

            if (!$this->user_id && getLoginUid()) {
                $this->loading('index/login');
            }
            // 判断是否需要验证权限
            // 判断控制器和方法判断是否有对应权限
            if(!$this->auth->isSuperAdmin())
            {
                if (!$this->auth->match($this->noNeedRight) && !$this->auth->check($checkPath, $this->user_id)) {
                    $this->error('您没有访问权限', '/');
                }
            }
        }

        //渲染菜单
        if ($this->user_id)
        {
            $menu = $this->auth->getTreeMenu();
            $this->view->assign($menu);
            $breadCrumb = DataHelper::formatBreadCrumb($this->auth->getBreadCrumb($checkPath));
            // 菜单
            $this->view->assign(['breadCrumb' => $breadCrumb]);
            if(get_setting('record_admin_log')=='Y')
            {
                // 进行操作日志的记录
                AdminLog::record();
            }
        }

        $return_url = $_SERVER['HTTP_REFERER'] ?? '/';
        session('return_url',base64_encode($return_url));
        $this->returnUrl = base64_decode(session('return_url'));
        $this->assign('return_url',base64_decode(session('return_url')));

        if(get_setting('email_enable')!='Y' ||get_setting('email_host')=='' || get_setting('email_username')=='')
        {
            $this->assign('email_tips',1);
        }

        // 后台是源码（DudaCMS）的管理界面，品牌与运营站点名解耦：
        // 后台 Logo 文字固定为源码品牌；站点名只出现在"当前站点"语境（页面 title、页脚）。
        $logoText = trim((string) get_setting('admin_logo_text', ''));
        if ($logoText === '') {
            $logoText = 'DudaCMS';
        }
        $logoText = function_exists('mb_substr') ? mb_substr($logoText, 0, 12, 'UTF-8') : substr($logoText, 0, 12);

        $this->assign([
            'baseUrl'=> $this->baseUrl,
            'notify_count'=>NotifyHelper::getNotifyCount(),
            'notify_list'=>NotifyHelper::getNotifyTextList(),
            'user_id'=>$this->user_id,
            'version'=>env('APP_DEBUG') ? time() : config('version.version'),
            'user_info'=>$this->user_info,
            '_ajax' =>$this->request->param('_ajax',0),
            '_ajax_open'=>$this->request->param('_ajax_open',0),
            'base_url' => $this->baseUrl.$this->request->baseFile(),
            'cdnUrl' => get_setting('cdn_url', $this->baseUrl),
            'assetBase' => asset('', false),
            'setting'=>get_setting(),
            'admin_logo_text'=>$logoText,
            'admin_block'=>app_path(). DS.'backend'. DS . 'view' . DS.'block.php',
            'version_info'=>[
                'code'=>0,
                'msg'=>'',
                'data'=>''
            ],
        ]);

        // 控制器初始化
        $this->initialize();
    }

    /**
     * 后台错误统一转成带诊断信息的卡片，不再跳到泛化错误页。
     */
    protected function error($msg = '', $url = null, $data = '', int $wait = 3)
    {
        if (is_string($url)) {
            $url = (string) $url;
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : app('route')->buildUrl($url)->__toString();
        } elseif (is_object($url)) {
            $url = (string) $url;
        } else {
            $url = null;
        }

        $message = L($msg);
        if ($this->getResponseType() === 'json') {
            $response = json([
                'code' => 0,
                'msg'  => $message,
                'data' => $data,
                'url'  => $url,
                'wait' => $wait,
            ]);
            throw new HttpResponseException($response);
        }

        $context = [
            'error_message'   => $message,
            'error_hint'      => $this->adminErrorHint((string) $message),
            'error_module'    => defined('ENTRANCE') ? strtolower((string) ENTRANCE) : 'backend',
            'error_controller'=> (string) $this->request->controller(),
            'error_action'    => (string) $this->request->action(),
            'error_url'       => (string) $this->request->url(true),
            'error_file'      => '',
            'error_line'      => 0,
            'error_type'      => '操作提示',
            'error_trace'     => '',
            'back_url'        => $url ?: ($_SERVER['HTTP_REFERER'] ?? ''),
            'home_url'        => (string) url('Index/index'),
        ];
        $response = view(app()->getBasePath() . 'common' . DS . 'tpl' . DS . 'admin_error.tpl', $context);
        throw new HttpResponseException($response);
    }

    /**
     * 根据常见后台错误给出可操作建议。
     */
    private function adminErrorHint(string $message): string
    {
        if (str_contains($message, '权限') || str_contains($message, '无权')) {
            return '当前账号缺少该功能权限，请联系超级管理员分配后重试。';
        }
        if (str_contains($message, '参数') || str_contains($message, '不存在')) {
            return '请检查必填参数是否正确，或返回列表页重新进入当前功能。';
        }
        return '请记录下方诊断信息并联系维护人员；也可以返回上一页或后台首页重试。';
    }

    public function initialize(){}
}