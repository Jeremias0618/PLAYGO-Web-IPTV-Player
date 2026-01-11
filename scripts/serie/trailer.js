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
                stopTrailer();
                trailerModal.hide();
            });
        }

        function stopTrailer() {
            if (trailerIframe) {
                trailerIframe.src = "about:blank";
            }
        }

        btnTrailer.addEventListener('click', function() {
            trailerIframe.src = "https://www.youtube.com/embed/" + youtubeId + "?autoplay=1";
            trailerModal.show();
        });

        trailerModalElement.addEventListener('hide.bs.modal', function () {
            stopTrailer();
        });

        trailerModalElement.addEventListener('hidden.bs.modal', function () {
            stopTrailer();
        });
        
        trailerModalElement.addEventListener('click', function(e) {
            if (e.target === trailerModalElement) {
                stopTrailer();
            }
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTrailer);
    } else {
        initTrailer();
    }
})();

