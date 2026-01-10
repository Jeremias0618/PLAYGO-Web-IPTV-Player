(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        const seasonSelect = document.getElementById('seasonSelect');
        const viewToggleBtn = document.getElementById('viewToggleBtn');
        const episodesContainers = document.querySelectorAll('.episodes-container');
        
        if (!seasonSelect || !viewToggleBtn) {
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
        
        // Manejar toggle de vista (cuadrícula <-> lista)
        viewToggleBtn.addEventListener('click', function() {
            const currentView = this.getAttribute('data-view');
            const newView = currentView === 'grid' ? 'list' : 'grid';
            const viewText = this.querySelector('.view-toggle-text');
            
            // Actualizar el atributo data-view del botón
            this.setAttribute('data-view', newView);
            
            // Actualizar el texto según la vista
            if (viewText) {
                viewText.textContent = newView === 'grid' ? 'Cuadrícula' : 'Lista';
            }
            
            // Cambiar la vista de todos los contenedores
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
        });
        
        // Inicializar vista de cuadrícula
        episodesContainers.forEach(container => {
            container.classList.add('episodes-grid-view');
            container.setAttribute('data-view', 'grid');
        });
    });
})();
