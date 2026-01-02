(function() {
    function getUrlCategoryId() {
        const urlParams = new URLSearchParams(window.location.search);
        const id = urlParams.get('id');
        return id || '';
    }

    function initializeChannels(categoriasCanales) {
        const categoryIdFromUrl = getUrlCategoryId();

        if (window.innerWidth <= 600) {
            if (window.MobileCategories) {
                window.mobileCategoriesInstance = window.MobileCategories(categoriasCanales);
                if (categoryIdFromUrl && window.mobileCategoriesInstance) {
                    window.mobileCategoriesInstance.setCategoria(categoryIdFromUrl);
                }
            }
            if (window.ChannelNavigation) {
                window.ChannelNavigation();
            }
        }

        if (window.ChannelFilter) {
            window.channelFilterInstance = window.ChannelFilter(categoriasCanales);
            if (categoryIdFromUrl && window.channelFilterInstance) {
                window.channelFilterInstance.setCategoria(categoryIdFromUrl);
            }
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

