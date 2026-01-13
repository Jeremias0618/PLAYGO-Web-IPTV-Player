(function() {
    'use strict';

    function initSagasAdmin() {
        if (typeof window.SagasAdminSagas !== 'undefined' && typeof window.SagasAdminSagas.load === 'function') {
            window.SagasAdminSagas.load();
        } else if (typeof window.loadSagas === 'function') {
            window.loadSagas();
        } else {
            setTimeout(() => {
                if (typeof window.SagasAdminSagas !== 'undefined' && typeof window.SagasAdminSagas.load === 'function') {
                    window.SagasAdminSagas.load();
                } else if (typeof window.loadSagas === 'function') {
                    window.loadSagas();
                }
            }, 500);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSagasAdmin);
    } else {
        initSagasAdmin();
    }
})();

