(function() {
    'use strict';

    function initSagasAdmin() {
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSagasAdmin);
    } else {
        initSagasAdmin();
    }
})();

