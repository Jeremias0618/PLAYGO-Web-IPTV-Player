(function() {
    'use strict';

    const BlockedHandler = {
        init: function() {
            const blockedAlert = document.querySelector('.alert-blocked');
            if (!blockedAlert) return;

            this.disableForm();
            this.startCountdown();
        },

        disableForm: function() {
            const form = document.querySelector('form');
            const inputs = document.querySelectorAll('.form-control');
            const submitBtn = document.querySelector('.login-btn');

            if (form) {
                form.style.pointerEvents = 'none';
                form.style.opacity = '0.5';
            }

            inputs.forEach(input => {
                input.disabled = true;
                input.style.cursor = 'not-allowed';
            });

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.style.cursor = 'not-allowed';
                submitBtn.textContent = 'BLOQUEADO';
            }
        },

        startCountdown: function() {
            const countdownElement = document.getElementById('countdown');
            if (!countdownElement) return;

            let timeLeft = parseInt(countdownElement.textContent);
            const countdownInterval = setInterval(() => {
                timeLeft--;
                countdownElement.textContent = timeLeft;

                if (timeLeft <= 0) {
                    clearInterval(countdownInterval);
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1000);
                }
            }, 60000);
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            BlockedHandler.init();
        });
    } else {
        BlockedHandler.init();
    }
})();

