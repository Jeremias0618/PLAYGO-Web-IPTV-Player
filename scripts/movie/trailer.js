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
            trailerCloseBtn.addEventListener('click', function() {
                trailerModal.hide();
            });
        }
        
        trailerModalElement.addEventListener('hidden.bs.modal', function () {
            trailerIframe.src = "";
        });
    });
})();

