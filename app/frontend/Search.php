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
namespace app\frontend;

use app\common\controller\Frontend;
use app\common\library\helper\IpHelper;
use app\common\library\helper\StringHelper;
use app\common\paginator\Dask;
use app\model\Common;

/**
 * 公用搜索模块
 * Class Search
 * @package app\ask\controller
 */
class Search extends Frontend
{
    //搜索首页
    public function index()
    {
        //搜索引擎处理方法

        $sortWhitelist = ['all', 'new', 'hot', 'recommend', 'unresponsive', 'focus', 'default'];
        $rawSort = strtolower(trim((string) $this->request->param('sort', 'all')));
        $order = in_array($rawSort, $sortWhitelist, true) ? $rawSort : 'all';
        $rawType = trim((string) $this->request->param('type', 'all'));
        // 搜索类型可能来自 dask_search_engine 自定义配置，只限制为安全标识符，不再剥离正文关键词。
        $type = $rawType !== '' && preg_match('/^[A-Za-z0-9_]+$/', $rawType) ? $rawType : 'all';
        $limit = $this->request->param('limit', get_setting('contents_per_page'), 'intval');
        $page = $this->request->param('page', 1, 'intval');
        $keywords = StringHelper::sanitizeSearchKeyword($this->request->param('q', ''));
        if (!$keywords) {
            // 空词不再 redirect() 到空 Location（浏览器会停在空白页），直接渲染带热门搜索的引导页
            $this->assign([
                'type' => $type,
                'sort' => $order,
                'keywords' => '',
                'list' => [],
                'total' => 0,
                'page' => '',
                'tab_list' => db('search_engine')->where(['status' => 1, 'search_engine' => 'regexp'])->column('name,title'),
                'search_list' => Common::getHotSearchList(1, 10),
                'empty_keyword' => true
            ]);
            return $this->fetch();
        }

        $cache_key = 'search_result_q' . md5(trim($keywords) . '_search_type' . $type . '_page' . $page . '_sort' . $order . '_uid' . $this->user_id);
        $data = cache($cache_key);
        if (!$data) {
            $handle = new \app\logic\search\Search(get_setting('search_handle'));
            $data = $handle->search($keywords, $type, $this->user_id, $order, intval($page), intval($limit));
            cache($cache_key, $data, 60);
            //只记录有效搜索词；同一 IP 短时间内重复搜索不重复计数
            if (!Common::isDirtySearchKeyword($keywords)) {
                $ip = IpHelper::getRealIp();
                $recent = db('search_log')
                    ->where('ip', $ip)
                    ->where('keyword', $keywords)
                    ->where('create_time', '>=', time() - 60)
                    ->count();
                if (!$recent) {
                    db('search_log')->insert([
                        'uid' => $this->user_id,
                        'ip' => $ip,
                        'keyword' => $keywords,
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                        'from' => $_SERVER['HTTP_REFERER'] ?? $this->request->server('HTTP_REFERER') ?? '',
                        'create_time' => time()
                    ]);
                }
            }
        }
        //搜索类型标签
        $tab_name = db('search_engine')->where(['status' => 1, 'search_engine' => 'regexp'])->column('name,title');
        if (!$data) {
            $this->assign([
                'type' => $type,
                'sort' => $order,
                'list' => [],
                'total' => 0,
                'keywords' => $keywords,
                'page' => '',
                'tab_list' => $tab_name,
                'search_list' => Common::getHotSearchList(1, 10)
            ]);
            return $this->fetch();
        }
        $result = Dask::make($data['list'], intval($limit), intval($page), $data['total'], false, [
            'path' => $this->request->baseUrl(),
            'list_rows' => intval($limit),
            'query' => $this->request->param(),
            'page' => intval($page),
            'pjax' => 'SearchResultMain',
        ]);
        $this->assign([
            'type' => $type,
            'sort' => $order,
            'list' => $data['list'],
            'total' => $data['total'],
            'keywords' => $keywords,
            'page' => $result->render(),
            'tab_list' => $tab_name,
            'search_list' => Common::getHotSearchList(1, 10)
        ]);
        return $this->fetch();
    }
}
