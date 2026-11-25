<?php
// +----------------------------------------------------------------------
// | DudaCMS
// +----------------------------------------------------------------------
// | Copyright (c) 2026 https://ask.btbk.org
// +----------------------------------------------------------------------
// | DudaCMS团队一款基于TP8开发的社交化知识付费问答系统、企业内部知识库系统，打造私有社交化问答、内部知识存储
// +----------------------------------------------------------------------
// | Author: DudaCMS团队 <ask@btbk.org>
// +----------------------------------------------------------------------
namespace app\backend\member;

use app\common\controller\Backend;
use app\model\Score as ScoreModel;
use think\App;
use think\facade\Request;

/**
 * 成长值规则
 * Class Score
 */
class CreditRule extends Backend
{
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->model = new ScoreModel();
        $this->table = 'credit_rule';
    }

    public function index()
    {
        $columns = [
            ['id', '编号'],
            ['name', '行为标识'],
            ['title', '行为名称'],
            ['cycle', '执行周期'],
            ['cycle_type', '周期类型', 'tag', '', ['month' => '每月', 'week' => '每周', 'day' => '每天', 'hour' => '每小时', 'minute' => '每分钟', 'second' => '每秒']],
            ['max', '最大执行次数'],
            ['credit', '成长值'],
            ['log', '日志规则'],
            ['status', '状态', 'status', '0', ['0' => '禁用', '1' => '启用']],
        ];

        $search = [];

        if ($this->request->param('_list')) {
            // 排序规则
            $orderByColumn = safe_order_column($this->request->param('orderByColumn') ?? 'id');
            $isAsc = safe_order_direction($this->request->param('isAsc') ?? 'desc');
            $where = $this->makeBuilder->getWhere($search);
            $pageSize = $this->request->param('pageSize', get_setting("contents_per_page", 15));
            return db('credit_rule')
                ->where($where)
                ->order([$orderByColumn => $isAsc])
                ->paginate([
                    'query'     => Request::get(),
                    'list_rows' => $pageSize,
                ])
                ->toArray();
        }

        return $this->tableBuilder
            ->setUniqueId('id')
            ->addColumns($columns)
            ->setSearch($search)
            ->addColumn('right_button', '操作', 'btn')
            ->addRightButtons(['edit', 'delete'])
            ->addTopButtons(['add', 'delete'])
            ->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->except(['file'], 'post');
            if (empty($data['name']) || !preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', (string) $data['name'])) {
                $this->error('行为标识需以字母开头，只能包含字母、数字和下划线');
            }
            if (empty($data['title'])) {
                $this->error('请填写行为名称');
            }
            if (db('credit_rule')->where('name', $data['name'])->find()) {
                $this->error('该行为标识已存在');
            }
            $data['cycle'] = max(1, (int) ($data['cycle'] ?? 1));
            $data['max'] = max(0, (int) ($data['max'] ?? 0));
            $data['credit'] = (int) ($data['credit'] ?? 0);
            $result = $this->model->create($data);
            if ($result) {
                $this->success('添加成功', 'index');
            } else {
                $this->error('添加失败');
            }
        }
        return $this->formBuilder
            ->addText('name', '行为标识', '输入行为标识 英文字母')
            ->addText('title', '行为名称', '输入行为名称')
            ->addText('cycle', '执行周期', '输入执行周期,代表每xx周期执行', 1)
            ->addRadio('cycle_type', '周期类型', '选择执行类型', ['month' => '每月', 'week' => '每周', 'day' => '每天', 'hour' => '每小时', 'minute' => '每分钟', 'second' => '每秒'])
            ->addNumber('max', '最大执行次数', '输入最大执行次数')
            ->addText('credit', '成长值', '输入成长值')
            ->addTextarea('log', '日志规则', '记录日志备注时按此规则来生成，支持[变量|函数],如[user|get_username]。目前变量有：user(用户uid),time(时间),record(记录值)')
            ->addRadio('status', '状态', '状态', ['0' => '禁用', '1' => '正常'], 1)
            ->fetch();
    }

    public function edit($id = 0)
    {
        if ($this->request->isPost()) {
            $data = $this->request->except(['file'], 'post');
            if (empty($data['id'])) {
                $this->error('缺少记录编号');
            }
            if (empty($data['name']) || !preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', (string) $data['name'])) {
                $this->error('行为标识需以字母开头，只能包含字母、数字和下划线');
            }
            if (empty($data['title'])) {
                $this->error('请填写行为名称');
            }
            $duplicate = db('credit_rule')->where('name', $data['name'])->where('id', '<>', (int) $data['id'])->find();
            if ($duplicate) {
                $this->error('该行为标识已存在');
            }
            $data['cycle'] = max(1, (int) ($data['cycle'] ?? 1));
            $data['max'] = max(0, (int) ($data['max'] ?? 0));
            $data['credit'] = (int) ($data['credit'] ?? 0);
            $result = $this->model->update($data);
            if ($result !== false) {
                $this->success('修改成功', 'index');
            } else {
                $this->error('提交失败或数据无变化');
            }
        }

        $info = $this->model->where('id', $id)->find();
        if (!$info) {
            $this->error('成长值规则不存在');
        }

        return $this->formBuilder
            ->addHidden('id', $info['id'])
            ->addText('name', '行为标识', '输入行为标识 英文字母', $info['name'])
            ->addText('title', '行为名称', '输入行为名称', $info['title'])
            ->addText('cycle', '执行周期', '代表每xx周期执行', $info['cycle'])
            ->addRadio('cycle_type', '周期类型', '选择周期类型', ['month' => '每月', 'week' => '每周', 'day' => '每天', 'hour' => '每小时', 'minute' => '每分钟', 'second' => '每秒'], $info['cycle_type'])
            ->addNumber('max', '最大执行次数', '输入最大执行次数', $info['max'])
            ->addText('credit', '成长值', '输入成长值', $info['credit'])
            ->addTextarea('log', '日志规则', '记录日志备注时按此规则来生成，支持[变量|函数],如[user|get_username]。目前变量有：user(用户uid),time(时间),record(记录值)', $info['log'])
            ->addRadio('status', '状态', '状态', ['0' => '禁用', '1' => '正常'], $info['status'])
            ->fetch();
    }

    //日志列表
    public function log()
    {
        $columns = [
            ['id', '编号'],
            ['nick_name', '执行用户', 'link', build_user_url('__url_token__', '__url_token__')],
            ['record_id', '数据id'],
            ['action_type', '触发行为的类型', 'tag'],
            ['credit', '成长值'],
            ['remark', '成长值说明'],
            ['balance', '成长值余额'],
            ['create_time', '记录时间', 'datetime'],
        ];

        $search = [
            ['text', 'nick_name', '用户昵称', 'LIKE'],
        ];

        if ($this->request->param('_list')) {
            // 排序规则
            $orderByColumn = safe_order_column($this->request->param('orderByColumn') ?? 'id');
            $isAsc = safe_order_direction($this->request->param('isAsc') ?? 'desc');
            $pageSize = $this->request->param('pageSize', get_setting("contents_per_page", 15));
            $where = [];
            if ($nick_name = $this->request->param('nick_name')) {
                $uid = db('users')->where([['nick_name', 'like', $nick_name]])->value('uid');
                if ($uid) {
                    $where[] = ['uid', 'like', $uid];
                }
            }
            $result = db('credit_log')
                ->where($where)
                ->order([$orderByColumn => $isAsc])
                ->paginate([
                    'query'     => Request::get(),
                    'list_rows' => $pageSize,
                ])
                ->toArray();
            foreach ($result['data'] as $k => $v) {
                $result['data'][$k]['nick_name'] = db('users')->where('uid', $v['uid'])->value('nick_name');
                $result['data'][$k]['url_token'] = db('users')->where('uid', $v['uid'])->value('url_token');
                $result['data'][$k]['action_type'] = db('credit_rule')->where('name', $v['action_type'])->value('title');
            }
            return $result;
        }

        return $this->tableBuilder
            ->setUniqueId('id')
            ->addColumns($columns)
            ->setSearch($search)
            ->setEditUrl((string)url('detail', ['id' => '__id__']))
            ->fetch();
    }

    /** 成长记录详情：列表行点击后展示该笔成长值流水。 */
    public function detail(string $id = '0')
    {
        $id = (int) $id;
        $info = db('credit_log')->where('id', $id)->find();
        if (!$info) {
            $this->error('成长记录不存在');
        }
        $user = db('users')->where('uid', $info['uid'])->field('user_name,nick_name')->find();
        $rule = db('credit_rule')->where('name', $info['action_type'])->value('title');

        return $this->formBuilder
            ->addHtml('detail_info', '', '',
                '<dl class="row mb-0">' .
                '<dt class="col-sm-3">记录编号</dt><dd class="col-sm-9">' . (int) $info['id'] . '</dd>' .
                '<dt class="col-sm-3">执行用户</dt><dd class="col-sm-9">' . htmlspecialchars((string) ($user['nick_name'] ?: $user['user_name'] ?: $info['uid']), ENT_QUOTES, 'UTF-8') . '</dd>' .
                '<dt class="col-sm-3">行为类型</dt><dd class="col-sm-9">' . htmlspecialchars((string) ($rule ?: $info['action_type']), ENT_QUOTES, 'UTF-8') . '</dd>' .
                '<dt class="col-sm-3">数据ID</dt><dd class="col-sm-9">' . htmlspecialchars((string) $info['record_id'], ENT_QUOTES, 'UTF-8') . '</dd>' .
                '<dt class="col-sm-3">成长值变动</dt><dd class="col-sm-9">' . (int) $info['credit'] . '</dd>' .
                '<dt class="col-sm-3">变动后余额</dt><dd class="col-sm-9">' . (int) $info['balance'] . '</dd>' .
                '<dt class="col-sm-3">说明</dt><dd class="col-sm-9">' . htmlspecialchars((string) $info['remark'], ENT_QUOTES, 'UTF-8') . '</dd>' .
                '<dt class="col-sm-3">记录时间</dt><dd class="col-sm-9">' . date('Y-m-d H:i:s', (int) $info['create_time']) . '</dd>' .
                '</dl>'
            )
            ->hideBtn(['submit'])
            ->fetch();
    }
}
