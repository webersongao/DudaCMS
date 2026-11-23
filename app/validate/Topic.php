<?php
namespace app\validate;
use think\Validate;
class Topic extends Validate
{
    protected $rule = [
        'title' => 'require|unique:topic,title|checkTitleLength',  // 增加自定义验证规则 checkTitleLength
        'description' => 'checkDescriptionLength',
    ];

    protected $message = [
        'title.require' => '请输入话题标题',
        'title.unique' => '话题标题已存在',
        'title.checkTitleLength' => '话题标题最多6个中文字符或12个英文字符',
        'description.checkDescriptionLength' => '话题描述最多120个字',
    ];

    // 编辑场景只校验描述，避免 title unique 规则与自身冲突
    protected $scene = [
        'description' => ['description'],
    ];

    // 话题描述最多 120 字（按去除标签后的纯文本计）
    protected function checkDescriptionLength($value)
    {
        $plain = trim(preg_replace('/\s+/u', ' ', strip_tags(htmlspecialchars_decode((string) $value))));
        return mb_strlen($plain, 'UTF-8') <= \app\model\Topic::DESCRIPTION_MAX_CHARS;
    }

    // 自定义验证规则
    protected function checkTitleLength($value, $rule, $data = [])
    {
        // 判断是否为中文字符
        if (preg_match("/[\x{4e00}-\x{9fa5}]/u", $value)) {
            // 计算中文字符长度
            if (mb_strlen($value, 'UTF-8') > 6) {
                return false;
            }
        } else {
            // 计算英文字符长度
            if (strlen($value) > 12) {
                return false;
            }
        }
        return true;
    }
}
