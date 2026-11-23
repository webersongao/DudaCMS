<?php

namespace app\common\library\builder;
use app\common\library\helper\ArrayHelper;
use app\common\library\helper\DateHelper;
use app\common\library\helper\TreeHelper;
use think\facade\Request;

class MakeBuilder
{
    protected static $instance = null;
    protected static $prefix = '';
    /**
     * @param bool $refresh
     * @return MakeBuilder
     */
    final public static function getInstance(bool $refresh = false): MakeBuilder
    {
        if (is_null(self::$instance) || $refresh) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public static function getPrefix()
    {
        return self::$prefix ?: self::$prefix = app()->db->getConfig('connections.mysql.prefix');
    }

    /**
     * 所有数据库表详情
     * @param string $table
     * @return array
     */
    public function tablesInfo(string $table = ''): array
    {
        $prefix = self::getPrefix();
        $tableName = $table;
        if ($table) {
            $table = static::safeTableName($table);
            $table = " AND TABLE_NAME = '{$prefix}{$table}'";
        }
        $sql = "SELECT TABLE_NAME as `table`,TABLE_COMMENT as `comment`,TABLE_ROWS as `rows`,ENGINE as `engine` FROM information_schema.TABLES WHERE TABLE_SCHEMA = :table " . $table;
        $list = \think\facade\Db::query($sql, ['table' => app()->db->getConfig('connections.mysql.database')]);
        foreach ($list as $k => $v) {
            $list[$k] = [
                'table' => $prefix && 0 === strpos($v['table'], $prefix) ? substr($v['table'], strlen($prefix)) : $v['table'],
                'title' => $v['comment'],
                'remark' => $v['comment'],
                'top_button' => 'add,delete,export',
                'right_button' => 'edit,delete',
                'status' => 0,
                'page' => 1,
                'pk' => $this->getPrimary($tableName),
                'pid_field'=>'pid',
                'menu_pid' => 0
            ];
        }
        return $table ? $list[0]:$list;
    }

    /**
     * 所有表中字段详情
     * @param string $table
     * @param mixed $field
     * @return array
     */
    public function fieldsInfo(string $table,$field = null): array
    {
        $fields = \think\facade\Db::query("SHOW full columns FROM " . self::getPrefix() . $table);
        $lists = [];
        is_string($field) && $field = (array)$field;
        $weight = 10;
        foreach ($fields as $info) {
            if (null === $field || in_array($info['Field'], $field)) {
                $auto_increment = strpos($info['Extra'], 'auto_increment') !== false;
                $list = [
                    'table' => $table,
                    'name' => $info['Comment'] ?: $info['Field'],
                    'field' => $info['Field'],
                    'sort' => $weight,
                    "is_list" => 1,
                    "is_add" => 1,
                    "is_edit" => 1,
                    "is_search" => 0,
                    "is_sort" => 0,
                    'is_pk' => 0,
                    'relation_db' => '',      //关联表
                    'relation_field' => '',    //关联表字段
                    "search_type" => "=",
                    'settings' => json_encode([
                        'default' => $info['Default'],
                        'extra_attr' => '',
                        'extra_class' => '',
                        'placeholder' => '',
                        'param' => [],
                        'format'=>'Y-m-d',
                        'step'=>1,
                        'fieldType'=>$info['Type']
                    ],JSON_UNESCAPED_UNICODE),
                    "type" => "text",
                    'tips' => '',           // 表单提示
                    'required' => 0,//是否必填
                    'minlength' => 1,//最小长度
                    'maxlength' => 255,//最大长度
                ];

                if ($info['Key'] === 'PRI') { // 主键默认值
                    $list['sort'] = 1;
                    $list['is_pk'] = 1;
                    if ($auto_increment) {
                        $list['type'] = 'hidden';
                    }
                }

                if ($list['field'] == 'update_time' || $list['field'] == 'create_time') {
                    $list['type'] = 'datetime';
                }

                if ($list['field'] == 'sort') {
                    $list['type'] = 'sort';
                }

                if ($list['field'] == 'icon') {
                    $list['type'] = 'icon';
                }

                if ($list['field'] == 'status') {
                    $list['type'] = 'status';
                }

                $lists[] = $list;
            }
            $weight += 5;
        }
        return $lists;
    }

    /**
     * 表名白名单：仅允许字母、数字与下划线，非法输入返回空串。
     */
    protected static function safeTableName(string $str): string
    {
        return preg_match('/^[A-Za-z0-9_]+$/', $str) ? $str : '';
    }

    /**
     * 获取列表显示字段
     * @param $table
     * @return mixed
     */
    public function getListField($table)
    {
        // 表结构以数据库为准：不再依赖后台可自定义的 curd_field 表。
        return array_column($this->fieldsInfo($table), 'field');
    }

    /**
     * 返回单表信息
     * @param string $table
     * @return mixed
     */
    public function table(string $table)
    {
        return $this->tablesInfo($table);
    }

    /**
     * 返回单条字段信息
     * @param string $table
     * @param string $field
     * @return array
     */
    public function field(string $table, string $field): array
    {
        $list = $this->fieldsInfo($table, $field);
        $info = $list[0] ?? [];
        if (isset($info['settings']) && is_string($info['settings'])) {
            $info['settings'] = json_decode($info['settings'], true) ?: [];
        }
        return $info;
    }

    /**
     * 获取表字段信息
     * @param $table
     * @return array
     */
    public function fields($table): array
    {
        $tableInfo = $this->table($table);
        // 非空判断
        if (!$tableInfo) {
            return [];
        }
        // 字段信息直接来自 information_schema，后台不允许自定义表结构。
        $fields = $this->fieldsInfo($table);
        foreach ($fields as &$field) {
            $field['is_pk'] = !empty($field['is_pk']) || $field['field'] === ($tableInfo['pk'] ?? '') ? 1 : 0;
            $field['settings'] = json_decode($field['settings'], true) ?: [];
        }
        return $fields;
    }

    /**
     * 获取表的主键
     * @param string $table 表名称
     * @return mixed|string
     */
    public function getPrimaryKey(string $table = '')
    {
        return $this->getPrimary($table) ?: 'id';
    }

    /**
     * 获取列表页面可展示的字段信息
     * @param string $table 表名称
     * @return array
     */
    public function getListColumns(string $table = ''): array
    {
        $columns = [];
        $fields = $this->fields($table);
        foreach ($fields as &$field) {
            if ($field['is_list']) {
                // 默认值
                $default = $field['settings']['default'] ??'';
                // 排序
                $sortable = $field['is_sort'] ? 'true' : 'false';
                $param = $field['settings']['param'] ?? [];
                $class = $field['settings']['extra_class'] ?? '';
                // 添加到返回数组中
                $columns[] = [$field['field'], $field['name'], $field['type'], $default, $param, $class, $sortable];
            }
        }
        return $columns;
    }

    /**
     * 获取搜索字段
     * @param $table
     * @return array
     */
    public function getSearchColumn($table): array
    {
        $columns = [];
        $fields = $this->fields($table);
        foreach ($fields as &$field)
        {
            if ($field['is_search']) {
                $param = isset($field['settings']['param']) ? ArrayHelper::strToArr($field['settings']['param']) : '';
                $item['type'] = $field['table_type'] ?? '';  // 字段类型
                $item['name'] = $field['field'] ?? '';  // 字段名称
                $item['title'] = $field['title'] ?? '';  // 字段别名
                $item['option'] = $field['search_type'] ?? '='; // 匹配方式
                $item['default'] = $field['default'] ?? '';  // 默认值
                $item['param'] = $param ?? [];  // 额外参数
                $item['data_source'] = $field['data_source'] ?? 0;   // 数据源
                $item['relation_db'] = $field['relation_db'] ?? '';  // 模型关联
                $item['relation_field'] = $field['relation_field'] ?? '';  // 关联字段
                $item['field_id'] = $field['weight'] ?? 0;   // 字段编号
                // 添加到返回数组中
                $columns[] = array_values($item);
            }
        }
        return $columns;
    }

    /**
     * 获取添加页面可展示的字段信息
     * @param string $table 表名称
     * @param array $info
     * @return array
     */
    public function getAddColumns(string $table = '', $info = []): array
    {
        $fields = $this->fields($table);
        $columns = [];
        foreach ($fields as &$field) {
            // 非主键字段判断是否可添加
            if (strpos(strtolower(Request::action()), 'add') !== false && $field['is_add'] != 1) {
                continue;
            }

            // 非主键字段判断是否可修改
            if ($field['is_pk'] != 1 && strpos(strtolower(Request::action()), 'edit') !== false && $field['is_edit'] != 1) {
                continue;
            }

            // 状态为0的字段不可新增或修改
            if ($field['is_pk'] != 1 && isset($field['status']) && $field['status'] == 0) {
                continue;
            }

            if ($info) {
                if (isset($info[$field['field']])) {
                    $field['settings']['default'] = $info[$field['field']] ?? $field['default'];
                    // 禁止修改
                    if ($field['is_edit'] != 1) {
                        $field['settings']['extra_attr'] = isset($field['settings']['extra_attr']) ? $field['settings']['extra_attr'] . ' readonly = "readonly"' : '';
                    }
                }
            }
            $options = $this->getFieldOptions($field);

            $field['options'] = $options ?? [];

            // 必填项转换
            $field['required'] = $field['required'] == 1;
            // 添加到返回数组中,注意form构建器和table构建器的不一致
            if ($field['type'] == 'text') {
                $columns[] = [
                    $field['type'],                // 类型
                    $field['field'],               // 字段名称
                    $field['name'],                // 字段别名
                    $field['tips'],                // 提示信息
                    $field['settings']['default']??'',    // 默认值
                    '',               // 标签组，可以在文本框前后添加按钮或者文字
                    $field['settings']['extra_attr']??'', // 额外属性
                    $field['settings']['extra_class']??'',// 额外CSS
                    $field['settings']['placeholder']??'',// 占位符
                    $field['required'],            // 是否必填
                ];
            } elseif ($field['type'] == 'icon') {
                $columns[] = [
                    $field['type'],                // 类型
                    $field['field'],               // 字段名称
                    $field['name'],                // 字段别名
                    $field['tips'],                // 提示信息
                    $field['settings']['default'] ?? '',    // 默认值
                    '',               // 标签组，可以在文本框前后添加按钮或者文字
                    $field['settings']['extra_attr'] ?? '', // 额外属性
                    $field['settings']['extra_class'] ?? '', // 额外CSS
                    $field['settings']['placeholder'] ?? '', // 占位符
                    $field['required'],            // 是否必填
                ];
            } elseif ($field['type'] == 'textarea' || $field['type'] == 'password') {
                $columns[] = [
                    $field['type'],                // 类型
                    $field['field'],               // 字段名称
                    $field['name'],                // 字段别名
                    $field['tips'],                       // 提示信息
                    $field['settings']['default']??'',           // 默认值
                    $field['settings']['extra_attr']??'',        // 额外属性
                    $field['settings']['extra_class'] ?? '', // 额外CSS
                    $field['settings']['placeholder'] ?? '', // 占位符
                    $field['required'],                   // 是否必填
                ];
            } elseif ($field['type'] == 'radio' || $field['type'] == 'checkbox') {
                $columns[] = [
                    $field['type'],                // 类型
                    $field['field'],               // 字段名称
                    $field['name'],                // 字段别名
                    $field['tips'],                // 提示信息
                    $field['options'],             // 选项（数组）
                    $field['settings']['default']??'',    // 默认值
                    $field['settings']['extra_attr']??'', // 额外属性 extra_attr
                    $field['settings']['extra_class']??'',                            // 额外CSS extra_class
                    $field['required'],            // 是否必填
                ];
            } elseif ($field['type'] == 'select') {
                $columns[] = [
                    $field['type'],                // 类型
                    $field['field'],               // 字段名称
                    $field['name'],                // 字段别名
                    $field['tips'],                       // 提示信息
                    $field['options'],                    // 选项（数组）
                    $field['settings']['default']??'',           // 默认值
                    $field['settings']['extra_attr']??'',        // 额外属性
                    $field['settings']['extra_class'] ?? '', // 额外CSS
                    $field['settings']['placeholder'] ?? '', // 占位符
                    $field['required'],                   // 是否必填
                ];
            } elseif ($field['type'] == 'select2') {
                $columns[] = [
                    $field['type'],                // 类型
                    $field['field'],               // 字段名称
                    $field['name'],                // 字段别名
                    $field['tips'],                       // 提示信息
                    $field['options'],                    // 选项（数组）
                    $field['settings']['default']??'',           // 默认值
                    $field['settings']['extra_attr']??'',        // 额外属性
                    $field['settings']['extra_class'] ?? '', // 额外CSS
                    $field['settings']['placeholder'] ?? '', // 占位符
                    $field['required'],                   // 是否必填
                    $field['settings']['ajax']??'',                             // ajax请求地址
                ];
            } elseif ($field['type'] == 'number') {
                $columns[] = [
                    $field['type'],                // 类型
                    $field['field'],               // 字段名称
                    $field['name'],                // 字段别名
                    $field['tips'],                       // 提示信息
                    $field['settings']['default'],           // 默认值
                    $field['minlength'],                                   // 最小值
                    $field['maxlength'],                                   // 最大值
                    $field['settings']['step']??1,              // 步进值
                    $field['settings']['extra_attr']??'',        // 额外属性
                    $field['settings']['extra_class']??'',       // 额外CSS
                    $field['settings']['placeholder'] ?? '', // 占位符
                    $field['required'],                   // 是否必填
                ];
            } elseif ($field['type'] == 'hidden') {
                $columns[] = [
                    $field['type'],                      // 类型
                    $field['field'],                     // 字段名称
                    $field['settings']['default'] ?? '',    // 默认值
                    $field['settings']['extra_attr'] ?? '', // 额外属性 extra_attr
                ];
            } elseif ($field['type'] == 'date' || $field['type'] == 'time' || $field['type'] == 'datetime') {
                // 使用每个字段设定的格式
                if ($field['type'] == 'time') {
                    $format = $field['settings']['format'] ?: 'H:i:s';
                } else {
                    $format = $field['settings']['format'] ?: 'Y-m-d H:i:s';
                }
                $field['settings']['default'] = (int)$field['settings']['default'] > 0 && is_int($field['settings']['default']) ? date($format, $field['settings']['default']) : $field['settings']['default'];
                $columns[] = [
                    $field['type'],                // 类型
                    $field['field'],               // 字段名称
                    $field['name'],                // 字段别名
                    $field['tips'],                // 提示信息
                    $field['settings']['default']??'',    // 默认值
                    $field['settings']['format']??'',     // 日期格式
                    $field['settings']['extra_attr']??'', // 额外属性 extra_attr
                    $field['settings']['extra_class']??'',                            // 额外CSS extra_class
                    $field['settings']['placeholder']??'',// 占位符
                    $field['required'],            // 是否必填
                ];
            } elseif ($field['type'] == 'daterange') {
                $columns[] = [
                    $field['type'],                // 类型
                    $field['field'],               // 字段名称
                    $field['name'],                // 字段别名
                    $field['tips'],                       // 提示信息
                    $field['settings']['default']??'',           // 默认值
                    $field['settings']['format']??'',            // 日期格式
                    $field['settings']['extra_attr'] ?? '',  // 额外属性
                    $field['settings']['extra_class'] ?? '', // 额外CSS
                    $field['required'],                   // 是否必填
                ];
            } elseif ($field['type'] == 'tag') {
                $columns[] = [
                    $field['type'],                // 类型
                    $field['field'],               // 字段名称
                    $field['name'],                // 字段别名
                    $field['tips'],                       // 提示信息
                    $field['settings']['default']??'',           // 默认值
                    $field['settings']['extra_attr'] ?? '',  // 额外属性
                    $field['settings']['extra_class'] ?? '', // 额外CSS
                    $field['required'],                   // 是否必填
                ];
            } elseif ($field['type'] == 'image' || $field['type'] == 'images' || $field['type'] == 'file' || $field['type'] == 'files') {
                // 多(图/文件)上传执行解析操作
                if ($field['type'] == 'images' || $field['type'] == 'files') {
                    $field['settings']['default'] = json_decode($field['settings']['default'], true);
                }

                $columns[] = [
                    $field['type'],                // 类型
                    $field['field'],               // 字段名称
                    $field['name'],                // 字段别名
                    $field['tips'],                       // 提示信息
                    $field['settings']['default'],           // 默认值
                    $field['settings']['extra_attr'] ?? '',  // 额外属性
                    $field['settings']['extra_class'] ?? '', // 额外CSS
                    $field['settings']['placeholder'] ?? '', // 占位符
                    $field['required'],                   // 是否必填
                ];
            } elseif ($field['type'] == 'editor') {
                $columns[] = [
                    $field['type'],                // 类型
                    $field['field'],               // 字段名称
                    $field['name'],                // 字段别名
                    $field['tips'],                       // 提示信息
                    $field['settings']['default'],           // 默认值
                    $field['settings']['height'] ?? 0,       // 高度
                    $field['settings']['extra_attr'] ?? '',  // 额外属性
                    $field['settings']['extra_class'] ?? '', // 额外CSS
                    $field['required'],                   // 是否必填
                ];
            } elseif ($field['type'] == 'color') {
                $columns[] = [
                    $field['type'],                // 类型
                    $field['field'],               // 字段名称
                    $field['name'],                // 字段别名
                    $field['tips'],                       // 提示信息
                    $field['settings']['default'],           // 默认值
                    $field['settings']['extra_attr'] ?? '',  // 额外属性
                    $field['settings']['extra_class'] ?? '', // 额外CSS
                    $field['settings']['placeholder'] ?? '', // 占位符
                    $field['required'],                   // 是否必填
                ];
            }
            // Button
        }
        return $columns;
    }

    /*获取验证*/
    public function getValidateRule($table): array
    {
        // 必填规则由各业务 validate 类定义，不再读取可自定义的字段配置表。
        return ['rule' => [], 'message' => []];
    }
    /**
     * 获取搜索条件
     * @param array $search
     * @return array
     */
    public function getWhere(array $search = []): array
    {
        $searchWhere = [];
        foreach ($search as $key => $val) {
            $searchWhere[$key] = [
                $val[0],
                $val[1],
                $val[2],
                $val[3] ?? '=',
                $val[4] ?? '',
                $val[5] ?? [],
                $val[6] ?? 0,
            ];
        }
        //全局查询条件
        $where = [];
        // 循环所有搜索字段，看是否有传递
        foreach ($searchWhere as $k => $v) {
            if (Request::param($v[1]) || Request::param($v[1]) === "0") {
                $searchKeywords = Request::param($v[1]);
                // 判断字段类型，默认为=
                if (isset($v[3]) && !empty($v[3])) {
                    $option = $v[3];
                } else {
                    $option = '=';
                }
                switch ($v[0]) {
                    case 'select':
                    case 'text':
                        if (strtoupper($option) == 'LIKE') {
                            $where[] = [$v[1], $option, '%' . $searchKeywords . '%'];
                        } else {
                            $where[] = $searchKeywords!='' ? [$v[1], $option, $searchKeywords] : [];
                        }
                        break;
                    case 'time':
                    case 'datetime':
                    case 'date':
                        $getDateRange = DateHelper::dateRange($searchKeywords);
                        $where[] = [$v[1], 'between', $getDateRange];
                        break;
                    // 默认都当作文本框
                    default:
                        if (strtoupper($option) == 'LIKE') {
                            $where[] = [$v[1], $option, '%' . $searchKeywords . '%'];
                        } else {
                            $where[] = $searchKeywords ? [$v[1], $option, $searchKeywords] : [];
                        }
                }
            }
        }
        return $where;
    }

    public function getFieldOptions(array $field): array
    {
        // 0 字段本身，2 关联表（原 1=系统字典分支随字典模块移除而删除）
        $dataSource = $field['data_source'] ?? 0;
        if ($dataSource == 2) {
            if ( $field['type'] == 'select2') {
                $result = [];
            } else {
                // 取出对应模型的所有数据
                $module = db($field['relation_db']);
                // 排序与主键均从真实表结构推导
                $order = $this->getOrder($field['relation_db']);
                $pk = $this->getPk($field['relation_db']);

                // 当表中包含 pid/parent_id 时格式化展示效果
                $fieldPid = '';
                $relatedFields = array_column($this->fieldsInfo($field['relation_db']), 'field');
                if (in_array('pid', $relatedFields, true)) {
                    $fieldPid = ',pid';
                } elseif (in_array('parent_id', $relatedFields, true)) {
                    $fieldPid = ',parent_id';
                }
                // 获取数据列表
                $result = $module->field($pk . ',' . $field['relation_field'] . $fieldPid)
                    ->order($order)
                    ->select()
                    ->toArray();
                $result = $this->changeSelect($result);
            }
        } else {
            $result = [];
        }

        return $result;
    }

    private function getPk(string $table)
    {
        return $this->getPrimary($table) ?: 'id';
    }

    private function changeSelect(array $array): array
    {
        $result = [];
        // 当元素个数为3时执行tree操作
        if ($array && count(($array[0])) == 3) {
            $array = TreeHelper::treeThree($array);
        }

        foreach ($array as &$arr) {
            if (count($arr) == 2) {
                $result[current($arr)] = end($arr);
            } else {
                $keys = array_keys($arr);
                $result[$arr[$keys[0]]] = $arr[$keys[1]];
            }
        }
        return $result;
    }

    private function getOrder(string $table): string
    {
        $pk = $this->getPk($table);
        $fields = array_column($this->fieldsInfo($table), 'field');
        return in_array('sort', $fields, true) ? 'sort ASC,' . $pk . ' DESC' : $pk . ' DESC';
    }

    /**
     * 获取表主键
     * @param $table
     * @return mixed
     */
    public function getPrimary($table){
        $database = config('database.connections.mysql.database');
        $table = get_table($table);
        $sql = "SELECT k.column_name  FROM
             information_schema.table_constraints t
         JOIN
             information_schema.key_column_usage k
         USING
             (constraint_name,table_schema,table_name)
         WHERE
             t.constraint_type='PRIMARY KEY'
         AND
             t.table_schema='$database'
         AND
             t.table_name='$table'";
        $rows = \think\facade\Db::query($sql);
        $row  = $rows[0] ?? [];

        // MySQL 8 的 information_schema 字段名可能返回大写 COLUMN_NAME
        return $row['column_name'] ?? $row['COLUMN_NAME'] ?? 'id';

    }
}
