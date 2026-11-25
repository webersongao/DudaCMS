<?php
namespace app\common\traits;

use app\common\library\helper\ArrayHelper;
use app\common\library\helper\ExcelHelper;
use app\model\admin\AdminLog;
use app\service\StorageCleanupService;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use think\exception\ValidateException;
use think\facade\Request;

trait Curd
{
    protected function getPk()
    {
        $pk =  $this->makeBuilder->getPrimaryKey($this->table) ?: $this->pk;
        return $pk ?: 'id';
    }

    protected function table()
    {
        return $this->makeBuilder->table($this->table);
    }

    //首页
    public function index()
    {
        $tableInfo = $this->table();
        $columns = $this->makeBuilder->getListColumns($this->table);
        $search = $this->makeBuilder->getSearchColumn($this->table);
        if ($this->request->param('_list'))
        {
            $isAsc = $this->request->param('isAsc')=='asc' ? 'DESC': 'ASC';
            $where = $this->makeBuilder->getWhere($search);
            $fields = $this->makeBuilder->getListField($tableInfo['id']);

            if($tableInfo['page'])
            {
                // TODO 获取关联表信息
                return db($this->table)
                    ->where($where)
                    ->field($fields)
                    ->order([$this->getPk() => $isAsc])
                    ->paginate([
                        'query'     => Request::get(),
                        'list_rows' =>$this->request->param('pageSize',get_setting("contents_per_page",15)),
                    ])
                    ->toArray();
            }else{
                $list = db($this->table)
                    ->where($where)
                    ->field($fields)
                    ->order([$this->getPk() => $isAsc])
                    ->select()
                    ->toArray();
                return [
                    'total' => count($list),
                    'per_page' => 10000,
                    'current_page' => 1,
                    'last_page' => 1,
                    'data' => $list,
                ];
            }
        }
        // 构建页面
        return $this->tableBuilder
            ->setUniqueId($this->getPk())
            ->addColumns($columns)
            ->setSearch($search)
            ->addColumn('right_button', '操作', 'btn')
            ->addRightButtons($tableInfo['right_button'])        // 设置右侧操作列
            ->addTopButtons($tableInfo['top_button'])            // 设置顶部按钮组
            ->setPagination($tableInfo['page'] ? 'true' : 'false')                        // 关闭分页显示
            ->fetch();
    }

    //新增
    public function add()
    {
        if($this->request->isPost())
        {
            $data = db_input_normalize($this->table, $this->request->except(['file'], 'post'));

            $validate = $this->makeBuilder->getValidateRule($this->table);
            if($validate)
            {
                try {
                    validate($validate['rule'],$validate['message'])->check($data);
                } catch (ValidateException $e) {
                    $this->error($e->getError());
                }
            }
            $result = db($this->table)->insert($data);
            if ($result) {
                $this->success('添加成功','index');
            } else {
                $this->error('添加失败');
            }
        }
        return $this->formBuilder
            ->addFormItems($this->makeBuilder->getAddColumns($this->table))
            ->fetch();
    }

    //编辑
    public function edit()
    {
        if ($this->request->isPost())
        {
            $data = db_input_normalize($this->table, $this->request->except(['file'], 'post'));
            $validate = $this->makeBuilder->getValidateRule($this->table);
            if($validate)
            {
                try {
                    validate($validate['rule'],$validate['message'])->check($data);
                } catch (ValidateException $e) {
                    $this->error($e->getError());
                }
            }

            $pk = $this->getPk();
            $oldRow = isset($data[$pk]) ? db($this->table)->where($pk, $data[$pk])->find() : null;
            $result = db($this->table)->update($data);
            if ($result) {
                StorageCleanupService::cleanupUpdatedRecord($this->table, $data, null, $pk, $oldRow ?: null);
                $this->success('修改成功', 'index');
            } else {
                $this->error('提交失败或数据无变化');
            }
        }
        $id= $this->request->param('id');
        $info = db($this->table)->where($this->getPk(),$id)->find();
        return $this->formBuilder
            ->addFormItems($this->makeBuilder->getAddColumns($this->table,$info))
            ->fetch();
    }

