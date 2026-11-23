<?php
namespace app\backend\extend;
use app\common\controller\Backend;
use app\common\library\helper\RandomHelper;
use think\facade\Request;

class Token extends Backend
{
    protected $table = 'app_token';

    public function index()
    {
        $type = $this->request->param('type',1);
        if($type==1)
        {
            $columns = [
                ['id','编号'],
                ['title', '客户端名称'],
                ['token', '客户端Token'],
                ['version', '客户端APi版本'],
                ['create_time', '创建时间','datetime'],
            ];
        }else{
            $columns = [
                ['id','编号'],
                ['title', '客户端名称'],
                ['token', '客户端Token'],
                ['create_time', '创建时间','datetime'],
            ];
        }
        if ($this->request->param('_list'))
        {
            $type = $this->request->param('type',1);
            // 排序规则
            $orderByColumn = safe_order_column($this->request->param('orderByColumn') ?? 'id');
            $isAsc = safe_order_direction($this->request->param('isAsc') ?? 'desc');
            $pageSize = $this->request->param('pageSize',get_setting("contents_per_page",15));
            return db('app_token')
                ->where(['type'=>$type])
                ->order([$orderByColumn => $isAsc])
                ->paginate([
                    'query'     => Request::get(),
                    'list_rows' =>$pageSize,
                ])
                ->toArray();
        }
        return $this->tableBuilder
            ->setPageTips('模块Token请求接口端需添加AccessToken头标识和version标识，插件Token需添加ApiToken头')
            ->setUniqueId('id')
            ->addColumns($columns)
            ->setDataUrl(Request::baseUrl().'?_list=1&type='.$type)
            ->addColumn('right_button', '操作', 'btn')
            ->addRightButtons(['edit','delete'])
            ->addTopButtons(['add','delete'])
            ->setLinkGroup([
                [
                    'title'=>'模块',
                    'link'=>(string)url('index', ['type' => 1]),
                    'active'=> $type==1
                ],
                [
                    'title'=>'插件',
                    'link'=>(string)url('index', ['type' => 2]),
                    'active'=> $type==2
                ],
            ])
            ->fetch();
    }

    public function add()
    {
        if($this->request->isPost())
        {
            $data = $this->request->except(['file'],'post');
            $data['create_time'] = time();
            $data['token'] = RandomHelper::alpha(16);
            $result = db('app_token')->insert($data);
            if (!$result) {
                $this->error('添加失败');
            } else {
                $this->success('添加成功','index');
            }
        }
        return $this->formBuilder
            ->addText('title','客户端名称','填写客户端名称')
            ->addText('version','API版本','填写API版本')
            ->fetch();
    }

    public function edit($id=0)
    {
        if ($this->request->isPost())
        {
            $data =$this->request->except(['file'],'post');
            $result = db('app_token')->update($data);
            if ($result) {
                $this->success('修改成功', 'index');
            } else {
                $this->error('提交失败或数据无变化');
            }
        }

        $info = db('app_token')->where('id',$id)->find();
        return $this->formBuilder
            ->setFormData($info)
            ->addHidden('id')
            ->addText('title','客户端名称','填写客户端名称')
            ->addText('version','API版本','填写API版本')
            ->fetch();
    }
}