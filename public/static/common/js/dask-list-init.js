/* 根据模板上的 data-dask-list 标记初始化统一滚动分页。
   标记为渐进增强：没有标记或组件未加载时，原有服务端分页继续可用。 */
(function () {
  'use strict';

  var Dask = window.DaskComponents;
  if (!Dask) return;

  function esc(value) { return Dask.escapeHtml(value); }

  function pick(item, names) {
    for (var i = 0; i < names.length; i += 1) {
      if (item && Object.prototype.hasOwnProperty.call(item, names[i]) && item[names[i]] != null) {
        return item[names[i]];
      }
    }
    return '';
  }

  function itemClass(href, title, excerpt, meta) {
    return '<dask-list-item class="dask-list-item" data-dask-list-item>' +
      '<a class="dask-list-item__title" href="' + esc(href) + '">' + esc(title) + '</a>' +
      (excerpt ? '<div class="dask-two-line">' + esc(excerpt) + '</div>' : '') +
      (meta ? '<div class="dask-list-item__meta">' + esc(meta) + '</div>' : '') +
      '</dask-list-item>';
  }

  function detailUrl(type, id) {
    var map = { question: 'question', article: 'article', topic: 'topic', answer: 'question' };
    var path = map[type] || 'question';
    return '/' + path + '/detail.html?id=' + encodeURIComponent(id);
  }

  var renderers = {
    feed: function (item) {
      var type = item.type || 'question';
      var author = pick(item.author || {}, ['nick_name', 'user_name', 'name']);
      var meta = [author, pick(item, ['create_time_text'])].filter(Boolean).join(' · ');
      return itemClass(detailUrl(type, item.id), item.title, item.excerpt || item.detail, meta);
    },
    question: function (item) {
      var meta = [
        pick(item, ['answer_count']) + ' ' + '回答',
        pick(item, ['view_count']) + ' ' + '浏览',
        pick(item, ['create_time_text'])
      ].join(' · ');
      return itemClass(detailUrl('question', item.id), item.title, item.excerpt || item.detail, meta);
    },
    article: function (item) {
      var meta = [
        pick(item, ['agree_count']) + ' ' + '赞同',
        pick(item, ['view_count']) + ' ' + '浏览',
        pick(item, ['create_time_text'])
      ].join(' · ');
      return itemClass(detailUrl('article', item.id), item.title, item.excerpt, meta);
    },
    topic: function (item) {
      var meta = [
        '讨论 ' + pick(item, ['discuss']),
        '关注 ' + pick(item, ['focus'])
      ].join(' · ');
      return itemClass(detailUrl('topic', item.id), '#' + pick(item, ['title']), item.description, meta);
    },
    search: function (item) {
      var type = item.search_type || item.type || 'question';
      var href = type === 'users' ? (item.url || '/people/' + item.uid)
        : type === 'topic' ? detailUrl('topic', item.id)
          : type === 'article' ? detailUrl('article', item.id)
            : detailUrl('question', item.id);
      return itemClass(href, item.title || item.name, item.excerpt || item.signature || item.description, type);
    },
    notify: function (item) {
      var id = esc(item.id);
      return '<dask-list-item class="dask-list-item" data-dask-list-item>' +
        '<div class="dask-list-item__title">' + esc(item.subject) + '</div>' +
        '<div class="dask-list-item__meta">' + esc(item.content) + '</div>' +
        '<a href="javascript:;" class="me-3" data-action="user-read-notify" data-id="' + id + '">标记已读</a>' +
        '<a href="javascript:;" data-action="user-delete-notify" data-id="' + id + '">删除</a>' +
        '</dask-list-item>';
    },
    inbox: function (item) {
      var user = item.user || {};
      var name = esc(pick(user, ['nick_name', 'name']));
      return '<dask-list-item class="dask-list-item" data-dask-list-item>' +
        '<div class="dask-list-item__title">' + name + '</div>' +
        '<div class="dask-list-item__meta">' + esc(item.last_message || '') + '</div>' +
        '<a href="javascript:;" data-action="user-inbox" data-name="' + name + '">查看对话</a>' +
        '</dask-list-item>';
    },
    answer: function (item, root) {
      var author = pick(item.author || {}, ['nick_name', 'user_name', 'name']);
      var questionId = pick(item, ['question_id']) || (root && root.getAttribute('data-dask-question-id'));
      return itemClass(
        detailUrl('question', questionId) + '&answer=' + encodeURIComponent(pick(item, ['id'])),
        '回答 · ' + author,
        item.content || item.excerpt,
        pick(item, ['create_time_text'])
      );
    }
  };

  function buildUrl(root) {
    var api = root.getAttribute('data-dask-api');
    if (!api) return '';
    var url = new URL(api, location.href);
    var keys = ['tab', 'sort', 'category_id', 'type', 'q', 'uid', 'keyword'];
    keys.forEach(function (key) {
      var value = root.getAttribute('data-dask-' + key);
      if (value !== null && value !== '') url.searchParams.set(key, value);
    });
    return url.toString();
  }

  function createList(root) {
    var kind = root.getAttribute('data-dask-kind') || 'feed';
    var dataType = root.getAttribute('data-dask-data-type') || 'json';
    var options = {
      root: root,
      listSelector: '[data-dask-list-items]',
      url: buildUrl(root),
      page: parseInt(root.getAttribute('data-dask-next-page') || '2', 10),
      pageSize: parseInt(root.getAttribute('data-dask-page-size') || '10', 10),
      listKey: root.getAttribute('data-dask-list-key') || location.pathname,
      dataType: dataType,
      htmlSelector: root.getAttribute('data-dask-html-selector') || '[data-dask-list-items]',
      pageParam: root.getAttribute('data-dask-page-param') || 'page',
      sizeParam: root.getAttribute('data-dask-size-param') || 'page_size',
      renderItem: function (item) {
        return (renderers[kind] || renderers.feed)(item, root);
      }
    };
    var list = new Dask.InfiniteList(options);
    root._daskList = list;

    // 返回时若 bfcache 未命中，页面只有首屏数据，目标位置会被浏览器夹到页底。
    // 因此每追加一页都重新对齐，直到到达目标或数据用尽；绝不覆盖 history.state。
    var restoreTarget = null;
    try {
      var scrollState = window.history && window.history.state && window.history.state.daskScroll;
      if (scrollState && scrollState.listKey === options.listKey && typeof scrollState.scrollY === 'number') {
        restoreTarget = scrollState.scrollY;
      }
    } catch (e) {}
    var restoreAttempts = 0;

    function tryRestore() {
      if (restoreTarget === null || !list.hasMore || restoreAttempts >= 20) return;
      var current = Math.round(window.scrollY || document.documentElement.scrollTop || 0);
      if (Math.abs(current - restoreTarget) <= 8) {
        restoreTarget = null;
        return;
      }
      restoreAttempts += 1;
      list.restoreScroll();
    }
    root.addEventListener('dask:list:rendered', tryRestore);
    window.addEventListener('popstate', function () {
      window.setTimeout(function () {
        restoreAttempts = 0;
        list.restoreScroll();
      }, 30);
    });
    list.restoreScroll();
    return list;
  }

  function init() {
    var roots = document.querySelectorAll('[data-dask-list]');
    Array.prototype.forEach.call(roots, function (root) {
      if (!root._daskList && root.getAttribute('data-dask-api')) createList(root);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
  // §14：Turbo 替换 body 后重新绑定新出现的声明式列表。
  document.addEventListener('dask:page:rendered', init);
})();
