// 原生 dialog 组件：复用 dask-components.css 里已有的 .dask-dialog / .dask-toast 样式，
// 不引入 layer，也不新增一套样式类。
function ensureDialog() {
    let dialog = document.getElementById('dask-app-dialog');
    if (!dialog) {
        dialog = document.createElement('dialog');
        dialog.id = 'dask-app-dialog';
        dialog.className = 'dask-dialog';
        dialog.setAttribute('role', 'dialog');
        dialog.setAttribute('aria-modal', 'true');
        dialog.innerHTML = `
            <div class="dask-dialog__head">
                <span class="dask-dialog__title"></span>
                <button type="button" class="dask-dialog__close" aria-label="关闭">×</button>
            </div>
            <div class="dask-dialog__body"></div>
            <div class="dask-dialog__foot">
                <button type="button" class="dask-btn-secondary" data-dask-dialog-value="cancel">取消</button>
                <button type="button" class="dask-btn-primary" data-dask-dialog-value="confirm">确定</button>
            </div>`;
        document.body.appendChild(dialog);
    }
    return dialog;
}

function ensureToastStack() {
    let stack = document.querySelector('.dask-toast-stack[data-dask-app-toast-stack]');
    if (!stack) {
        stack = document.createElement('div');
        stack.className = 'dask-toast-stack';
        stack.setAttribute('aria-live', 'polite');
        stack.setAttribute('data-dask-app-toast-stack', '');
        document.body.appendChild(stack);
    }
    return stack;
}

export const dialog = {
    confirm(message, title = '操作确认') {
        const element = ensureDialog();
        element.querySelector('.dask-dialog__title').textContent = title;
        element.querySelector('.dask-dialog__body').textContent = message;

        return new Promise((resolve) => {
            let done = false;
            const finish = (value) => {
                if (done) return;
                done = true;
                element.removeEventListener('close', onClose);
                element.removeEventListener('click', onClick);
                if (element.open) element.close();
                resolve(value);
            };
            const onClose = () => finish(element.returnValue === 'confirm');
            const onClick = (event) => {
                const btn = event.target.closest('[data-dask-dialog-value]');
                if (!btn) return;
                element.returnValue = btn.dataset.daskDialogValue;
                finish(element.returnValue === 'confirm');
            };
            element.addEventListener('close', onClose);
            element.addEventListener('click', onClick);
            if (typeof element.showModal === 'function') {
                element.showModal();
            } else {
                element.setAttribute('open', '');
            }
        });
    },

    toast(message, type = 'info', timeout = 2400) {
        const stack = ensureToastStack();
        // E3：使用 <dask-toast> 自定义元素承载自动消失逻辑；保留原类名与标记，
        // 使既有 .dask-toast 样式与 [data-dask-app-toast] 断言继续有效。
        const item = document.createElement('dask-toast');
        item.className = 'dask-toast dask-toast--' + type;
        item.setAttribute('data-dask-app-toast', '');
        item.setAttribute('data-timeout', String(timeout));
        item.textContent = String(message || '');
        stack.appendChild(item);
        // 无 JS 自定义元素支持时（极老浏览器）退回定时器，保证 toast 仍会消失。
        if (typeof customElements === 'undefined' || !customElements.get('dask-toast')) {
            setTimeout(() => {
                if (item.parentNode) item.parentNode.removeChild(item);
            }, timeout);
        }
        return item;
    },
};
