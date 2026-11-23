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
use app\model\Config as ConfigModel;

/**
 * 系统配置
 *
 * 后台只展示真实业务功能，不再支持在后台自定义配置定义（配置管理/配置分组）。
 * 配置项的增删改已收敛到代码与安装/升级脚本，此处仅保留"系统配置"表单，
 * 供管理员修改站点名称、Logo、功能开关等已有配置值。
 */
class Config extends Backend
{
    protected $table = 'config';

    public function initialize()
    {
        parent::initialize();
        $this->model = new ConfigModel();
    }

    /**
     * @param int|null $group      配置分组 id
     * @param bool     $showTabs   是否渲染并列的配置分组 tab。
     *                             主题风格/编辑器/短信配置已提升为系统管理一级入口，固定渲染且不带 tab。
     */
    public function config($group = null, $showTabs = true)
    {
        // 分组在 GET 与 POST 阶段都需要：POST 时用于补齐未提交的 checkbox 空值。
        $group = $group !== null ? (int) $group : (int) $this->request->param('group', 1);

        if ($this->request->isPost()) {
            if ($data = $this->request->post()) {
                foreach ($data as $k => $v) {
                    if (is_array($v) && isset($v['key']) && isset($v['value'])) {
                        $value = [];
                        foreach ($v['key'] as $k1 => $v1) {
                            $value[$v1] = $v['value'][$k1];
                        }
                        $data[$k] = $value;
                    }
                }
                // checkbox 全部取消勾选时浏览器不会提交该字段，服务端显式补空值，
                // 否则 isset 判断跳过该项，出现"提示成功但刷新后未保存"的问题。
                // 仅补当前分组的 checkbox，避免影响其他分组表单。
                $checkboxNames = $this->model->where('group', $group)->where('type', 'checkbox')->column('name');
                foreach ($checkboxNames as $checkboxName) {
                    if (!isset($data[$checkboxName])) {
                        $data[$checkboxName] = '';
                    }
                }
                $configList = [];
                foreach (db('config')->select()->toArray() as $v) {
                    if (isset($data[$v['name']])) {
                        $value = $data[$v['name']];
                        $option = json_decode($v['option'], true);
                        if (in_array($v['type'], ['array', 'images', 'files'])) {
                            $option = $value;
                            $value = 0;
                        } else {
                            $value = is_array($value) ? implode(',', $value) : $value;
                        }
                        $v['value'] = $value;
                        $v['option'] = json_encode($option, JSON_UNESCAPED_UNICODE);
                        $configList[] = $v;
                    }
                }
                $result = $this->model->saveAll($configList);
                if ($result) {
                    $this->success('修改成功');
                } else {
                    $this->error('提交失败或数据无变化');
                }
            }
        }

        // 让表单提交地址携带 group，POST 时才能按分组补齐未提交的 checkbox。
        $this->formBuilder->setFormUrl((string) url('config', ['group' => $group]));
        $columns = [];
        $list = $this->model->where('group', $group)->order(['sort' => 'ASC', 'id' => 'ASC'])->column('type,name,title,tips,option,value');
        foreach ($list as $key => $val) {
            if (in_array($val['type'], ['editor', 'textarea', 'code', 'html'])) {
                $val['value'] = htmlspecialchars_decode($val['value']);
            }

            // 字典管理模块已移除，选项统一从 option(JSON) 读取，不再查 dask_dict。
            $val['option'] = json_decode($val['option'], true);
            if (!in_array($val['type'], ['radio', 'checkbox', 'select', 'array', 'images', 'files', 'select2'])) {
                unset($val['option']);
            }
            $columns[$key] = array_values($val);
        }
        // 第三方登录：按平台拆为顶部切换式 tab，避免 19 个配置项平铺堆在一页。
        if ((int) $group === 11) {
            $groups = $this->groupThirdColumns($columns);
            return $this->formBuilder
                ->setPageTitle('第三方登录')
                ->addGroup($groups)
                ->fetch();
        }
        // 主题风格已提升为系统管理一级入口：按其配置项前缀拆为顶部切换式 tab，
        // 按功能分组展示（发现页/问答页面/…），避免 32 个配置项堆在一页。
        if ((int) $group === 12) {
            $groups = $this->groupThemeColumns($columns);
            return $this->formBuilder
                ->setPageTitle('主题风格')
                ->addGroup($groups)
                ->fetch();
        }
        // 短信配置：按服务商拆为顶部切换式 tab，避免通用开关与两家平台凭证堆在一页。
        if ((int) $group === 14) {
            $groups = $this->groupSmsColumns($columns);
            return $this->formBuilder
                ->setPageTitle('短信配置')
                ->addGroup($groups)
                ->fetch();
        }

        // 编辑器/短信配置为系统管理一级入口，固定渲染，不再并列显示配置 tab。
        $showTabs = $showTabs && !in_array((int) $group, [13, 14], true);
        $links = [];
        if ($showTabs) {
            // 配置分组为内置常量，group id 作为常量 key。
            $filedGroup = config('dask.config_group');
            foreach ($filedGroup as $groupId => $groupName) {
                $links[] = [
                    'link' => (string)url('config', ['group' => $groupId]),
                    'title' => $groupName,
                    'active' => $group == $groupId ? 1 : 0
                ];
            }
        }

        return $this->formBuilder
            ->addLinkGroup($columns, $links)
            ->fetch();
    }

