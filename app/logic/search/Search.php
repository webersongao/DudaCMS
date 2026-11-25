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

namespace app\logic\search;

use app\logic\search\driver\ElasticSearch;
use app\logic\search\driver\RegexpSearch;

/**
 * 搜索引擎逻辑层
 * Class Search
 * @package app\common\logic
 */
class Search
{
    //搜索引擎
    protected $handler;

    // 当前是否为 ES 驱动（用于连接失败时降级）
    protected $isElastic = false;

    /**
     * 架构函数
     * @access public
     * @param null $search
     */
    public function __construct($search = null)
    {
        // 未显式指定时读取后台「其他配置」的搜索引擎选择，
        // 避免头部 Ajax 搜索等无参调用点恒走本地库、与搜索主页行为不一致。
        if ($search === null) {
            $search = get_setting('search_handle', 'regexp');
        }
        // 选了 ES 但未配置服务地址时自动降级本地搜索，避免整站搜索报错。
        if ($search === 'ElasticSearch' && !get_setting('search_engine_host')) {
            $search = 'regexp';
        }
        $this->isElastic = $search === 'ElasticSearch';
        $this->handler = $this->isElastic ? new ElasticSearch() : new RegexpSearch();
    }

    public function search($keywords, $type, $uid, $order, $page = 1, $per_page = 10)
    {
        //其他搜索引擎钩子
        $keywords = is_array($keywords) ? $keywords : explode(' ', $keywords);
        $keywords = array_filter($keywords);
        $where = [];
        if ($page <= 1) $page = 1;
        if ($per_page <= 0) $per_page = 5;
        try {
            if ($type == 'all') {
                $searchResult = $this->handler->search($keywords, '', $uid, [], $page, $per_page);
            } elseif (in_array($type, ['question', 'users', 'topic', 'answer', 'article'], true)) {
                $searchResult = $this->handler->search($keywords, $type, $uid, [], $page, $per_page);
            } else {
                //自定义搜索类型
                $searchResult = json_decode('', true);
            }
        } catch (\Throwable $e) {
            // ES 服务不可达时自动降级本地数据库搜索，保证前台搜索不中断。
            if (!$this->isElastic) {
                throw $e;
            }
            $searchResult = (new RegexpSearch())->search($keywords, $type === 'all' ? '' : $type, $uid, [], $page, $per_page);
        }
        return $searchResult;
    }
}
