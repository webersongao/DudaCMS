window.MathJax = {
    tex: {
        inlineMath: [['$', '$'], ['\\(', '\\)']]
    },
    svg: {
        fontCache: 'global'
    },
    options: {
        enableMenu: false
    }
};

// MathJax 本地化 + 首屏后加载：避免外部 CDN 拖慢 load 事件。
(function () {
    var current = document.currentScript && document.currentScript.src ? document.currentScript.src : '';
    var src = current.replace(/^(.*\/static\/)common\/js\/tex-mml-chtml\.js(?:\?.*)?$/, '$1vendor/mathjax/tex-mml-chtml.js');
    if (!src || src === current) {
        src = (window.DASK_MATHJAX_SRC || '/static/vendor/mathjax/tex-mml-chtml.js');
    }

    function inject() {
        var script = document.createElement('script');
        script.src = src;
        script.async = true;
        document.head.appendChild(script);
    }

    if (document.readyState === 'complete') {
        window.setTimeout(inject, 0);
    } else {
        window.addEventListener('load', function () {
            window.setTimeout(inject, 0);
        }, { once: true });
    }
})();