    /**
     * 短信配置项按服务商拆分为顶部切换式 tab。
     *
     * 分组顺序：通用设置 / 阿里云短信 / 腾讯云短信。
     * 字段名格式：sms_{platform}_xxx；服务商开关 sms_enable 归入通用设置。
     *
     * @param array $columns add{Type} 入参格式：[type, name, title, tips, option, value, ...]
     * @return array 关联数组：tab 标题 => 字段数组
     */
    private function groupSmsColumns(array $columns): array
    {
        $tabs = [
            'ali'     => '阿里云短信',
            'tencent' => '腾讯云短信',
        ];
        $generalTitle = '通用设置';
        $grouped = [$generalTitle => []];
        foreach ($tabs as $title) {
            $grouped[$title] = [];
        }
        foreach ($columns as $column) {
            $name = (string) ($column[1] ?? '');
            $tabTitle = $generalTitle;
            foreach ($tabs as $platform => $title) {
                if (strpos($name, 'sms_' . $platform . '_') === 0) {
                    $tabTitle = $title;
                    break;
                }
            }
            $grouped[$tabTitle][] = $column;
        }
        // 移除空 tab，避免出现无内容分组。
        foreach ($grouped as $title => $items) {
            if (!$items) {
                unset($grouped[$title]);
            }
        }
        return $grouped;
    }

    /**
     * 第三方登录配置项按平台拆分为顶部切换式 tab。
     *
     * 分组顺序：通用设置 / QQ / 微信 / 微博 / GitHub / Google / Twitter。
     * 字段名格式：third_{platform}_xxx；启用方式 third_enable 归入通用设置。
     *
     * @param array $columns add{Type} 入参格式：[type, name, title, tips, option, value, ...]
     * @return array 关联数组：tab 标题 => 字段数组
     */
    private function groupThirdColumns(array $columns): array
    {
        $tabs = [
            'qq'      => 'QQ',
            'wechat'  => '微信',
            'weibo'   => '微博',
            'github'  => 'GitHub',
            'google'  => 'Google',
            'twitter' => 'Twitter/X',
        ];
        $generalTitle = '通用设置';
        $grouped = [$generalTitle => []];
        foreach ($tabs as $title) {
            $grouped[$title] = [];
        }
        foreach ($columns as $column) {
            $name = (string) ($column[1] ?? '');
            $tabTitle = $generalTitle;
            foreach ($tabs as $platform => $title) {
                if (strpos($name, 'third_' . $platform . '_') === 0) {
                    $tabTitle = $title;
                    break;
                }
            }
            $grouped[$tabTitle][] = $column;
        }
        // 移除空 tab，避免出现无内容分组。
        foreach ($grouped as $title => $items) {
            if (!$items) {
                unset($grouped[$title]);
            }
        }
        return $grouped;
    }

    /**
     * 主题风格配置项按 name 前缀拆分为顶部切换式 tab。
     * 分组顺序：发现页/问答页面/文章页面/专栏页/通用设置/问题详情/文章详情/移动端首页。
     * 字段名格式：t_{前缀}_xxx，如 t_hm_search_enable → hm。
     *
     * @param array $columns add{Type} 入参格式：[type, name, title, tips, option, value, ...]
     * @return array 关联数组：tab 标题 => 字段数组
     */
    private function groupThemeColumns(array $columns): array
    {
        $tabs = [
            'hm'  => '发现页',
            'qs'  => '问答页面',
            'art' => '文章页面',
            'col' => '专栏页',
            'cm'  => '通用设置',
            'qd'  => '问题详情',
            'ad'  => '文章详情',
            'mb'  => '移动端首页',
        ];
        $grouped = [];
        foreach ($tabs as $prefix => $title) {
            $grouped[$title] = [];
        }
        foreach ($columns as $column) {
            $name = $column[1] ?? '';
            // 取 t_{prefix}_ 中的 prefix；未识别前缀统一归入通用设置，避免配置项丢失。
            $key = '';
            if (preg_match('/^t_([a-z]+)_/', (string) $name, $m)) {
                $key = $m[1];
            }
            $tabTitle = isset($tabs[$key]) ? $tabs[$key] : '通用设置';
            $grouped[$tabTitle][] = $column;
        }
        // 移除空 tab，避免出现无内容分组。
        foreach ($grouped as $title => $items) {
            if (!$items) {
                unset($grouped[$title]);
            }
        }
        return $grouped;
    }

    /**
     * 主题风格：系统管理一级入口，固定渲染 group=12 的配置表单。
     */
    public function theme()
    {
        return $this->config(12, false);
    }

    /**
     * 编辑器：系统管理一级入口，固定渲染 group=13 的配置表单。
     */
    public function editor()
    {
        return $this->config(13, false);
    }

    /**
     * 短信配置：系统管理一级入口，固定渲染 group=14 的配置表单。
     */
    public function sms()
    {
        return $this->config(14, false);
    }
}
