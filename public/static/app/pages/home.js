// 首页按页模块：目前只标记挂载状态，后续承载首页专属交互。
export function mount(root) {
    window.__esmMountCount = (window.__esmMountCount || 0) + 1;
    document.documentElement.setAttribute('data-esm-page', 'home');
    root.setAttribute('data-esm-page-mounted', 'home');
}
