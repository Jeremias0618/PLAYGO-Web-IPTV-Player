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
        let currentYoutubeUrl = '';
        
        function stopTrailer() {
            if (trailerIframe) {
                const currentSrc = trailerIframe.src;
                
                if (currentSrc && currentSrc.includes('youtube.com')) {
                    try {
                        trailerIframe.contentWindow.postMessage('{"event":"command","func":"stopVideo","args":""}', '*');
                    } catch (e) {
                    }
                    
                    setTimeout(function() {
                        try {
                            const url = new URL(currentSrc);
                            const videoId = url.pathname.split('/').pop().split('?')[0];
                            if (videoId) {
                                trailerIframe.src = "https://www.youtube.com/embed/" + videoId + "?autoplay=0&enablejsapi=1";
                            }
                        } catch (e) {
                        }
                        
                        setTimeout(function() {
                            trailerIframe.src = "about:blank";
                        }, 200);
                    }, 100);
                } else {
                    trailerIframe.src = "about:blank";
                }
            }
        }
        
        btnTrailer.addEventListener('click', function() {
            currentYoutubeUrl = "https://www.youtube.com/embed/" + youtubeId + "?autoplay=1&enablejsapi=1";
            trailerIframe.src = currentYoutubeUrl;
            trailerModal.show();
        });
        
        if (trailerCloseBtn) {
            trailerCloseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                stopTrailer();
                setTimeout(function() {
                    trailerModal.hide();
                }, 50);
            });
        }
        
        trailerModalElement.addEventListener('hide.bs.modal', function (e) {
            stopTrailer();
        });
        
        trailerModalElement.addEventListener('hidden.bs.modal', function (e) {
            stopTrailer();
        });
        
        trailerModalElement.addEventListener('click', function(e) {
            if (e.target === trailerModalElement) {
                stopTrailer();
            }
        });
    });
})();

