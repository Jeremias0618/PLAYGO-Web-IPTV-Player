(function() {
    'use strict';

    window.SagasAdminUtils = {
        escapeHtml: function(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };
})();

