// 轻量 API 客户端：无构建、无全局污染、统一响应解包。
const BASE = '/api.php/v1';

async function request(path, options = {}) {
    const config = {
        method: options.method || 'GET',
        headers: { Accept: 'application/json', ...(options.headers || {}) },
        credentials: 'same-origin',
    };
    if (options.body !== undefined) {
        if (options.body instanceof FormData) {
            config.body = options.body;
        } else {
            config.headers['Content-Type'] = 'application/json';
            config.body = JSON.stringify(options.body);
        }
    }
    const url = path.startsWith('http') || path.startsWith('/') ? path : `${BASE}/${path.replace(/^\//, '')}`;
    const response = await fetch(url, config);
    let payload;
    try {
        payload = await response.json();
    } catch (error) {
        throw new Error(`接口返回格式错误：${response.status}`);
    }
    // 站点有两套响应约定：
    //   API v1 (Response::ok)  -> code=0，带 request_id/timestamp/data
    //   Web AJAX (Controller::success) -> code=1，带 msg/url
    // 两者都视为成功；其余 code 视为失败。
    const apiOk = payload.code === 0 && (payload.request_id !== undefined || payload.timestamp !== undefined || payload.data !== undefined);
    const webOk = payload.code === 1 || payload.code === undefined;
    if (!response.ok || (!apiOk && !webOk)) {
        const error = new Error(payload.msg || payload.message || `请求失败：${response.status}`);
        error.code = payload.code ?? response.status;
        error.requestId = payload.request_id || '';
        throw error;
    }
    // Web AJAX 的 msg/url 在 payload 顶层，API v1 的业务数据在 data 中。
    return Object.prototype.hasOwnProperty.call(payload, 'data') && !('msg' in payload) ? payload.data : payload;
}

export const http = {
    get: (path, params = {}) => {
        const query = new URLSearchParams(params).toString();
        return request(query ? `${path}?${query}` : path);
    },
    post: (path, body = {}) => request(path, { method: 'POST', body }),
    request,
};
