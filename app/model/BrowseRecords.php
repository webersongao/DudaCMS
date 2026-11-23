<?php
namespace app\model;
use think\facade\Request;

class BrowseRecords extends BaseModel
{
    /**
     * 记录用户浏览记录
     * @param $uid
     * @param $item_id
     * @param $item_type
     * @return bool
     */
    public static function recordViewLog($uid,$item_id,$item_type): bool
    {
        if(!$uid || !$item_id || !$item_type) return false;

        $cache_key = md5('cache_record_view_log_'.$item_type.'_'.$item_id.'_'.$uid);
        $cache_result = cache($cache_key);
        if($cache_result) {
            return true;
        }
        cache($cache_key,$cache_key,['expire'=>60]);
        // 每用户每内容仅保留一行：命中唯一键 (uid,item_type,item_id) 时 REPLACE 覆盖并刷新时间与可见态，
        // 避免重复行堆积
        return db('browse_records')->replace(true)->insert([
            'item_id'=>$item_id,
            'item_type'=>$item_type,
            'uid'=>$uid,
            'status'=>1,
            'create_time'=>time()
        ]);
    }

    /**
     * 各浏览记录类型对应的源表、查询字段与展示格式
     * 列表侧按类型批量预取，避免每行回表查询
     */
    protected static function browseItemTypes(): array
    {
        return [
            'question' => [
                'table'  => 'question',
                'fields' => 'id,title,detail',
                'where'  => ['status' => 1],
                'format' => function (array $info, $item_id) {
                    return [
                        'title'   => $info['title'],
                        'label'   => L('问答'),
                        'url'     => (string)url('question/detail', ['id' => $item_id]),
                        'content' => str_cut(strip_tags(htmlspecialchars_decode((string) $info['detail'])), 0, 200),
                    ];
                },
            ],
            'article' => [
                'table'  => 'article',
                'fields' => 'id,title,message',
                'where'  => ['status' => 1],
                'format' => function (array $info, $item_id) {
                    return [
                        'title'   => $info['title'],
                        'label'   => L('文章'),
                        'url'     => (string)url('article/detail', ['id' => $item_id]),
                        'content' => str_cut(strip_tags(htmlspecialchars_decode((string) $info['message'])), 0, 200),
                    ];
                },
            ],
            'column' => [
                'table'  => 'column',
                'fields' => 'id,name,description',
                'where'  => ['verify' => 1],
                'format' => function (array $info, $item_id) {
                    return [
                        'title'   => $info['name'],
                        'label'   => L('专栏'),
                        'url'     => (string)url('column/detail', ['id' => $item_id]),
                        'content' => str_cut((string) $info['description'], 0, 200),
                    ];
                },
            ],
            'topic' => [
                'table'  => 'topic',
                'fields' => 'id,title,description',
                'where'  => ['status' => 1],
                'format' => function (array $info, $item_id) {
                    return [
                        'title'   => $info['title'],
                        'label'   => L('话题'),
                        'url'     => (string)url('topic/detail', ['id' => $item_id]),
                        'content' => str_cut(strip_tags(htmlspecialchars_decode((string) $info['description'])), 0, 200),
                    ];
                },
            ],
        ];
    }

    /**
     * 获取用户浏览记录数据
     */
    public static function getRecordViewList($uid,$page=1,$per_page=10,$pjax='wrapMain')
    {
        if(!$uid) return false;

        $data = db('browse_records')
            ->where(['uid'=>$uid,'status'=>1])
            ->order('id','DESC')
            ->paginate([
                'query'     => Request::get(),
                'list_rows' =>$per_page,
                'page'=>$page,
                'pjax'=>$pjax
            ]);

        $pageVar = $data->render();
        $data = $data->toArray();
        $list = $data['data'];

        $result = [];

        if($list)
        {
            $item_types = self::browseItemTypes();

            //按类型收集本页需要的源数据ID
            $ids_by_type = [];
            foreach ($list as $v) {
                if(isset($item_types[$v['item_type']])) {
                    $ids_by_type[$v['item_type']][(int) $v['item_id']] = true;
                }
            }

            //每种类型一次查询取回本页全部源数据
            $items_by_type = [];
            foreach ($ids_by_type as $type => $ids) {
                $rows = db($item_types[$type]['table'])
                    ->whereIn('id', array_keys($ids))
                    ->where($item_types[$type]['where'])
                    ->field($item_types[$type]['fields'])
                    ->select()
                    ->toArray();
                foreach ($rows as $row) {
                    $items_by_type[$type][(int) $row['id']] = $row;
                }
            }

            foreach ($list as $k=>$v)
            {
                $result[$k] = $v;
                $type = $v['item_type'];
                if(isset($item_types[$type]))
                {
                    $item_id = (int) $v['item_id'];
                    if(!isset($items_by_type[$type][$item_id])) {
                        unset($result[$k]);
                        continue;
                    }
                    $result[$k] += $item_types[$type]['format']($items_by_type[$type][$item_id], $item_id);
                }else{
                    /*处理其他类型的浏览记录*/
                }
            }
        }

        $result_list['list'] = $result;
        $result_list['page'] = $pageVar;
        $result_list['total'] = $data['last_page'];

        return  $result_list;
    }
}