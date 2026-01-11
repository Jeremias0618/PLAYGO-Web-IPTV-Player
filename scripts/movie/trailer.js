(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        const btnTrailer = document.getElementById('btnTrailer');
        const trailerModalElement = document.getElementById('trailerModal');
        const trailerIframe = document.getElementById('trailerIframe');
        const trailerCloseBtn = trailerModalElement ? trailerModalElement.querySelector('.btn-close') : null;
        const youtubeId = window.movieYoutubeId || '';
        
        if (!btnTrailer || !trailerModalElement || !trailerIframe || !youtubeId) {
            return;
        }
        
        const trailerModal = new bootstrap.Modal(trailerModalElement);
        
        btnTrailer.addEventListener('click', function() {
            trailerIframe.src = "https://www.youtube.com/embed/" + youtubeId + "?autoplay=1";
            trailerModal.show();
        });
        
        if (trailerCloseBtn) {
            trailerCloseBtn.addEventListener('click', function(e) {
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
    });
})();

