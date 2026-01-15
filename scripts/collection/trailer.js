(function() {
    'use strict';

    function initTrailerModal() {
        const trailerButtons = document.querySelectorAll('.collection-btn-trailer');
        const trailerModalElement = document.getElementById('trailerModal');
        const trailerIframe = document.getElementById('trailerIframe');
        const trailerModalTitle = document.getElementById('trailerModalTitle');

        if (!trailerModalElement || !trailerIframe) {
            return;
        }

        let trailerModal = null;
        
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            try {
                trailerModal = new bootstrap.Modal(trailerModalElement, {
                    backdrop: true,
                    keyboard: true,
                    focus: true
                });
            } catch (e) {
            }
        }

        trailerButtons.forEach(function(btn) {
            let youtubeId = btn.getAttribute('data-youtube-id');
            const isDisabled = btn.hasAttribute('disabled') || btn.disabled;
            
            if (!youtubeId || youtubeId === '' || youtubeId === 'null' || youtubeId === 'undefined') {
                btn.disabled = true;
                btn.style.opacity = '0.5';
                btn.style.cursor = 'not-allowed';
            } else {
                btn.disabled = false;
                btn.removeAttribute('disabled');
                btn.style.opacity = '1';
                btn.style.cursor = 'pointer';
            }
            
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const currentYoutubeId = btn.getAttribute('data-youtube-id');
                const movieTitle = btn.getAttribute('data-movie-title');
                
                if (btn.disabled || !currentYoutubeId || currentYoutubeId === '' || currentYoutubeId === 'null' || currentYoutubeId === 'undefined') {
                    alert('Tráiler no disponible para esta película');
                    return;
                }
                
                if (currentYoutubeId && currentYoutubeId !== '' && currentYoutubeId !== 'null' && currentYoutubeId !== 'undefined') {
                    if (trailerModalTitle) {
                        trailerModalTitle.textContent = movieTitle ? movieTitle + ' - Tráiler' : 'Tráiler';
                    }
                    
                    const embedUrl = "https://www.youtube.com/embed/" + currentYoutubeId + "?autoplay=1&rel=0&modestbranding=1";
                    trailerIframe.src = embedUrl;
                    
                    if (trailerModal) {
                        trailerModalElement.setAttribute('aria-hidden', 'false');
                        trailerModal.show();
                    } else if (typeof $ !== 'undefined' && $.fn.modal) {
                        trailerModalElement.setAttribute('aria-hidden', 'false');
                        $(trailerModalElement).modal('show');
                    } else {
                        trailerModalElement.style.display = 'block';
                        trailerModalElement.classList.add('show');
                        trailerModalElement.setAttribute('aria-hidden', 'false');
                        document.body.classList.add('modal-open');
                        const backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        backdrop.style.zIndex = '9999';
                        backdrop.style.backgroundColor = 'rgba(0, 0, 0, 0.75)';
                        backdrop.style.position = 'fixed';
                        backdrop.style.top = '0';
                        backdrop.style.left = '0';
                        backdrop.style.width = '100%';
                        backdrop.style.height = '100%';
                        document.body.appendChild(backdrop);
                        
                        backdrop.addEventListener('click', function() {
                            closeTrailerModal();
                        });
                    }
                    
                    function closeTrailerModal() {
                        trailerModalElement.style.display = 'none';
                        trailerModalElement.classList.remove('show');
                        trailerModalElement.setAttribute('aria-hidden', 'true');
                        document.body.classList.remove('modal-open');
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) {
                            backdrop.remove();
                        }
                        if (trailerIframe) {
                            trailerIframe.src = '';
                        }
                    }
                } else {
                    alert('Tráiler no disponible para esta película');
                }
            });
        });

        if (trailerModalElement) {
            trailerModalElement.addEventListener('hidden.bs.modal', function () {
                trailerModalElement.setAttribute('aria-hidden', 'true');
                if (trailerIframe) {
                    trailerIframe.src = "";
                }
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                document.body.classList.remove('modal-open');
            });
            
            trailerModalElement.addEventListener('hide.bs.modal', function () {
                if (trailerIframe) {
                    trailerIframe.src = "";
                }
            });
            
            const closeBtn = trailerModalElement.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    if (trailerModal) {
                        trailerModal.hide();
                    } else if (typeof $ !== 'undefined' && $.fn.modal) {
                        $(trailerModalElement).modal('hide');
                    } else {
                        trailerModalElement.style.display = 'none';
                        trailerModalElement.classList.remove('show');
                        trailerModalElement.setAttribute('aria-hidden', 'true');
                        document.body.classList.remove('modal-open');
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) {
                            backdrop.remove();
                        }
                        if (trailerIframe) {
                            trailerIframe.src = '';
                        }
                    }
                });
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTrailerModal);
    } else {
        initTrailerModal();
    }
})();

