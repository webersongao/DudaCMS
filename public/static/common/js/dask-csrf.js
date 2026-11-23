(function () {
    'use strict';

    var meta = document.querySelector('meta[name="csrf-token"]');
    if (!meta) return;
    var token = meta.getAttribute('content') || '';
    if (!token) return;

    function isSameOrigin(url) {
        try {
            return new URL(String(url || ''), window.location.href).origin === window.location.origin;
        } catch (e) {
            return false;
        }
    }

    // 原生 XHR（jQuery / Dropzone / 自研 ajax 都走这里）统一注入同源请求头。
    var open = XMLHttpRequest.prototype.open;
    XMLHttpRequest.prototype.open = function (method, url) {
        this.__daskCsrfUrl = url;
        this.__daskCsrfHeaderSet = false;
        return open.apply(this, arguments);
    };

    var setRequestHeader = XMLHttpRequest.prototype.setRequestHeader;
    XMLHttpRequest.prototype.setRequestHeader = function (name, value) {
        if (String(name).toLowerCase() === 'x-csrf-token') {
            this.__daskCsrfHeaderSet = true;
        }
        return setRequestHeader.apply(this, arguments);
    };

    var send = XMLHttpRequest.prototype.send;
    XMLHttpRequest.prototype.send = function (body) {
        if (!this.__daskCsrfHeaderSet && isSameOrigin(this.__daskCsrfUrl)) {
            setRequestHeader.call(this, 'X-CSRF-TOKEN', token);
            this.__daskCsrfHeaderSet = true;
        }
        return send.apply(this, arguments);
    };

    if (!window.fetch) return;

    var originalFetch = window.fetch;
    window.fetch = function (input, init) {
        if (!isSameOrigin(input && input.url ? input.url : input)) {
            return originalFetch.apply(this, arguments);
        }

        var headers = new Headers(init && init.headers ? init.headers : undefined);
        if (typeof Request !== 'undefined' && input instanceof Request) {
            var requestHeaders = new Headers(input.headers);
            requestHeaders.forEach(function (value, key) {
                if (!headers.has(key)) headers.set(key, value);
            });
        }
        if (!headers.has('X-CSRF-TOKEN')) {
            headers.set('X-CSRF-TOKEN', token);
        }

        var options = Object.assign({}, init || {}, { headers: headers });
        if (typeof Request !== 'undefined' && input instanceof Request) {
            return originalFetch.call(this, new Request(input, options), options);
        }
        return originalFetch.call(this, input, options);
    };
})();
