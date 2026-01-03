(function() {
    'use strict';
    if (typeof window !== 'undefined' && window.jwplayer) {
        if (!window.jwplayer.plugins) {
            window.jwplayer.plugins = {};
        }
        window.jwplayer.plugins.related = function() {
            return {};
        };
    }
})();

