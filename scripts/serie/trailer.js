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
        
        const closeBtn = trailerModalElement.querySelector('.btn-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                stopTrailer();
                setTimeout(function() {
                    trailerModal.hide();
                }, 50);
            });
        }

        btnTrailer.addEventListener('click', function() {
            currentYoutubeUrl = "https://www.youtube.com/embed/" + youtubeId + "?autoplay=1&enablejsapi=1";
            trailerIframe.src = currentYoutubeUrl;
            trailerModal.show();
        });

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
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTrailer);
    } else {
        initTrailer();
    }
})();

