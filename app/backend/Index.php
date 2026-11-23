<?php
namespace app\backend;
use app\common\controller\Backend;
use app\common\library\helper\FileHelper;
use app\common\library\helper\MailHelper;
use think\App;
use think\facade\Cache;
use app\common\logic\common\StatisticLogic;

class Index extends Backend
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    public function index()
    {
        $sys_info['os'] = PHP_OS;
        $sys_info['zlib'] = function_exists('gzclose') ? 'YES' : 'NO';//zlib
        $sys_info['safe_mode'] = (boolean)ini_get('safe_mode') ? 'YES' : 'NO';//safe_mode = Off
        $sys_info['timezone'] = function_exists("date_default_timezone_get") ? date_default_timezone_get() : "no_timezone";
        $sys_info['curl'] = function_exists('curl_init') ? 'YES' : 'NO';
        $sys_info['web_server'] = $this->request->server('SERVER_SOFTWARE');
        $sys_info['php_version'] = phpversion();
        $sys_info['ip'] = getServerIp();
        $sys_info['file_upload'] = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknown';
        $sys_info['max_ex_time'] = @ini_get("max_execution_time").'s'; //脚本最大执行时间
        $sys_info['domain'] = $this->request->server('HTTP_HOST');
        $sys_info['memory_limit'] = ini_get('memory_limit');
        $sys_info['version'] = config('version.version');
        $mysqlInfo = \think\facade\Db::query("SELECT VERSION() as version");
        $sys_info['mysql_version'] = $mysqlInfo[0]['version'];
        if (function_exists("gd_info"))
        {
            $gd = gd_info();
            $sys_info['gd_info'] = $gd['GD Version'];
        } else
        {
            $sys_info['gd_info'] = "未知";
        }

        // 用户数据

        $users_info = [
            'users_count' => db('users')->where('status', 1)->count(),
            'users_valid_email_count' => db('users')->where('is_valid_email', 1)->count(),
            'column_count' => db('column')->count(),
            'article_count' => db('article')->where('status', 1)->count(),
            'question_count' => db('question')->where('status', 1)->count(),
            'answer_count' => db('answer')->where('status', 1)->count(),
            'no_answer_count' => db('question')->where('answer_count', 0)->count(),
            'best_answer_count' => db('answer')->where('is_best', 1)->count(),
            'topic_count' => db('topic')->where('status', 1)->count(),
            'attach_count' => db('attach')->where('status', 1)->count(),
            'approval_question_count' => db('approval')->where(['status' => 0, 'type' => 'question'])->count(),
            'approval_answer_count' => db('approval')->where(['status' => 0, 'type' => 'answer'])->count(),
        ];

        // 成长值概况：按来源(action_type)聚合流水，替换原"财务概况"占位。
        // credit 为增减值：income=正数和、expense=负数绝对值和、net=净额。
        $credit_stats = [];
        $credit_total = ['income' => 0, 'expense' => 0, 'net' => 0, 'count' => 0];
        try {
            $rules = db('credit_rule')->column('title', 'name');
            $rows = db('credit_log')
                ->field('action_type, COUNT(*) AS times, SUM(CASE WHEN credit > 0 THEN credit ELSE 0 END) AS income, SUM(CASE WHEN credit < 0 THEN -credit ELSE 0 END) AS expense, SUM(credit) AS net')
                ->group('action_type')
                ->select()
                ->toArray();
            foreach ($rows as $row) {
                $key = $row['action_type'] ?: 'UNKNOWN';
                $credit_stats[] = [
                    'title' => $rules[$key] ?? $key,
                    'times' => (int) $row['times'],
                    'income' => (int) $row['income'],
                    'expense' => (int) $row['expense'],
                    'net' => (int) $row['net'],
                ];
                $credit_total['income'] += (int) $row['income'];
                $credit_total['expense'] += (int) $row['expense'];
                $credit_total['net'] += (int) $row['net'];
                $credit_total['count'] += (int) $row['times'];
            }
            usort($credit_stats, function ($a, $b) { return $b['income'] <=> $a['income']; });
        } catch (\Throwable $e) {
            // 升级未完成/表缺失时降级为空态，不影响后台首页
            $credit_stats = [];
        }

        $this->view->assign('sysInfo',$sys_info);
        $this->view->assign('usersInfo', $users_info);
        $this->view->assign('creditStats', $credit_stats);
        $this->view->assign('creditTotal', $credit_total);
        return $this->view->fetch();
    }

    //后台统计
    public function statistic()
    {
        if (!$start_time = strtotime($_GET['start_date'] . ' 00:00:01'))
        {
            $start_time = strtotime('-12 months');
        }

        if (!$end_time = strtotime($_GET['end_date'] . ' 23:59:59'))
        {
            $end_time = time();
        }

        $statistic_tag = $_GET['tag'] ? explode(',', $_GET['tag']) : [];
        if (empty($statistic_tag)) exit;
        if (!$month_list = get_month_list($start_time, $end_time, 'y')) exit;
        $data = $labels = $statistic = $data_template = [];
        foreach ($month_list AS $key => $val)
        {
            $labels[] = $val['year'] . '-' . $val['month'];
            $data_template[] = 0;
        }

        foreach ($statistic_tag AS $key => $val)
        {
            switch ($val)
            {
                case 'new_article':
                case 'new_answer':  // 新增答案
                case 'new_question':    // 新增问题
                case 'new_user':    // 新注册用户
                case 'user_valid':  // 新激活用户
                case 'new_topic':   // 新增话题
                case 'new_answer_vote': // 新增答案投票
                case 'new_answer_thanks': // 新增答案感谢
                    $statistic[] = StatisticLogic::singleTagData($val, $start_time, $end_time);
                    break;
            }
        }

        foreach($statistic AS $key => $val)
        {
            $statistic_data = $data_template;
            foreach ($val AS $k => $v)
            {
                $data_key = array_search($v['date'], $labels);
                $statistic_data[$data_key] = $v['count'];
            }
            $data[] = $statistic_data;
        }

        $this->success('', null, ['labels' => $labels, 'data' => $data]);
    }

    //系统登录
    public function login()
    {
        if(session('admin_login_uid'))
        {
            $this->redirect(url('index'));
        }

        if($this->request->isPost())
        {
            $postData = $this->request->post();
            $postData['password'] = (string) ($postData['password'] ?? '');
            if(!$this->auth->login($postData['username'],$postData['password']))
            {
                $this->error('账号或密码错误');
            }
            $this->success('登录成功',url('index/index'));
        }

        if($login_user_info = get_user_info(getLoginUid()))
        {
            $this->assign('user_info',$login_user_info);
        }else{
            $this->redirect(get_url('account/login'));
        }

        return $this->fetch();
    }

    //退出登录
    public function logout()
    {
        session('admin_user_info',null);
        session('admin_login_uid',null);
        $this->success('退出成功','login');
    }

    //清除缓存
    public function clear()
    {
        $type = $this->request->param('type','cache');
        $path = runtime_path();
        if($type!='all')
        {
            $path = runtime_path($type);
        }

        if($type=='cache' || $type=='all')
        {
            Cache::clear();
        }

        if (FileHelper::delDir($path)) {
            $this->success('清除成功');
        }
        $this->error('清除失败');
    }

    /**
     * 图标
     * @return mixed
     */
    public function icons()
    {
        return $this->view->fetch('global/icons');
    }

    //发送测试邮件
    public function send_test_email()
    {
        if($this->request->isPost())
        {
            $email = $this->request->post('email');
            $subject = get_setting('site_name').'测试邮件';
            $message = '该邮件为测试邮件，请勿回复';
            $res = MailHelper::sendEmail($email, $subject, $message);
            if ($res['code'] == 0) $this->error($res['message']);
            $this->success('测试邮件发送成功');
        }
        return $this->formBuilder
            ->addText('email','邮箱地址','请输入测试邮箱地址')
            ->fetch();
    }
}
