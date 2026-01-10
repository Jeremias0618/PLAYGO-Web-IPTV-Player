(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        const seasonSelect = document.getElementById('seasonSelect');
        const viewGridBtn = document.getElementById('viewGridBtn');
        const viewListBtn = document.getElementById('viewListBtn');
        const episodesContainers = document.querySelectorAll('.episodes-container');
        
        if (!seasonSelect || !viewGridBtn || !viewListBtn) {
            return;
        }
        
        // Manejar cambio de temporada
        seasonSelect.addEventListener('change', function() {
            const selectedSeason = this.value;
            const targetPane = document.getElementById('season-' + selectedSeason);
            
            if (targetPane) {
                // Ocultar todos los panes
                document.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.classList.remove('show', 'active');
                });
                
                // Mostrar el pane seleccionado
                targetPane.classList.add('show', 'active');
            }
        });
        
        // Manejar cambio a vista de cuadrícula
        viewGridBtn.addEventListener('click', function() {
            episodesContainers.forEach(container => {
                container.setAttribute('data-view', 'grid');
                container.classList.remove('episodes-list-view');
                container.classList.add('episodes-grid-view');
            });
            
            viewGridBtn.style.background = 'linear-gradient(90deg,#e50914 60%,#c8008f 100%)';
            viewGridBtn.style.border = 'none';
            viewListBtn.style.background = '#232027';
            viewListBtn.style.border = '1px solid #444';
        });
        
        // Manejar cambio a vista de lista
        viewListBtn.addEventListener('click', function() {
            episodesContainers.forEach(container => {
                container.setAttribute('data-view', 'list');
                container.classList.remove('episodes-grid-view');
                container.classList.add('episodes-list-view');
            });
            
            viewListBtn.style.background = 'linear-gradient(90deg,#e50914 60%,#c8008f 100%)';
            viewListBtn.style.border = 'none';
            viewGridBtn.style.background = '#232027';
            viewGridBtn.style.border = '1px solid #444';
        });
        
        // Inicializar vista de cuadrícula
        episodesContainers.forEach(container => {
            container.classList.add('episodes-grid-view');
        });
    });
})();
