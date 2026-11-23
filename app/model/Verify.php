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

namespace app\model;

class Verify extends BaseModel
{
    protected $name = 'users_verify';

    //获取配置列表
    public static function getConfigList($map = []): array
    {
        $keyList = db('verify_field')->where($map)->select()->toArray();
        foreach ($keyList as $k => $v) {
            if (in_array($v['type'], ['select', 'checkbox'])) {
                $keyList[$k]['value'] = explode(',', $v['value']);
            } elseif ($v['type'] === 'array') {
                $keyList[$k]['value'] = json_decode($v['option'], true);
            }
            $keyList[$k]['option'] = json_decode($v['option'], true);
            $keyList[$k]['tips'] = htmlspecialchars($v['tips']);
        }
        return $keyList;
    }
}
