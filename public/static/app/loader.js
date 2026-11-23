// 轻量按页模块加载器：模板通过 data-page-module 声明需要的模块，
// loader 只 import 声明过的模块；页面切换后重新扫描并挂载新节点。
import { enhanceLists } from './components.js';

const registry = {
    home: () => import('./pages/home.js'),
    questions: () => import('./pages/questions.js'),
};

// 记录节点当前已挂载的模块 id：pjax 替换后同一个节点可能声明另一个模块。
const mounted = new WeakMap();

async function mountRoot(root) {
    if (!root || !root.querySelectorAll) return 0;
    const nodes = [];
    if (root.matches && root.matches('[data-page-module]')) nodes.push(root);
    root.querySelectorAll('[data-page-module]').forEach((node) => nodes.push(node));
    let count = 0;
    for (const node of nodes) {
        const id = node.dataset.pageModule;
        if (mounted.get(node) === id) continue;
        const factory = registry[id];
        if (!factory) continue;
        mounted.set(node, id);
        try {
            const mod = await factory();
            if (typeof mod.mount === 'function') mod.mount(node);
            node.setAttribute('data-esm-mounted', '1');
            count += 1;
        } catch (error) {
            mounted.delete(node);
            node.setAttribute('data-esm-error', String(error && error.message || error));
        }
    }
    return count;
}

export function mount(root = document) {
    // 页面替换/初始挂载后同步增强该根节点下的 .dask-list-item（补 role，不改样式）。
    enhanceLists(root);
    return mountRoot(root);
}

export function bindNavigation() {
    // §14：Turbo 导航完成后由 dask-turbo.js 统一发出 dask:page:rendered。
    document.addEventListener('dask:page:rendered', () => mount(document));
}
