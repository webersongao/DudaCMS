import './elements.js';
import { http } from './http.js';
import { dialog } from './dialog.js';
import { init } from './components.js';
import { mount, bindNavigation } from './loader.js';

window.DaskApp = Object.freeze({ http, dialog, init, mount });

function boot() {
    init();
    bindNavigation();
    mount(document);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
} else {
    boot();
}
