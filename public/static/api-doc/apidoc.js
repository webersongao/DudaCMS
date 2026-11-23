(function () {
    'use strict';

    function slugify(text) {
        return text.toLowerCase()
            .replace(/[\s/]+/g, '-')
            .replace(/[^\w\u4e00-\u9fa5-]/g, '')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '') || 'section';
    }

    function buildToc() {
        var content = document.getElementById('apidoc-content');
        var toc = document.getElementById('apidoc-toc');
        if (!content || !toc) return;

        var headings = content.querySelectorAll('h2, h3');
        var used = {};
        var html = '';
        headings.forEach(function (heading, index) {
            var text = heading.textContent.trim();
            if (!text) return;
            var base = slugify(text);
            var id = base;
            var n = 2;
            while (used[id]) { id = base + '-' + n++; }
            used[id] = true;
            heading.id = id;
            var level = heading.tagName === 'H3' ? 'toc-h3' : 'toc-h2';
            html += '<li class="' + level + '"><a href="#' + id + '" data-target="' + id + '">' + text.replace(/</g, '&lt;') + '</a></li>';
        });
        toc.innerHTML = html;

        var links = toc.querySelectorAll('a');
        links.forEach(function (link) {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                var target = document.getElementById(link.getAttribute('data-target'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    history.replaceState(null, '', '#' + target.id);
                }
            });
        });

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    links.forEach(function (link) {
                        link.classList.toggle('active', link.getAttribute('data-target') === entry.target.id);
                    });
                });
            }, { rootMargin: '-10% 0px -75% 0px', threshold: 0 });
            headings.forEach(function (heading) { observer.observe(heading); });
        }
    }

    function buildFilters() {
        var input = document.getElementById('apidoc-filter');
        var toc = document.getElementById('apidoc-toc');
        if (!input || !toc) return;
        input.addEventListener('input', function () {
            var value = input.value.trim().toLowerCase();
            toc.querySelectorAll('li').forEach(function (li) {
                li.classList.toggle('toc-hidden', value !== '' && li.textContent.toLowerCase().indexOf(value) === -1);
            });
        });
    }

    function buildCopyButtons() {
        var content = document.getElementById('apidoc-content');
        if (!content) return;
        content.querySelectorAll('pre').forEach(function (pre) {
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'apidoc-copy';
            button.textContent = '复制';
            button.addEventListener('click', function () {
                var code = pre.querySelector('code');
                var text = code ? code.innerText : pre.innerText;
                var done = function () {
                    button.textContent = '已复制';
                    setTimeout(function () { button.textContent = '复制'; }, 1600);
                };
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(done).catch(function () {});
                } else {
                    var range = document.createRange();
                    range.selectNodeContents(code || pre);
                    var sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(range);
                    try { document.execCommand('copy'); done(); } catch (e) {}
                    sel.removeAllRanges();
                }
            });
            pre.appendChild(button);
        });
    }

    function mobileToggle() {
        var toggle = document.getElementById('apidoc-menu-toggle');
        var sidebar = document.getElementById('apidoc-sidebar');
        if (!toggle || !sidebar) return;
        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('expanded');
            toggle.textContent = sidebar.classList.contains('expanded') ? '隐藏目录' : '显示目录';
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        buildToc();
        buildFilters();
        buildCopyButtons();
        mobileToggle();
    });
})();
