/**
 * Istana Laundry ERP - Anti-Tamper Closed Shadow DOM Custom Alert & Dialog Engine
 * Encapsulated in Closed Shadow DOM (mode: 'closed') to prevent DOM manipulation/tampering via F12 DevTools or external scripts.
 * Supports Light & Dark Theme detection via HTML class and Rich Transaction Breakdown Table.
 */
(function () {
    'use strict';

    class SecureDialogContainer extends HTMLElement {
        constructor() {
            super();
            this._shadow = this.attachShadow({ mode: 'closed' });
            this._initStylesAndDOM();
            this._activeResolve = null;
            this._activeReject = null;
        }

        _initStylesAndDOM() {
            this._shadow.innerHTML = `
                <style>
                    :host {
                        font-family: 'Plus Jakarta Sans', 'Outfit', system-ui, -apple-system, sans-serif;
                    }
                    * {
                        box-sizing: border-box;
                        margin: 0;
                        padding: 0;
                    }
                    .dialog-backdrop {
                        position: fixed;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background: rgba(15, 23, 42, 0.75);
                        backdrop-filter: blur(8px);
                        -webkit-backdrop-filter: blur(8px);
                        z-index: 999999;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        padding: 1rem;
                        opacity: 0;
                        visibility: hidden;
                        transition: opacity 0.25s ease-in-out, visibility 0.25s ease-in-out;
                    }
                    .dialog-backdrop.active {
                        opacity: 1;
                        visibility: visible;
                    }
                    .dialog-card {
                        background: #ffffff;
                        color: #0f172a;
                        width: 100%;
                        max-width: 480px;
                        border-radius: 24px;
                        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35), 0 0 0 1px rgba(226, 232, 240, 0.8);
                        overflow: hidden;
                        transform: scale(0.92) translateY(10px);
                        transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), background 0.2s, color 0.2s;
                    }
                    .dialog-card.dark {
                        background: #0f172a;
                        color: #f8fafc;
                        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(51, 65, 85, 0.8);
                    }
                    .dialog-backdrop.active .dialog-card {
                        transform: scale(1) translateY(0);
                    }
                    .dialog-header-ribbon {
                        height: 6px;
                        width: 100%;
                        background: linear-gradient(90deg, #ff6600 0%, #ea580c 50%, #f97316 100%);
                    }
                    .dialog-header-ribbon.danger {
                        background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%);
                    }
                    .dialog-header-ribbon.warning {
                        background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
                    }
                    .dialog-header-ribbon.success {
                        background: linear-gradient(90deg, #10b981 0%, #059669 100%);
                    }
                    .dialog-body {
                        padding: 24px;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        text-align: center;
                    }
                    .dialog-icon-wrapper {
                        width: 56px;
                        height: 56px;
                        border-radius: 18px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin-bottom: 16px;
                        background: #fff7ed;
                        color: #ff6600;
                    }
                    .dialog-icon-wrapper.danger {
                        background: #fef2f2;
                        color: #ef4444;
                    }
                    .dialog-icon-wrapper.warning {
                        background: #fffbeb;
                        color: #f59e0b;
                    }
                    .dialog-icon-wrapper.success {
                        background: #ecfdf5;
                        color: #10b981;
                    }
                    .dialog-card.dark .dialog-icon-wrapper { background: rgba(255, 102, 0, 0.15); }
                    .dialog-card.dark .dialog-icon-wrapper.danger { background: rgba(239, 68, 68, 0.15); }
                    .dialog-card.dark .dialog-icon-wrapper.warning { background: rgba(245, 158, 11, 0.15); }
                    .dialog-card.dark .dialog-icon-wrapper.success { background: rgba(16, 185, 129, 0.15); }

                    .dialog-icon-wrapper svg {
                        width: 32px;
                        height: 32px;
                        fill: currentColor;
                    }
                    .dialog-title {
                        font-size: 18px;
                        font-weight: 800;
                        color: #0f172a;
                        margin-bottom: 6px;
                        letter-spacing: -0.3px;
                    }
                    .dialog-card.dark .dialog-title { color: #f8fafc; }

                    .dialog-message {
                        font-size: 13.5px;
                        color: #475569;
                        line-height: 1.5;
                        margin-bottom: 16px;
                        word-break: break-word;
                    }
                    .dialog-card.dark .dialog-message { color: #94a3b8; }

                    .dialog-details-wrapper {
                        width: 100%;
                        margin-bottom: 16px;
                        text-align: left;
                        display: none;
                        max-height: 220px;
                        overflow-y: auto;
                        border-radius: 14px;
                        border: 1px solid #e2e8f0;
                        background: #f8fafc;
                        padding: 12px;
                    }
                    .dialog-card.dark .dialog-details-wrapper {
                        background: #1e293b;
                        border-color: #334155;
                    }
                    .dialog-details-wrapper.active {
                        display: block;
                    }
                    .dialog-table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 12px;
                    }
                    .dialog-table th {
                        text-align: left;
                        font-size: 10px;
                        font-weight: 800;
                        text-transform: uppercase;
                        color: #64748b;
                        padding-bottom: 6px;
                        border-bottom: 1px solid #cbd5e1;
                    }
                    .dialog-card.dark .dialog-table th {
                        color: #94a3b8;
                        border-color: #334155;
                    }
                    .dialog-table td {
                        padding: 6px 0;
                        border-bottom: 1px dashed #e2e8f0;
                        color: #334155;
                    }
                    .dialog-card.dark .dialog-table td {
                        border-color: #334155;
                        color: #cbd5e1;
                    }
                    .dialog-table tr:last-child td {
                        border-bottom: none;
                    }
                    .dialog-table .text-right { text-align: right; }
                    .dialog-table .font-bold { font-weight: 700; color: #0f172a; }
                    .dialog-card.dark .dialog-table .font-bold { color: #f8fafc; }

                    .dialog-input-wrapper {
                        width: 100%;
                        margin-bottom: 20px;
                        display: none;
                    }
                    .dialog-input-wrapper.active {
                        display: block;
                    }
                    .dialog-input {
                        width: 100%;
                        padding: 12px 16px;
                        border-radius: 12px;
                        border: 1px solid #cbd5e1;
                        background: #f8fafc;
                        font-size: 14px;
                        color: #0f172a;
                        outline: none;
                        transition: border-color 0.2s;
                    }
                    .dialog-card.dark .dialog-input {
                        background: #1e293b;
                        border-color: #334155;
                        color: #f8fafc;
                    }
                    .dialog-input:focus {
                        border-color: #ff6600;
                        box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.15);
                    }

                    .dialog-actions {
                        display: flex;
                        gap: 12px;
                        width: 100%;
                    }
                    .btn {
                        flex: 1;
                        padding: 12px 20px;
                        border-radius: 14px;
                        font-size: 13.5px;
                        font-weight: 800;
                        border: none;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        gap: 6px;
                    }
                    .btn-cancel {
                        background: #f1f5f9;
                        color: #475569;
                    }
                    .btn-cancel:hover {
                        background: #e2e8f0;
                        color: #1e293b;
                    }
                    .dialog-card.dark .btn-cancel {
                        background: #1e293b;
                        color: #cbd5e1;
                    }
                    .dialog-card.dark .btn-cancel:hover {
                        background: #334155;
                        color: #f8fafc;
                    }
                    .btn-confirm {
                        background: #ff6600;
                        color: #ffffff;
                        box-shadow: 0 4px 14px rgba(255, 102, 0, 0.35);
                    }
                    .btn-confirm:hover {
                        background: #ea580c;
                        transform: translateY(-1px);
                    }
                    .btn-confirm.danger {
                        background: #ef4444;
                        box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35);
                    }
                    .btn-confirm.danger:hover {
                        background: #dc2626;
                    }
                    .toast-container {
                        position: fixed;
                        bottom: 24px;
                        right: 24px;
                        z-index: 9999999;
                        display: flex;
                        flex-direction: column;
                        gap: 10px;
                        pointer-events: none;
                        max-width: 380px;
                        width: calc(100% - 48px);
                    }
                    .toast-item {
                        pointer-events: auto;
                        background: #ffffff;
                        color: #0f172a;
                        border-radius: 14px;
                        padding: 14px 18px;
                        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15), 0 0 0 1px rgba(226, 232, 240, 0.8);
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        font-size: 13px;
                        font-weight: 700;
                        transform: translateY(20px) scale(0.95);
                        opacity: 0;
                        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
                    }
                    .toast-item.dark {
                        background: #0f172a;
                        color: #f8fafc;
                        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(51, 65, 85, 0.8);
                    }
                    .toast-item.show {
                        transform: translateY(0) scale(1);
                        opacity: 1;
                    }
                    .toast-icon {
                        width: 22px;
                        height: 22px;
                        shrink: 0;
                        fill: currentColor;
                    }
                    .toast-success .toast-icon { color: #10b981; }
                    .toast-error .toast-icon { color: #ef4444; }
                    .toast-warning .toast-icon { color: #f59e0b; }
                    .toast-info .toast-icon { color: #ff6600; }
                </style>
                <div class="dialog-backdrop">
                    <div class="dialog-card">
                        <div class="dialog-header-ribbon"></div>
                        <div class="dialog-body">
                            <div class="dialog-icon-wrapper">
                                <svg class="icon-svg" viewBox="0 0 24 24"></svg>
                            </div>
                            <div class="dialog-title"></div>
                            <div class="dialog-message"></div>
                            <div class="dialog-details-wrapper"></div>
                            <div class="dialog-input-wrapper">
                                <input type="text" class="dialog-input" autocomplete="off" />
                            </div>
                            <div class="dialog-actions">
                                <button class="btn btn-cancel"></button>
                                <button class="btn btn-confirm"></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="toast-container"></div>
            `;

            this._backdrop = this._shadow.querySelector('.dialog-backdrop');
            this._card = this._shadow.querySelector('.dialog-card');
            this._ribbon = this._shadow.querySelector('.dialog-header-ribbon');
            this._iconWrapper = this._shadow.querySelector('.dialog-icon-wrapper');
            this._iconSvg = this._shadow.querySelector('.icon-svg');
            this._titleEl = this._shadow.querySelector('.dialog-title');
            this._messageEl = this._shadow.querySelector('.dialog-message');
            this._detailsWrapper = this._shadow.querySelector('.dialog-details-wrapper');
            this._inputWrapper = this._shadow.querySelector('.dialog-input-wrapper');
            this._inputEl = this._shadow.querySelector('.dialog-input');
            this._cancelBtn = this._shadow.querySelector('.btn-cancel');
            this._confirmBtn = this._shadow.querySelector('.btn-confirm');
            this._toastContainer = this._shadow.querySelector('.toast-container');

            this._cancelBtn.addEventListener('click', () => this._handleAction(false));
            this._confirmBtn.addEventListener('click', () => this._handleAction(true));
            this._inputEl.addEventListener('keyup', (e) => {
                if (e.key === 'Enter') this._handleAction(true);
                if (e.key === 'Escape') this._handleAction(false);
            });

            window.addEventListener('keydown', (e) => {
                if (this._backdrop.classList.contains('active')) {
                    if (e.key === 'Escape') {
                        e.stopImmediatePropagation();
                        this._handleAction(false);
                    }
                }
            });
        }

        _getIconSvg(type) {
            switch (type) {
                case 'danger':
                    return '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>';
                case 'warning':
                    return '<path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>';
                case 'success':
                    return '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>';
                default: // info
                    return '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>';
            }
        }

        _syncTheme() {
            const isDark = document.documentElement.classList.contains('dark') || 
                           localStorage.getItem('darkMode') === 'true';
            this._card.classList.toggle('dark', isDark);
            return isDark;
        }

        showDialog(options) {
            return new Promise((resolve, reject) => {
                this._activeResolve = resolve;
                this._activeReject = reject;

                this._syncTheme();

                const {
                    title = 'Pemberitahuan',
                    message = '',
                    detailsHtml = '',
                    type = 'info',
                    confirmText = 'OK',
                    cancelText = 'Batal',
                    showCancel = false,
                    showInput = false,
                    inputValue = '',
                    placeholder = ''
                } = options;

                this._ribbon.className = 'dialog-header-ribbon ' + type;
                this._iconWrapper.className = 'dialog-icon-wrapper ' + type;
                this._confirmBtn.className = 'btn btn-confirm ' + (type === 'danger' ? 'danger' : '');
                this._iconSvg.innerHTML = this._getIconSvg(type);

                this._titleEl.textContent = title;
                this._messageEl.textContent = message;
                this._confirmBtn.textContent = confirmText;
                this._cancelBtn.textContent = cancelText;

                // Details HTML (e.g. Transaction Item Table)
                if (detailsHtml) {
                    this._detailsWrapper.innerHTML = detailsHtml;
                    this._detailsWrapper.classList.add('active');
                } else {
                    this._detailsWrapper.innerHTML = '';
                    this._detailsWrapper.classList.remove('active');
                }

                this._cancelBtn.style.display = showCancel ? 'inline-flex' : 'none';

                if (showInput) {
                    this._inputWrapper.classList.add('active');
                    this._inputEl.value = inputValue;
                    this._inputEl.placeholder = placeholder;
                    setTimeout(() => this._inputEl.focus(), 150);
                } else {
                    this._inputWrapper.classList.remove('active');
                }

                this._backdrop.classList.add('active');
                if (!showInput) {
                    setTimeout(() => this._confirmBtn.focus(), 100);
                }
            });
        }

        _handleAction(confirmed) {
            if (!this._backdrop.classList.contains('active')) return;

            this._backdrop.classList.remove('active');

            if (this._activeResolve) {
                const resolve = this._activeResolve;
                this._activeResolve = null;
                this._activeReject = null;

                if (this._inputWrapper.classList.contains('active')) {
                    resolve(confirmed ? this._inputEl.value : null);
                } else {
                    resolve(confirmed);
                }
            }
        }

        showToast(message, type = 'info', duration = 3500) {
            const isDark = this._syncTheme();
            const toast = document.createElement('div');
            toast.className = `toast-item toast-${type} ${isDark ? 'dark' : ''}`;
            toast.innerHTML = `
                <svg class="toast-icon" viewBox="0 0 24 24">${this._getIconSvg(type)}</svg>
                <span></span>
            `;
            toast.querySelector('span').textContent = message;

            this._toastContainer.appendChild(toast);
            requestAnimationFrame(() => toast.classList.add('show'));

            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 350);
            }, duration);
        }
    }

    if (!customElements.get('secure-dialog-container')) {
        customElements.define('secure-dialog-container', SecureDialogContainer);
    }

    let dialogContainer = document.querySelector('secure-dialog-container');
    if (!dialogContainer) {
        dialogContainer = document.createElement('secure-dialog-container');
        document.documentElement.appendChild(dialogContainer);
    }

    const AppDialog = {
        alert: function (title, message, options = {}) {
            return dialogContainer.showDialog({
                title: typeof title === 'string' ? title : 'Pemberitahuan',
                message: typeof message === 'string' ? message : String(title || ''),
                detailsHtml: options.detailsHtml || '',
                type: options.type || 'info',
                confirmText: options.confirmText || 'Selesai',
                showCancel: false
            });
        },
        confirm: function (title, message, options = {}) {
            return dialogContainer.showDialog({
                title: typeof title === 'string' ? title : 'Konfirmasi Action',
                message: typeof message === 'string' ? message : String(title || ''),
                detailsHtml: options.detailsHtml || '',
                type: options.type || 'warning',
                confirmText: options.confirmText || 'Ya, Lanjutkan',
                cancelText: options.cancelText || 'Batal',
                showCancel: true
            });
        },
        danger: function (title, message, options = {}) {
            return dialogContainer.showDialog({
                title: typeof title === 'string' ? title : 'Konfirmasi Penghapusan',
                message: typeof message === 'string' ? message : String(title || ''),
                detailsHtml: options.detailsHtml || '',
                type: 'danger',
                confirmText: options.confirmText || 'Ya, Hapus Data',
                cancelText: options.cancelText || 'Batal',
                showCancel: true
            });
        },
        prompt: function (title, message, options = {}) {
            return dialogContainer.showDialog({
                title: typeof title === 'string' ? title : 'Input Informasi',
                message: typeof message === 'string' ? message : String(title || ''),
                detailsHtml: options.detailsHtml || '',
                type: options.type || 'info',
                confirmText: options.confirmText || 'Simpan',
                cancelText: options.cancelText || 'Batal',
                showCancel: true,
                showInput: true,
                inputValue: options.inputValue || '',
                placeholder: options.placeholder || ''
            });
        }
    };

    const Toast = {
        success: function (msg, duration) { dialogContainer.showToast(msg, 'success', duration); },
        error: function (msg, duration) { dialogContainer.showToast(msg, 'danger', duration); },
        warning: function (msg, duration) { dialogContainer.showToast(msg, 'warning', duration); },
        info: function (msg, duration) { dialogContainer.showToast(msg, 'info', duration); }
    };

    Object.freeze(AppDialog);
    Object.freeze(Toast);

    Object.defineProperty(window, 'AppDialog', {
        value: AppDialog,
        writable: false,
        configurable: false
    });

    Object.defineProperty(window, 'Toast', {
        value: Toast,
        writable: false,
        configurable: false
    });

    window.alert = function (msg) {
        AppDialog.alert('Pemberitahuan Sistem', String(msg || ''));
    };

    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form || form.getAttribute('data-confirm-bypassed') === 'true') return;

        const onsubmitAttr = form.getAttribute('onsubmit');
        const dataConfirm = form.getAttribute('data-confirm');

        let confirmMsg = null;
        if (dataConfirm) {
            confirmMsg = dataConfirm;
        } else if (onsubmitAttr && onsubmitAttr.includes('confirm(')) {
            const match = onsubmitAttr.match(/confirm\((['"])(.*?)\1\)/);
            if (match && match[2]) {
                confirmMsg = match[2];
            }
        }

        if (confirmMsg) {
            e.preventDefault();
            e.stopImmediatePropagation();

            const isDelete = (form.querySelector('input[name="_method"]')?.value || '').toUpperCase() === 'DELETE' || confirmMsg.toLowerCase().includes('hapus');

            const actionMethod = isDelete ? AppDialog.danger : AppDialog.confirm;
            actionMethod(
                isDelete ? 'Konfirmasi Tindakan' : 'Konfirmasi Operasi',
                confirmMsg,
                { confirmText: isDelete ? 'Ya, Lanjutkan' : 'Ya, Setuju' }
            ).then((confirmed) => {
                if (confirmed) {
                    form.setAttribute('data-confirm-bypassed', 'true');
                    form.submit();
                }
            });
        }
    }, true);

})();
