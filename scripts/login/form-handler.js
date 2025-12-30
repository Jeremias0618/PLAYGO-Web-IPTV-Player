(function() {
    'use strict';

    const FormHandler = {
        init: function() {
            const form = document.querySelector('form');
            const blockedAlert = document.querySelector('.alert-blocked');

            if (!form || blockedAlert) return;

            this.preventMultipleSubmit(form);
        },

        preventMultipleSubmit: function(form) {
            let isSubmitting = false;
            const submitBtn = document.querySelector('.login-btn');

            form.addEventListener('submit', function(e) {
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }
                isSubmitting = true;
                if (submitBtn) {
                    submitBtn.textContent = 'VERIFICANDO...';
                    submitBtn.disabled = true;
                }
            });
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            FormHandler.init();
        });
    } else {
        FormHandler.init();
    }
})();

