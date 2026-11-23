/* DudaCMS PWA Service Worker —— 服务于同一套 SSR 页面 */
const VERSION = 'dask-pwa-v4';
const SHELL_CACHE = `${VERSION}-shell`;
const RUNTIME_CACHE = `${VERSION}-runtime`;
const API_CACHE = `${VERSION}-api`;
const ASSET_CACHE = `${VERSION}-asset`;

const SHELL = [
    '/',
    '/pwa/manifest.json',
    '/pwa/offline.html',
    '/pwa/icons/icon-192.png',
    '/pwa/icons/icon-512.png',
    '/pwa/icons/apple-touch-icon.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil((async () => {
        const cache = await caches.open(SHELL_CACHE);
        await Promise.all(SHELL.map((url) => cache.add(url).catch(() => {})));
        await self.skipWaiting();
    })());
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const keys = await caches.keys();
        await Promise.all(keys.filter((key) => !key.startsWith(VERSION)).map((key) => caches.delete(key)));
        // 不主动 claim：避免首次注册时让当前页面失去 bfcache，破坏返回位置恢复。
    })());
});

self.addEventListener('message', (event) => {
    if (event.data === 'SKIP_WAITING') self.skipWaiting();
});

function isPrivateApi(url) {
    return /\/api\.php\/v1\/(auth|users\/me|upload)/.test(url.pathname);
}

async function networkFirst(request, cacheName, fallback) {
    const cache = await caches.open(cacheName);
    try {
        const response = await fetch(request);
        if (response && response.ok) cache.put(request, response.clone());
        return response;
    } catch (error) {
        const cached = await cache.match(request);
        if (cached) return cached;
        if (fallback) {
            const shell = await caches.open(SHELL_CACHE);
            return (await shell.match(fallback)) || Response.error();
        }
        throw error;
    }
}

async function staleWhileRevalidate(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);
    const network = fetch(request).then((response) => {
        if (response && response.ok) cache.put(request, response.clone());
        return response;
    }).catch(() => null);
    return cached || network || Response.error();
}

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    // 页面导航：网络优先，离线回退到统一的离线提示页
    if (request.mode === 'navigate') {
        event.respondWith(networkFirst(request, RUNTIME_CACHE, '/pwa/offline.html'));
        return;
    }

    // API：只缓存公开 GET；私有接口不落盘
    if (url.pathname.startsWith('/api.php/v1/')) {
        if (isPrivateApi(url)) return;
        event.respondWith(networkFirst(request, API_CACHE));
        return;
    }

    // SSR 静态资源：缓存优先 + 后台更新；用户上传的 /storage/ 不缓存
    if (url.pathname.startsWith('/static/')) {
        event.respondWith(staleWhileRevalidate(request, ASSET_CACHE));
    }
});
