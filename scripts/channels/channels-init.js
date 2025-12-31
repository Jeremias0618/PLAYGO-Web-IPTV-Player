(function() {
    function initializeChannels(categoriasCanales) {
        if (window.innerWidth <= 600) {
            if (window.MobileCategories) {
                window.mobileCategoriesInstance = window.MobileCategories(categoriasCanales);
            }
            if (window.ChannelNavigation) {
                window.ChannelNavigation();
            }
        }

        if (window.ChannelFilter) {
            window.channelFilterInstance = window.ChannelFilter(categoriasCanales);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof categoriasCanales !== 'undefined') {
            initializeChannels(categoriasCanales);
        }
    });

    window.addEventListener('resize', function() {
        if (typeof categoriasCanales !== 'undefined') {
            initializeChannels(categoriasCanales);
        }
    });
})();

