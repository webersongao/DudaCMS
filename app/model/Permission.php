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

use think\Model;

class Permission extends Model
{
    protected $name = 'users_permission';

    public static function updateUsersPermission()
    {
        $list = db('users_permission')->order('sort', 'asc')->column('type,name,title,option,value,group');

        $admin_permission = db('admin_group')->column('id,permission');

        $credit_permission = db('credit_group')->column('id,permission');

        //系统组权限
        foreach ($admin_permission as $k => $v) {
            $permission_option = json_decode($v['permission'], true);
            foreach ($list as $key => $val) {
                if ($val['group'] == 'system' || $val['group'] == 'common') {
                    if (!isset($permission_option[$val['name']])) {
                        $permission_option[$val['name']] = $val['value'];
                    }
                }
            }
            db('admin_group')->where(['id' => $v['id']])->update(['permission' => json_encode($permission_option, JSON_UNESCAPED_UNICODE)]);
        }
        //成长等级组权限
        foreach ($credit_permission as $k => $v) {
            $permission_option = json_decode($v['permission'], true);
            foreach ($list as $key => $val) {
                if ($val['group'] == 'credit' || $val['group'] == 'common') {
                    if (!isset($permission_option[$val['name']])) {
                        $permission_option[$val['name']] = $val['value'];
                    }
                }
            }
            db('credit_group')->where(['id' => $v['id']])->update(['permission' => json_encode($permission_option, JSON_UNESCAPED_UNICODE)]);
        }

        return true;
    }
}
