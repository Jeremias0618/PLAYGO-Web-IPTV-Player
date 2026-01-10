(function() {
    'use strict';
    
    function initTrailer() {
        if (typeof bootstrap === 'undefined') {
            setTimeout(initTrailer, 50);
            return;
        }
        
        const btnTrailer = document.getElementById('btnTrailer');
        const trailerModalElement = document.getElementById('trailerModal');
        const trailerIframe = document.getElementById('trailerIframe');
        const youtubeId = window.serieYoutubeId || '';

        if (!btnTrailer || !trailerModalElement || !trailerIframe || !youtubeId) {
            return;
        }

        const trailerModal = new bootstrap.Modal(trailerModalElement);
        
        const closeBtn = trailerModalElement.querySelector('.btn-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                trailerModal.hide();
            });
        }

        btnTrailer.addEventListener('click', function() {
            trailerIframe.src = "https://www.youtube.com/embed/" + youtubeId + "?autoplay=1";
            trailerModal.show();
        });

        trailerModalElement.addEventListener('hidden.bs.modal', function () {
            trailerIframe.src = "";
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTrailer);
    } else {
        initTrailer();
    }
})();

