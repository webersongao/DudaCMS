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

namespace app\common\library\helper;

use app\model\PostRelation;
use app\model\Article;
use app\model\Question;

/**
 * 规则核心类
 * Class RuleHelper
 * @package app\common\library\helper
 */
class PopularHelper
{
    // 热度算法为站点内部规则，不暴露为后台配置项，避免低使用场景的配置噪音。
    private const QUALITY_INIT = 1.0;
    private const GRAVITY = 1.5;
    private const AGREE_RATIO = 2;
    private const AGAINST_RATIO = 1;
    private const VIEW_RATIO = 2;
    private const COMMENT_RATIO = 2;

    /**
     * 计算问题热度
     * @param $question_id
     * @return bool
     */
    public static function calcQuestionPopularValue($question_id): bool
    {
        if (!$question_info = Question::getQuestionInfo($question_id)) {
            return false;
        }

        $qualityInitValue = self::QUALITY_INIT;
        $gravity = self::GRAVITY;
        $hotParamValue = $question_info['agree_count'] * self::AGREE_RATIO
            + $question_info['against_count'] * self::AGAINST_RATIO
            + $question_info['view_count'] * self::VIEW_RATIO
            + $question_info['answer_count'] * self::COMMENT_RATIO;
        $publishTime = $question_info['create_time']; //内容发布的时间
        $hotValue = (($hotParamValue + $qualityInitValue) / pow((time() - $publishTime) + 1, $gravity)) * 100000; //热度值

        if (db('question')->where('id', $question_id)->update(['popular_value' => $hotValue, 'popular_value_update' => time()])) {
            PostRelation::updatePostRelation($question_id, 'question', ['popular_value' => $hotValue]);
        }
        return true;
    }

    /**
     * 计算文章热度
     * @param $article_id
     * @return bool
     */
    public static function calcArticlePopularValue($article_id): bool
    {
        if (!$article_info = Article::getArticleInfo($article_id)) {
            return false;
        }

        $qualityInitValue = self::QUALITY_INIT;
        $gravity = self::GRAVITY;
        $hotParamValue = $article_info['agree_count'] * self::AGREE_RATIO
            + $article_info['against_count'] * self::AGAINST_RATIO
            + $article_info['view_count'] * self::VIEW_RATIO
            + $article_info['comment_count'] * self::COMMENT_RATIO;
        $hotValue = (($hotParamValue + $qualityInitValue) / pow((time() - $article_info['create_time']) + 1, $gravity)) * 100000; //热度值

        if (db('article')->where('id', $article_id)->update(['popular_value' => $hotValue, 'popular_value_update' => time()])) {
            PostRelation::updatePostRelation($article_id, 'article', ['popular_value' => $hotValue]);
        }
        return true;
    }
}
