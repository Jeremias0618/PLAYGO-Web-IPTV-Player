(function() {
    'use strict';

    window.SagasAdminUtils = {
        escapeHtml: function(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },
        normalizeImagePath: function(path) {
            if (!path) return null;
            let normalized = path.replace(/^https?:\/\/[^\/]+/, '').replace(/^\//, '');
            const pathParts = normalized.split('/');
            const assetsIndex = pathParts.indexOf('assets');
            if (assetsIndex >= 0) {
                normalized = pathParts.slice(assetsIndex).join('/');
            }
            return normalized || null;
        }
    };
})();

