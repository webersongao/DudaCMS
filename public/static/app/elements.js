// E3 · Web Components 渐进层。
// 原则（方案 §13.3）：SSR 优先、light DOM、只增强交互、不引入构建链。
// - 元素自身不渲染正文：SSR / dask-list-init 已输出完整可见 HTML，元素只在连接后补交互。
// - 不使用 Shadow DOM：直接继承 tokens.css 与 html[data-theme] 深浅色变量。
// - 重复注册安全：pjax 片段或多次 import 时 customElements.define 只生效一次。

function define(tag, ctor) {
    if (typeof customElements === 'undefined') return;
    if (!customElements.get(tag)) customElements.define(tag, ctor);
}

// 信息流条目的渐进增强逻辑，抽成函数以便同时作用于：
// 1) <dask-list-item> 自定义元素（connectedCallback）；
// 2) SSR 已输出的 <div class="dask-list-item">（ESM 组件层批量增强，避免二次改模板）。
// 只补无障碍语义，不改任何结构/样式，故与 12B/C/D 的像素回归完全兼容。
export function enhanceListItem(el) {
    if (!el || el.dataset.daskEnhanced === '1') return;
    el.dataset.daskEnhanced = '1';
    if (!el.hasAttribute('role')) el.setAttribute('role', 'listitem');
}

class DaskListItem extends HTMLElement {
    connectedCallback() { enhanceListItem(this); }
}

// <dask-toast>：轻通知条目。自动消失逻辑内聚到元素自身，dialog.js 只负责创建与挂载。
class DaskToast extends HTMLElement {
    connectedCallback() {
        if (this.dataset.daskTimerStarted === '1') return;
        this.dataset.daskTimerStarted = '1';
        if (!this.hasAttribute('role')) this.setAttribute('role', 'status');
        const timeout = Number(this.getAttribute('data-timeout') || 2400);
        if (timeout > 0) {
            this._daskTimer = window.setTimeout(() => this.dismiss(), timeout);
        }
    }

    disconnectedCallback() {
        if (this._daskTimer) {
            window.clearTimeout(this._daskTimer);
            this._daskTimer = null;
        }
    }

    dismiss() {
        if (this._daskTimer) {
            window.clearTimeout(this._daskTimer);
            this._daskTimer = null;
        }
        if (this.parentNode) this.parentNode.removeChild(this);
    }
}

define('dask-list-item', DaskListItem);
define('dask-toast', DaskToast);

export { DaskListItem, DaskToast };
