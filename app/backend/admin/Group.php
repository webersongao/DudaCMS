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

namespace app\backend\admin;

use app\common\controller\Backend;

class Group extends Backend
{
    protected $table = 'admin_group';
    public function index()
    {
        $columns = [
            ['id', '编号'],
            ['title', '组名称'],
            ['status', '是否启用', 'status', '0', [
                ['0' => '禁用'],
                ['1' => '启用']
            ]],
        ];

        $search = [
            ['text', 'title', '组名称', 'LIKE'],
        ];
        // 搜索
        if ($this->request->param('_list')) {
            // 排序规则
            $orderByColumn = safe_order_column($this->request->param('orderByColumn') ?? 'id');
            $isAsc = safe_order_direction($this->request->param('isAsc') ?? 'asc');
            $where = $this->makeBuilder->getWhere($search);
            // 排序处理
            $list = db('admin_group')
                ->where($where)
                ->order([$orderByColumn => $isAsc])
                ->select()
                ->toArray();
            // 渲染输出
            return [
                'total'        => count($list),
                'per_page'     => 1000,
                'current_page' => 1,
                'last_page'    => 1,
                'data'         => $list,
            ];
        }
        // 构建页面
        return $this->tableBuilder
            ->setUniqueId('id')
            ->addColumns($columns)
            ->setPageTips('特别提醒：<br>1、当前台用户所在系统组为普通用户时，普通用户组权限会被前台默认使用组覆盖<br>2、前台其他非普通组权限会覆盖前台默认组的权限，请谨慎配置权限')
            ->setSearch($search)
            ->addColumn('right_button', '操作', 'btn')
            ->setPagination('false')
            ->addRightButtons(['edit', 'delete', 'permission' => [
                'type'          => 'permission',
                'title'         => '权限管理',
                'icon'          => 'fa fa-cogs',
                'class'         => 'btn btn-success btn-xs dask-ajax-open',
                'url'           => (string)url('permission', ['id' => '__id__']),
                'href'          => ''
            ]])
            ->addTopButtons(['add', 'delete'])
            ->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();

            $permission = db('users_permission')->column('name,value');
            $permission_arr = [];
            foreach ($permission as $key => $val) {
                $permission_arr[$val['name']] = $val['value'];
            }
            $data['permission'] = json_encode($permission_arr, JSON_UNESCAPED_UNICODE);
            $data = db_input_normalize('admin_group', $data);

            $result = db('admin_group')->insertGetId($data);
            if ($result) {
                $this->success('添加成功', 'index');
            } else {
                $this->error('添加失败');
            }
        }
        return $this->formBuilder
            ->addFormItems([
                ['text', 'title', '系统组名称', '系统组名称'],
                ['checkbox2', 'rules', '权限规则', '', json_encode($this->auth->getGroupAuthRule())],
                ['radio', 'status', '启用状态', '', ['0' => '禁用', '1' => '启用'], 0],
            ])
            ->fetch();
    }

    public function edit($id = 0)
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (db_input_int($data['id'] ?? 0) === 1) {
                $data['rules'] = '*';
            }
            $data = db_input_normalize('admin_group', $data);
            $result = db('admin_group')->update($data);
            if ($result) {
                $this->success('修改成功', 'index');
            } else {
                $this->error('提交失败或数据无变化');
            }
        }
        $info = db('admin_group')->find($id);
        $columns = [
            ['hidden', 'id', $info['id']],
            ['text', 'title', '系统组名称', '系统组名称', $info['title']],
            ['radio', 'status', '启用状态', '', ['0' => '禁用', '1' => '启用'], $info['status']],
            ['checkbox2', 'rules', '权限规则', '', json_encode($this->auth->getGroupAuthRule($id)),  $info['rules']],
        ];
        return $this->formBuilder->addFormItems($columns)->fetch();
    }

    public function delete()
    {
        if ($this->request->isPost()) {
            $id = $this->request->post('id');
            if (strpos($id, ',') !== false) {
                $ids = explode(',', $id);

                if (db($this->table)->where('system', 0)->whereIn('id', $ids)->delete()) {
                    return json(['error' => 0, 'msg' => '删除成功!']);
                }
                return json(['error' => 1, 'msg' => '删除失败,删除组可能包含系统内置组']);
            }

            if (db($this->table)->where('system', 0)->where('id', $id)->delete()) {
                return json(['error' => 0, 'msg' => '删除成功!']);
            }
            return json(['error' => 1, 'msg' => '删除失败,删除组可能为系统内置组']);
        }
    }

    public function permission($id = 0)
    {
        if ($this->request->isPost()) {
            if ($data = $this->request->post()) {
                foreach ($data as $k => $v) {
                    if (is_array($v)) {
                        if (isset($v['key']) && isset($v['value'])) {
                            $value = [];
                            foreach ($v['key'] as $k1 => $v1) {
                                $value[$v1] = $v['value'][$k1];
                            }
                            $data[$k] = $value;
                        }
                    }
                }
                $data = json_encode($data, JSON_UNESCAPED_UNICODE);
                $result = db('admin_group')->where('id', $id)->update(['permission' => $data]);
                if ($result) {
                    $this->success('修改成功');
                } else {
                    $this->error('提交失败或数据无变化');
                }
            }
        }

        $permission = db($this->table)->where(['id' => $id])->value('permission');
        $permission = json_decode($permission, true);
        //排除游客组权限
        if ($id == 5) {
            $list = db('users_permission')
                ->whereIN('name', ['visit_website'])
                ->order('sort', 'asc')
                ->column('type,name,title,tips,option,value');
        } else {
            $list = db('users_permission')->whereRaw('`group`="common" OR `group`= "system"')->order('sort', 'asc')->column('type,name,title,tips,option,value');
        }
        $columns = array();
        foreach ($list as $key => $val) {
            $list[$key]['option'] = json_decode($val['option'], true);
            if (!in_array($val['type'], ['radio', 'checkbox', 'select', 'array'])) {
                unset($list[$key]['option']);
            }
            if (isset($permission[$val['name']])) {
                $list[$key]['value'] = $permission[$val['name']];
            }
        }
        foreach ($list as $key => $val) {
            $columns[$key] = array_values($val);
        }

        // 权限项按功能分类展示，避免 30+ 开关堆在同一页。
        $groups = $this->groupPermissionColumns($columns);

        // 构建页面
        return $this->formBuilder
            ->addGroup($groups)
            ->setFormUrl((string)url('permission', ['id' => $id]))
            ->fetch();
    }

    /**
     * 权限项按功能拆分为顶部切换式 tab。
     * @param array $columns add{Type} 入参格式：[type, name, title, tips, option, value, ...]
     * @return array 关联数组：tab 标题 => 字段数组
     */
    private function groupPermissionColumns(array $columns): array
    {
        $maps = [
            '网站访问' => ['visit_website', 'available_invite_count', 'publish_url'],
            '内容发布' => [
                'publish_question_enable', 'publish_article_enable', 'publish_answer_enable',
                'create_topic_enable', 'publish_question_num', 'publish_article_num',
            ],
            '审核设置' => [
                'publish_question_approval', 'publish_article_approval', 'publish_answer_approval',
                'modify_answer_approval', 'modify_article_approval', 'modify_question_approval',
                'publish_approval_time_start', 'publish_approval_time_end',
            ],
            '内容管理' => [
                'topic_manager', 'edit_content_topic', 'modify_article', 'remove_article',
                'modify_question', 'remove_question', 'set_best_answer', 'recommend_post',
                'set_top_post', 'modify_answer', 'remove_answer', 'lock_topic', 'remove_topic',
                'redirect_question', 'lock_question', 'merge_topic',
            ],
        ];
        $grouped = [];
        foreach ($maps as $title => $names) {
            $grouped[$title] = [];
        }
        foreach ($columns as $column) {
            $name = (string) ($column[1] ?? '');
            $target = '';
            foreach ($maps as $title => $names) {
                if (in_array($name, $names, true)) {
                    $target = $title;
                    break;
                }
            }
            $grouped[$target ?: '其他权限'][] = $column;
        }
        foreach ($grouped as $title => $items) {
            if (!$items) {
                unset($grouped[$title]);
            }
        }
        return $grouped;
    }
}
