(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        const btnTrailer = document.getElementById('btnTrailer');
        const trailerModalElement = document.getElementById('trailerModal');
        const trailerIframe = document.getElementById('trailerIframe');
        const youtubeId = window.serieYoutubeId || '';

        if (!btnTrailer || !trailerModalElement || !trailerIframe || !youtubeId) {
            return;
        }

        const trailerModal = new bootstrap.Modal(trailerModalElement);

        btnTrailer.addEventListener('click', function() {
            trailerIframe.src = "https://www.youtube.com/embed/" + youtubeId + "?autoplay=1";
            trailerModal.show();
        });

        trailerModalElement.addEventListener('hidden.bs.modal', function () {
            trailerIframe.src = "";
        });
    });
})();

