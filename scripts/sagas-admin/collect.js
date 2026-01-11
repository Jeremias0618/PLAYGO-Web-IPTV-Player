(function() {
    'use strict';

    let moviesCache = [];
    let groupedMovies = {};

    function collectMovies() {
        const btn = document.getElementById('collectMoviesBtn');
        if (!btn) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Recolectando...';

        fetch('libs/endpoints/SagasAdmin.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=collect_movies'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                moviesCache = data.movies;
                groupMoviesByName();
                displayGroupedMovies();
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check"></i> Recolectado';
                setTimeout(() => {
                    btn.innerHTML = '<i class="fas fa-download"></i> Recolectar Películas';
                }, 2000);
            } else {
                alert('Error: ' + (data.error || 'No se pudieron recolectar las películas'));
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-download"></i> Recolectar Películas';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al recolectar películas');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-download"></i> Recolectar Películas';
        });
    }

    function groupMoviesByName() {
        groupedMovies = {};
        
        moviesCache.forEach(movie => {
            if (!movie.name || !movie.id) return;
            
            const baseName = extractBaseName(movie.name);
            
            if (!groupedMovies[baseName]) {
                groupedMovies[baseName] = [];
            }
            
            groupedMovies[baseName].push(movie);
        });
    }

    function extractBaseName(name) {
        name = name.trim();
        
        name = name.replace(/^(Saga|SAGA|Série|SERIE|Serie|Series|SERIES|Movie|MOVIE|Film|FILM)\s*/i, '');
        
        name = name.replace(/\s*-\s*Parte\s*\d+.*$/i, '');
        name = name.replace(/\s*-\s*Part\s*\d+.*$/i, '');
        name = name.replace(/\s*-\s*Vol\.\s*\d+.*$/i, '');
        name = name.replace(/\s*-\s*Volume\s*\d+.*$/i, '');
        name = name.replace(/\s*\(Parte\s*\d+\)/i, '');
        name = name.replace(/\s*\(Part\s*\d+\)/i, '');
        
        name = name.replace(/\s*-\s*\d{4}.*$/i, '');
        name = name.replace(/\s*\(\d{4}\).*$/i, '');
        
        name = name.replace(/\s*:\s*.*$/i, '');
        
        name = name.replace(/\s*-\s*S\d+E\d+.*$/i, '');
        
        return name.trim();
    }

    function displayGroupedMovies() {
        const container = document.getElementById('groupedMoviesContainer');
        if (!container) return;

        if (Object.keys(groupedMovies).length === 0) {
            container.innerHTML = '<div class="sagas-admin-message">No se encontraron agrupaciones</div>';
            return;
        }

        let html = '<div class="sagas-admin-groups">';
        
        Object.keys(groupedMovies).sort().forEach(baseName => {
            const movies = groupedMovies[baseName];
            if (movies.length < 2) return;
            
            const groupId = 'group_' + baseName.replace(/[^a-zA-Z0-9]/g, '_');
            
            html += `
                <div class="sagas-admin-group" data-group-name="${baseName}">
                    <div class="sagas-admin-group-header" onclick="toggleGroup('${groupId}')">
                        <span class="sagas-admin-group-title">${escapeHtml(baseName)}</span>
                        <span class="sagas-admin-group-count">${movies.length} películas</span>
                        <i class="fas fa-chevron-down sagas-admin-group-icon" id="icon_${groupId}"></i>
                    </div>
                    <div class="sagas-admin-group-content" id="${groupId}" style="display: none;">
                        <div class="sagas-admin-movies-list">
                            ${movies.map(movie => `
                                <div class="sagas-admin-movie-item">
                                    <span class="sagas-admin-movie-name">${escapeHtml(movie.name)}</span>
                                    <span class="sagas-admin-movie-id">ID: ${movie.id}</span>
                                </div>
                            `).join('')}
                        </div>
                        <button class="sagas-admin-btn-confirm" onclick="openSagaModal('${baseName}', ${JSON.stringify(movies).replace(/"/g, '&quot;')})">
                            <i class="fas fa-check"></i> Crear Saga
                        </button>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        container.innerHTML = html;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    window.collectMovies = collectMovies;
    window.toggleGroup = function(groupId) {
        const content = document.getElementById(groupId);
        const icon = document.getElementById('icon_' + groupId);
        
        if (content.style.display === 'none') {
            content.style.display = 'block';
            if (icon) icon.classList.add('fa-chevron-up');
            if (icon) icon.classList.remove('fa-chevron-down');
        } else {
            content.style.display = 'none';
            if (icon) icon.classList.add('fa-chevron-down');
            if (icon) icon.classList.remove('fa-chevron-up');
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('collectMoviesBtn');
            if (btn) {
                btn.addEventListener('click', collectMovies);
            }
        });
    } else {
        const btn = document.getElementById('collectMoviesBtn');
        if (btn) {
            btn.addEventListener('click', collectMovies);
        }
    }
})();

