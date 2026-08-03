/**
 * Istana Laundry ERP - Global Real-Time Live Form Validation Engine
 * Validates inputs instantly on typing ('input', 'change', 'blur') without waiting for form submission.
 */
(function () {
    'use strict';

    class RealtimeFormValidator {
        constructor() {
            this.rules = {
                required: (val) => val !== null && val !== undefined && String(val).trim() !== '',
                email: (val) => !val || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val),
                numeric: (val) => !val || !isNaN(Number(val)),
                positive: (val) => !val || Number(val) > 0,
                phone: (val) => !val || /^(08|\+628)[0-9]{8,12}$/.test(val.replace(/[\s-]/g, '')),
                minlen: (val, len) => !val || String(val).trim().length >= Number(len),
                maxlen: (val, len) => !val || String(val).trim().length <= Number(len),
                min: (val, minVal) => !val || Number(val) >= Number(minVal),
                max: (val, maxVal) => !val || Number(val) <= Number(maxVal)
            };

            this.messages = {
                required: 'Wajib diisi',
                email: 'Format email tidak valid (contoh: user@domain.com)',
                numeric: 'Harus berupa angka',
                positive: 'Harus bernilai lebih dari 0',
                phone: 'Format No. HP tidak valid (contoh: 08123456789)',
                minlen: (len) => `Minimal ${len} karakter`,
                maxlen: (len) => `Maksimal ${len} karakter`,
                min: (minVal) => `Nilai minimal ${minVal}`,
                max: (maxVal) => `Nilai maksimal ${maxVal}`
            };

            this._init();
        }

        _init() {
            document.addEventListener('DOMContentLoaded', () => {
                this.attachToForms();
            });

            // Re-attach dynamically for SPA / Alpine modals
            const observer = new MutationObserver(() => this.attachToForms());
            observer.observe(document.body, { childList: true, subtree: true });
        }

        attachToForms() {
            const inputs = document.querySelectorAll('input:not([type="hidden"]):not([type="submit"]), select, textarea');
            inputs.forEach(input => {
                if (input.dataset.realtimeBound) return;
                input.dataset.realtimeBound = 'true';

                const handler = () => this.validateInput(input);
                input.addEventListener('input', handler);
                input.addEventListener('change', handler);
                input.addEventListener('blur', handler);
            });
        }

        validateInput(input) {
            // Skip hidden or disabled inputs
            if (input.type === 'hidden' || input.disabled || input.offsetParent === null) return true;

            const value = input.value;
            const errorContainer = this._getOrCreateErrorElement(input);
            let errorMessage = null;

            // Check standard HTML5 validation attributes
            if (input.hasAttribute('required') && !this.rules.required(value)) {
                errorMessage = input.dataset.msgRequired || this.messages.required;
            } else if (input.type === 'email' && !this.rules.email(value)) {
                errorMessage = input.dataset.msgEmail || this.messages.email;
            } else if (input.type === 'number') {
                if (value !== '' && !this.rules.numeric(value)) {
                    errorMessage = this.messages.numeric;
                } else if (input.hasAttribute('min') && !this.rules.min(value, input.min)) {
                    errorMessage = this.messages.min(input.min);
                } else if (input.hasAttribute('max') && !this.rules.max(value, input.max)) {
                    errorMessage = this.messages.max(input.max);
                }
            } else if (input.name === 'phone' || input.dataset.rule === 'phone') {
                if (!this.rules.phone(value)) {
                    errorMessage = this.messages.phone;
                }
            }

            // Custom data-rules="required|phone|minlen:5"
            if (!errorMessage && input.dataset.rules) {
                const rulesArr = input.dataset.rules.split('|');
                for (const ruleStr of rulesArr) {
                    const [ruleName, param] = ruleStr.split(':');
                    if (this.rules[ruleName]) {
                        const isValid = this.rules[ruleName](value, param);
                        if (!isValid) {
                            errorMessage = typeof this.messages[ruleName] === 'function' 
                                ? this.messages[ruleName](param) 
                                : this.messages[ruleName];
                            break;
                        }
                    }
                }
            }

            // UI State update
            if (errorMessage) {
                input.classList.add('border-rose-500', 'focus:border-rose-500', 'ring-2', 'ring-rose-500/20');
                input.classList.remove('border-emerald-500', 'ring-emerald-500/20');
                errorContainer.textContent = errorMessage;
                errorContainer.style.display = 'block';
                return false;
            } else {
                input.classList.remove('border-rose-500', 'focus:border-rose-500', 'ring-2', 'ring-rose-500/20');
                if (value !== '') {
                    input.classList.add('border-emerald-500');
                } else {
                    input.classList.remove('border-emerald-500');
                }
                errorContainer.textContent = '';
                errorContainer.style.display = 'none';
                return true;
            }
        }

        _getOrCreateErrorElement(input) {
            let errorEl = input.parentNode.querySelector('.realtime-error-msg');
            if (!errorEl) {
                errorEl = document.createElement('div');
                errorEl.className = 'realtime-error-msg text-2xs font-extrabold text-rose-500 mt-1 flex items-center gap-1 transition-all animate-fadeIn';
                errorEl.style.display = 'none';
                input.parentNode.appendChild(errorEl);
            }
            return errorEl;
        }

        validateForm(form) {
            const inputs = form.querySelectorAll('input:not([type="hidden"]), select, textarea');
            let isValid = true;
            inputs.forEach(input => {
                const valid = this.validateInput(input);
                if (!valid) isValid = false;
            });
            return isValid;
        }
    }

    window.RealtimeFormValidator = new RealtimeFormValidator();
})();
