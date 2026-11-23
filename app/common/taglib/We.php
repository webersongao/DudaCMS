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
namespace app\common\taglib;

use think\template\TagLib;

/**
 * 前台模板标签库。
 *
 * 仅提供列表页使用的 question / article 两个标签。
 * 标签属性统一经过白名单与强转后再写入生成的 PHP，避免模板属性成为注入面。
 *
 * 所有标签属性都要经过白名单/强转后再写入生成的 PHP，避免模板属性成为代码注入面。
 */
class We extends TagLib
{
    /** @var array<string,array{attr:string,close:int}> */
    protected $tags = [
        'question' => ['attr' => 'name,uid,limit,sort,topic_ids,category_id,page,key', 'close' => 1],
        'article'  => ['attr' => 'name,uid,limit,sort,topic_ids,category_id,page,key', 'close' => 1],
    ];

    /** 与 safe_content_sort() 保持同一白名单。 */
    private const SORTS = ['new', 'hot', 'recommend', 'unresponsive', 'focus', 'default'];

    public static function normalizeSort($value): string
    {
        $value = strtolower(trim((string) $value));
        return in_array($value, self::SORTS, true) ? $value : 'new';
    }

    public static function normalizeIds($value): string
    {
        $ids = is_array($value) ? $value : explode(',', (string) $value);
        $ids = array_values(array_filter(array_map('intval', $ids), static fn($id) => $id > 0));
        return $ids ? implode(',', $ids) : '0';
    }

    public static function normalizeVar($value, string $fallback): string
    {
        $value = ltrim(trim((string) $value), '$');
        return preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $value) === 1 ? $value : $fallback;
    }

    /**
     * 把模板属性转成安全的 PHP 表达式。
     * 仅接受简单变量（$sort）或字面量；数组下标、函数调用等表达式一律回退默认值。
     */
    private static function expr($value, string $type): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return self::defaultExpr($type);
        }

        if ($value[0] === '$') {
            if (preg_match('/^\$[A-Za-z_][A-Za-z0-9_]*$/', $value) !== 1) {
                return self::defaultExpr($type);
            }
            return match ($type) {
                'sort' => '\\app\\common\\taglib\\We::normalizeSort(' . $value . ')',
                'ids'  => '\\app\\common\\taglib\\We::normalizeIds(' . $value . ')',
                'int'  => '(int)(' . $value . ')',
                default => self::defaultExpr($type),
            };
        }

        return match ($type) {
            'sort' => var_export(self::normalizeSort($value), true),
            'ids'  => var_export(self::normalizeIds($value), true),
            'int'  => (string) (int) $value,
            default => self::defaultExpr($type),
        };
    }

    private static function defaultExpr(string $type): string
    {
        return match ($type) {
            'sort' => "'new'",
            'ids'  => "'0'",
            'int'  => '0',
            default => '0',
        };
    }

    public function tagQuestion($tag, $content): string
    {
        return $this->buildList('getQuestionList', $tag, $content);
    }

    public function tagArticle($tag, $content): string
    {
        return $this->buildList('getArticleList', $tag, $content);
    }

    private function buildList(string $method, array $tag, string $content): string
    {
        $name = self::normalizeVar($tag['name'] ?? '', 'v');
        $key = self::normalizeVar($tag['key'] ?? '', 'key');
        $page = self::normalizeVar($tag['page'] ?? '', 'page');
        $uid = self::expr($tag['uid'] ?? '', 'int');
        $sort = self::expr($tag['sort'] ?? '', 'sort');
        $limit = self::expr($tag['limit'] ?? '', 'int');
        $topicIds = self::expr($tag['topic_ids'] ?? '', 'ids');
        $categoryId = self::expr($tag['category_id'] ?? '', 'int');

        $parse = '<?php ';
        $parse .= '$uid=' . $uid . ';';
        $parse .= '$type=\'normal\';';
        $parse .= '$cache=0;';
        $parse .= '$pjax=\'wrapMain\';';
        $parse .= '$sort=' . $sort . ';';
        $parse .= '$limit=' . $limit . ';';
        $parse .= '$per_page=max(1, (int) get_setting(\'contents_per_page\', 20));';
        $parse .= '$topic_ids=' . $topicIds . ';';
        $parse .= '$category_id=' . $categoryId . ';';
        $parse .= '$__DATA__ = \\app\\logic\\common\\TemplateTag::' . $method . '($uid,$sort,$topic_ids,$category_id,$limit,$per_page,$cache,$pjax,$type);';
        $parse .= '$__LIST__ = $__DATA__[\'list\'];';
        $parse .= '$' . $page . ' = $__DATA__[\'page\'] ?? \'\';';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="' . $key . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }
}
