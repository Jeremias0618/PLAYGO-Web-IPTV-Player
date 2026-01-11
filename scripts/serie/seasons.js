(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        const seasonSelect = document.getElementById('seasonSelect');
        const viewToggleBtn = document.getElementById('viewToggleBtn');
        const episodesContainers = document.querySelectorAll('.episodes-container');
        
        if (!seasonSelect || !viewToggleBtn) {
            return;
        }
        
        seasonSelect.addEventListener('change', function() {
            const selectedSeason = this.value;
            const targetPane = document.getElementById('season-' + selectedSeason);
            
            if (targetPane) {
                document.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.classList.remove('show', 'active');
                });
                
                targetPane.classList.add('show', 'active');
            }
        });
        
        function setupEpisodeCards() {
            const episodeCards = document.querySelectorAll('.episode-card');
            episodeCards.forEach(card => {
                const container = card.closest('.episodes-container');
                if (!container) return;
                
                const isListView = container.classList.contains('episodes-list-view');
                const btnPlay = card.querySelector('.btn-play');
                
                card.removeEventListener('click', card._listViewClickHandler);
                
                if (isListView && btnPlay) {
                    card.style.cursor = 'pointer';
                    const episodeUrl = btnPlay.getAttribute('href');
                    if (episodeUrl) {
                        card._listViewClickHandler = function(e) {
                            if (e.target.closest('.btn-play')) {
                                e.stopPropagation();
                                return;
                            }
                            e.preventDefault();
                            window.location.href = episodeUrl;
                        };
                        card.addEventListener('click', card._listViewClickHandler);
                    }
                } else {
                    card.style.cursor = '';
                }
            });
        }
        
        viewToggleBtn.addEventListener('click', function() {
            const currentView = this.getAttribute('data-view');
            const newView = currentView === 'grid' ? 'list' : 'grid';
            const viewText = this.querySelector('.view-toggle-text');
            
            this.setAttribute('data-view', newView);
            
            if (viewText) {
                viewText.textContent = newView === 'grid' ? 'Cuadrícula' : 'Lista';
            }
            
            episodesContainers.forEach(container => {
                container.setAttribute('data-view', newView);
                
                if (newView === 'grid') {
                    container.classList.remove('episodes-list-view');
                    container.classList.add('episodes-grid-view');
                } else {
                    container.classList.remove('episodes-grid-view');
                    container.classList.add('episodes-list-view');
                }
            });
            
            setTimeout(setupEpisodeCards, 50);
        });
        
        function isMobile() {
            return window.innerWidth <= 600;
        }
        
        function applyView() {
            if (isMobile()) {
                episodesContainers.forEach(container => {
                    container.classList.remove('episodes-grid-view');
                    container.classList.add('episodes-list-view');
                    container.setAttribute('data-view', 'list');
                });
                if (viewToggleBtn) {
                    viewToggleBtn.setAttribute('data-view', 'list');
                    const viewText = viewToggleBtn.querySelector('.view-toggle-text');
                    if (viewText) {
                        viewText.textContent = 'Lista';
                    }
                }
            } else {
                episodesContainers.forEach(container => {
                    container.classList.add('episodes-grid-view');
                    container.setAttribute('data-view', 'grid');
                });
            }
            setupEpisodeCards();
        }
        
        applyView();
        
        window.addEventListener('resize', function() {
            if (isMobile()) {
                applyView();
            }
        });
    });
})();