    // 删除
    public function delete()
    {
        if ($this->request->isPost())
        {
            $id= $this->request->post('id');
            if (strpos($id, ',') !== false)
            {
                $ids = explode(',',$id);
                //存在状态字段，就执行逻辑删除，不存在物理删除
                /*if(db_field_exits($this->table, 'status'))
                {
                    if(db($this->table)->whereIn($this->getPk(),$ids)->update(['status'=>0]))
                    {
                        AdminLog::recycle($this->table,$ids,0);
                        return json(['error'=>0, 'msg'=>'删除成功!']);
                    }
                }else{
                    if(db($this->table)->whereIn($this->getPk(),$ids)->delete())
                    {
                        AdminLog::recycle($this->table,$ids,1);
                        return json(['error'=>0, 'msg'=>'删除成功!']);
                    }
                }*/

                $rows = db($this->table)->whereIn($this->getPk(), $ids)->select()->toArray();
                if(db($this->table)->whereIn($this->getPk(),$ids)->delete())
                {
                    StorageCleanupService::cleanupDeletedRows($this->table, $rows ?: []);
                    return json(['error'=>0, 'msg'=>'删除成功!']);
                }
                return json(['error' => 1, 'msg' => '删除失败']);
            }

            //存在状态字段，就执行逻辑删除，不存在物理删除
            /*if(db_field_exits($this->table, 'status'))
            {
                if(db($this->table)->where($this->getPk(),$id)->update(['status'=>0]))
                {
                    AdminLog::recycle($this->table,$id,0);
                    return json(['error'=>0, 'msg'=>'删除成功!']);
                }
            }else{
                if(db($this->table)->where($this->getPk(),$id)->delete())
                {
                    AdminLog::recycle($this->table,$id,1);
                    return json(['error'=>0,'msg'=>'删除成功!']);
                }
            }*/

            $rows = db($this->table)->where($this->getPk(), $id)->select()->toArray();
            if(db($this->table)->where($this->getPk(),$id)->delete())
            {
                StorageCleanupService::cleanupDeletedRows($this->table, $rows ?: []);
                return json(['error'=>0,'msg'=>'删除成功!']);
            }
            return json(['error' => 1, 'msg' => '删除失败']);
        }
    }

    // 排序
    public function sort()
    {
        if (request()->isPost())
        {
            $input = request()->post();
            $id = db_input_int($input['id'] ?? 0, 0);
            $field = (string) ($input['field'] ?? '');
            $columns = db_table_columns($this->table);
            if ($id <= 0 || $field === '' || ($columns && !isset($columns[$field]))) {
                return json(['error' => 1, 'msg' => '排序参数不正确']);
            }
            $value = $columns ? db_input_field_value($this->table, $field, $input['sort'] ?? 0) : db_input_int($input['sort'] ?? 0);
            $info = db($this->table)->find($id);
            if (!$info) {
                return json(['error' => 1, 'msg' => '记录不存在']);
            }
            if ($info[$field] != $value)
            {
                db($this->table)->where($this->getPk(), $id)->update([$field => $value]);
                return json(['error' => 0, 'msg' => '修改成功!']);
            }
            return json(['error' => 0, 'msg' => '提交失败或数据无变化!']);
        }
    }

    //选择、单选
    public function choose()
    {
        if(request()->isPost())
        {
            $input = request()->post();
            $id = db_input_int($input['id'] ?? ($input['pk'] ?? 0), 0);
            $field = (string) ($input['field'] ?? ($input['name'] ?? ''));
            $columns = db_table_columns($this->table);
            if ($id <= 0 || $field === '' || ($columns && !isset($columns[$field]))) {
                return json(['error' => 1, 'msg' => '参数不正确']);
            }
            $rawValue = $input['value'] ?? '';
            $rawValue = is_array($rawValue) ? implode(',', $rawValue) : $rawValue;
            $value = $columns ? db_input_field_value($this->table, $field, $rawValue) : $rawValue;
            db($this->table)->where($this->getPk(), $id)->update([$field => $value]);
            return json(['error'=>0, 'msg'=>'修改成功!']);
        }
    }

    // 状态变更
    public function state()
    {
        if (request()->isPost())
        {
            $id = db_input_int(request()->post('id'), 0);
            $field = (string) request()->post('field');
            $columns = db_table_columns($this->table);
            if ($id <= 0 || $field === '' || ($columns && !isset($columns[$field]))) {
                return json(['error' => 1, 'msg' => '参数不正确']);
            }
            $info = db($this->table)->find($id);
            if (!$info) {
                return json(['error' => 1, 'msg' => '记录不存在']);
            }
            $status = ((int) ($info[$field] ?? 0)) === 1 ? 0 : 1;
            db($this->table)->where($this->getPk(), $id)->update([$field => $status]);
            return json(['error'=>0, 'msg'=>'修改成功!']);
        }
    }

    //导出数据
    public function export($type='')
    {
        $columns = $this->makeBuilder->getListColumns($this->table);
        $tableInfo = $this->table();
        $search = $this->makeBuilder->getSearchColumn($this->table);
        $where = $this->makeBuilder->getWhere($search);
        $param = $this->request->param();
        $isAsc = $param['isAsc'] ?? 'desc';
        $page = $tableInfo['page']??1;
        $title = $tableInfo['title']??'数据导出';
        $orderByColumn = $param['orderByColumn'] ?? 'id';
        unset($param['type'],$param['pageSize'],$param['page'],$param['searchValue'],$param['orderByColumn'],$param['isAsc']);
        if(!$page || $type=='all')
        {
            $list = db($this->table)
                ->where($where)
                ->where($param)
                ->order([$orderByColumn => $isAsc])
                ->select()
                ->toArray();
        }else{
            $param = ArrayHelper::arrayFilter($param);
            $where = ArrayHelper::arrayFilter($where);
            $list = db($this->table)
                ->where($where)
                ->where($param)
                ->order([$orderByColumn => $isAsc])
                ->paginate([
                    'page'=>$this->request->param('page',1),
                    'query'     => Request::get(),
                    'list_rows' =>$this->request->param('pageSize',get_setting("contents_per_page",15))
                ])
                ->toArray()['data'];
        }
        try {
            ExcelHelper::exportData($list, $columns, $title);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}