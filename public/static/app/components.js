import { dialog } from './dialog.js';
import { enhanceListItem } from './elements.js';

// 组件层只处理 DOM 行为，不含业务 SQL / 模板逻辑。
export function initConfirmActions() {
    document.addEventListener('click', async (event) => {
        const trigger = event.target.closest('[data-dask-confirm]');
        if (!trigger) return;
        event.preventDefault();
        const ok = await dialog.confirm(trigger.dataset.daskConfirm || '确认执行此操作？');
        if (ok && trigger.dataset.daskAction) {
            window.DaskApp?.http.get(trigger.dataset.daskAction)
                .then((result) => {
                    dialog.toast((result && result.msg) || '操作成功', 'success');
                    const url = result && result.url;
                    setTimeout(() => {
                        if (url) window.location.href = url;
                        else window.location.reload();
                    }, 400);
                })
                .catch((error) => dialog.toast(error.message, 'error'));
        }
    });
}

// 把 SSR 已输出的 .dask-list-item 节点纳入 Web Components 增强层。
// SSR 结构/样式完全不变，只补 role=listitem 无障碍语义；页面切换后由 loader 的
// mount() 触发同一个增强入口，保证新插入节点同样被增强。
const ENHANCE_SELECTOR = 'div.dask-list-item, .dask-list-item';
export function enhanceLists(root = document) {
    if (!root || !root.querySelectorAll) return 0;
    let count = 0;
    const nodes = [];
    if (root.matches && root.matches(ENHANCE_SELECTOR)) nodes.push(root);
    root.querySelectorAll(ENHANCE_SELECTOR).forEach((node) => nodes.push(node));
    nodes.forEach((node) => {
        const before = node.dataset.daskEnhanced;
        enhanceListItem(node);
        if (node.dataset.daskEnhanced === '1' && before !== '1') count += 1;
    });
    return count;
}

export function init() {
    initConfirmActions();
    enhanceLists(document);
}
