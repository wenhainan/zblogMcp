/*!
 * zblogMcp 后台原生交互脚本
 * 不依赖 Vue / Element Plus / jQuery，仅使用浏览器原生 API
 */
(function (window, document) {
    'use strict';

    var Zbm = {};
    var ATTR = '[data-zbm-confirm],[data-zbm-copy],[data-zbm-copy-from],[data-zbm-tab],' +
        '[data-zbm-dialog-open],[data-zbm-dialog-close],[data-zbm-toggle-key],[data-zbm-toggle-input],[data-zbm-random-key]';

    /* ---------------- 轻提示 ---------------- */
    var toastWrap = null;

    function getToastWrap() {
        if (!toastWrap) {
            toastWrap = document.createElement('div');
            toastWrap.id = 'zbmToastWrap';
            document.body.appendChild(toastWrap);
        }
        return toastWrap;
    }

    Zbm.toast = function (message, type) {
        type = type || 'info';
        var el = document.createElement('div');
        el.className = 'zbm-toast zbm-toast--' + type;
        el.textContent = message;
        getToastWrap().appendChild(el);
        window.setTimeout(function () {
            el.className += ' is-show';
        }, 10);
        window.setTimeout(function () {
            el.className = el.className.replace(' is-show', '');
            window.setTimeout(function () {
                if (el.parentNode) {
                    el.parentNode.removeChild(el);
                }
            }, 300);
        }, 2200);
    };

    /* ---------------- 弹窗 ---------------- */
    function makeButton(text, className) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = className;
        btn.textContent = text;
        return btn;
    }

    Zbm.closeDialog = function (dialog) {
        if (dialog) {
            dialog.className = dialog.className.replace(' is-open', '');
        }
    };

    Zbm.openDialog = function (selector) {
        var dialog = document.querySelector(selector);
        if (dialog) {
            dialog.className += ' is-open';
        }
        return dialog;
    };

    /**
     * 确认弹窗
     * @param {Object} options title / message / confirmText / cancelText / type
     * @returns {Promise<boolean>}
     */
    Zbm.confirm = function (options) {
        options = options || {};
        return new Promise(function (resolve) {
            var dialog = document.createElement('div');
            dialog.className = 'zbm-dialog';

            var mask = document.createElement('div');
            mask.className = 'zbm-dialog__mask';

            var box = document.createElement('div');
            box.className = 'zbm-dialog__box zbm-dialog__box--confirm';

            var header = document.createElement('div');
            header.className = 'zbm-dialog__header';
            header.textContent = options.title || '提示';
            box.appendChild(header);

            var body = document.createElement('div');
            body.className = 'zbm-dialog__body';
            body.textContent = options.message || '';
            box.appendChild(body);

            var footer = document.createElement('div');
            footer.className = 'zbm-dialog__footer';
            var cancelBtn = makeButton(options.cancelText || '取消', 'zbm-btn zbm-btn--sm');
            var okBtn = makeButton(options.confirmText || '确定', 'zbm-btn zbm-btn--sm zbm-btn--' + (options.type === 'error' ? 'danger' : 'primary'));
            footer.appendChild(cancelBtn);
            footer.appendChild(okBtn);
            box.appendChild(footer);

            dialog.appendChild(mask);
            dialog.appendChild(box);

            function close(result) {
                document.removeEventListener('keydown', onKey, false);
                if (dialog.parentNode) {
                    dialog.parentNode.removeChild(dialog);
                }
                resolve(result);
            }

            function onKey(event) {
                if (event.key === 'Escape' || event.keyCode === 27) {
                    close(false);
                }
            }

            okBtn.addEventListener('click', function () {
                close(true);
            });
            cancelBtn.addEventListener('click', function () {
                close(false);
            });
            mask.addEventListener('click', function () {
                close(false);
            });
            document.addEventListener('keydown', onKey, false);

            document.body.appendChild(dialog);
            window.setTimeout(function () {
                dialog.className += ' is-open';
                okBtn.focus();
            }, 10);
        });
    };

    /* ---------------- 复制 ---------------- */
    Zbm.copy = function (text, successMessage) {
        if (!text) {
            Zbm.toast('内容为空，无法复制', 'warning');
            return;
        }
        var message = successMessage || '已复制到剪贴板';

        function fallbackCopy() {
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            textarea.style.top = '0';
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();
            try {
                document.execCommand('copy');
                Zbm.toast(message, 'success');
            } catch (error) {
                Zbm.toast('复制失败，请手动复制', 'error');
            }
            document.body.removeChild(textarea);
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(function () {
                Zbm.toast(message, 'success');
            }, fallbackCopy);
        } else {
            fallbackCopy();
        }
    };

    /* ---------------- 随机 ApiKey ---------------- */
    Zbm.randomKey = function () {
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var result = 'sk-mcp-';
        var i;
        if (window.crypto && typeof window.crypto.getRandomValues === 'function') {
            var buffer = new Uint32Array(40);
            window.crypto.getRandomValues(buffer);
            for (i = 0; i < 40; i++) {
                result += chars.charAt(buffer[i] % chars.length);
            }
        } else {
            for (i = 0; i < 40; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
        }
        return result;
    };

    /* ---------------- 标签页 ---------------- */
    function activateTab(item) {
        var scope = item;
        while (scope && !scope.hasAttribute('data-zbm-tabs')) {
            scope = scope.parentNode;
        }
        if (!scope) {
            return;
        }
        var name = item.getAttribute('data-zbm-tab');
        var tabs = scope.querySelectorAll('[data-zbm-tab]');
        var i;
        for (i = 0; i < tabs.length; i++) {
            tabs[i].className = tabs[i].className.replace(' is-active', '');
        }
        item.className += ' is-active';

        var panels = scope.querySelectorAll('[data-zbm-panel]');
        for (i = 0; i < panels.length; i++) {
            var panel = panels[i];
            var active = panel.getAttribute('data-zbm-panel') === name;
            panel.className = panel.className.replace(' is-active', '');
            if (active) {
                panel.className += ' is-active';
            }
        }
    }

    /* ---------------- 开关文案 ---------------- */
    function syncSwitch(input) {
        var wrap = input.parentNode;
        while (wrap && wrap.className.indexOf('zbm-switch') < 0) {
            wrap = wrap.parentNode;
        }
        if (!wrap) {
            return;
        }
        var label = wrap.parentNode ? wrap.parentNode.querySelector('.zbm-switch-label') : null;
        if (label) {
            label.textContent = input.checked
                ? (wrap.getAttribute('data-on-text') || '开启')
                : (wrap.getAttribute('data-off-text') || '关闭');
        }
    }

    /* ---------------- 全局事件委托 ---------------- */
    document.addEventListener('click', function (event) {
        var el = event.target;
        if (!el || el.nodeType !== 1 || typeof el.closest !== 'function') {
            return;
        }

        // 关闭弹窗
        var closer = el.closest('[data-zbm-dialog-close]');
        if (closer) {
            var opened = closer;
            while (opened && opened.className.indexOf('zbm-dialog') < 0) {
                opened = opened.parentNode;
            }
            Zbm.closeDialog(opened);
            return;
        }

        var hit = el.closest(ATTR);
        if (!hit) {
            return;
        }

        // 打开弹窗
        if (hit.hasAttribute('data-zbm-dialog-open')) {
            Zbm.openDialog(hit.getAttribute('data-zbm-dialog-open'));
            return;
        }

        // 标签页
        if (hit.hasAttribute('data-zbm-tab')) {
            activateTab(hit);
            return;
        }

        // 复制固定文本
        if (hit.hasAttribute('data-zbm-copy')) {
            Zbm.copy(hit.getAttribute('data-zbm-copy'), hit.getAttribute('data-zbm-copy-msg'));
            return;
        }

        // 复制指定元素的文本
        if (hit.hasAttribute('data-zbm-copy-from')) {
            var source = document.querySelector(hit.getAttribute('data-zbm-copy-from'));
            if (source) {
                Zbm.copy(source.textContent, hit.getAttribute('data-zbm-copy-msg'));
            }
            return;
        }

        // 明文 / 脱敏切换
        if (hit.hasAttribute('data-zbm-toggle-key')) {
            var node = document.querySelector(hit.getAttribute('data-zbm-toggle-key'));
            if (node) {
                var showFull = node.getAttribute('data-state') !== 'full';
                node.textContent = showFull ? node.getAttribute('data-full') : node.getAttribute('data-mask');
                node.setAttribute('data-state', showFull ? 'full' : 'mask');
                hit.textContent = showFull
                    ? (hit.getAttribute('data-label-full') || '隐藏')
                    : (hit.getAttribute('data-label-mask') || '显示');
            }
            return;
        }

        // 明文 / 密文切换
        if (hit.hasAttribute('data-zbm-toggle-input')) {
            var field = document.querySelector(hit.getAttribute('data-zbm-toggle-input'));
            if (field) {
                var toText = field.getAttribute('type') === 'password';
                field.setAttribute('type', toText ? 'text' : 'password');
                hit.textContent = toText
                    ? (hit.getAttribute('data-label-full') || '隐藏')
                    : (hit.getAttribute('data-label-mask') || '显示');
            }
            return;
        }

        // 随机生成 ApiKey
        if (hit.hasAttribute('data-zbm-random-key')) {
            var input = document.querySelector(hit.getAttribute('data-zbm-random-key'));
            if (input) {
                input.value = Zbm.randomKey();
                input.setAttribute('type', 'text');
                Zbm.toast('已生成随机 Key', 'success');
            }
            return;
        }

        // 带确认的跳转
        if (hit.hasAttribute('data-zbm-confirm')) {
            event.preventDefault();
            var href = hit.getAttribute('href') || hit.getAttribute('data-zbm-href');
            Zbm.confirm({
                title: hit.getAttribute('data-zbm-confirm-title') || '提示',
                message: hit.getAttribute('data-zbm-confirm'),
                confirmText: hit.getAttribute('data-zbm-confirm-ok') || '确定',
                cancelText: '取消',
                type: hit.getAttribute('data-zbm-confirm-type') || 'warning'
            }).then(function (ok) {
                if (ok && href) {
                    window.location.href = href;
                }
            });
        }
    }, false);

    document.addEventListener('change', function (event) {
        var input = event.target;
        if (!input || input.type !== 'checkbox') {
            return;
        }
        if (input.parentNode && input.parentNode.className.indexOf('zbm-switch') >= 0) {
            syncSwitch(input);
        }
    }, false);

    window.Zbm = Zbm;
})(window, document);
