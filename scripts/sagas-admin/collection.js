(function() {
    'use strict';

    let moviesCache = [];
    let groupedMovies = {};
    let ungroupedMovies = [];

    window.SagasAdminCollection = {
        collect: function() {
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
                    this.groupByName();
                    this.displayGrouped();
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
            .catch(() => {
                alert('Error al recolectar películas');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-download"></i> Recolectar Películas';
            });
        },

        groupByName: function() {
            groupedMovies = {};
            ungroupedMovies = [];

            moviesCache.forEach(movie => {
                if (!movie.name || !movie.id) return;

                const baseName = this.extractBaseName(movie.name);

                if (!groupedMovies[baseName]) {
                    groupedMovies[baseName] = [];
                }

                groupedMovies[baseName].push(movie);
            });

            Object.keys(groupedMovies).forEach(baseName => {
                if (groupedMovies[baseName].length < 2) {
                    ungroupedMovies = ungroupedMovies.concat(groupedMovies[baseName]);
                    delete groupedMovies[baseName];
                }
            });
        },

        extractBaseName: function(name) {
            if (!name) return '';

            name = name.trim();
            name = name.replace(/^(Saga|SAGA|Série|SERIE|Serie|Series|SERIES|Movie|MOVIE|Film|FILM)\s*/i, '');

            const patterns = [
                /\s*-\s*Parte\s*\d+.*$/i,
                /\s*-\s*Part\s*\d+.*$/i,
                /\s*-\s*Vol\.\s*\d+.*$/i,
                /\s*-\s*Volume\s*\d+.*$/i,
                /\s*\(Parte\s*\d+\)/i,
                /\s*\(Part\s*\d+\)/i,
                /\s*-\s*\d{4}.*$/i,
                /\s*\(\d{4}\).*$/i,
                /\s*-\s*S\d+E\d+.*$/i,
                /\s*:\s*El\s+.*$/i,
                /\s*:\s*La\s+.*$/i,
                /\s*:\s*Un\s+.*$/i,
                /\s*:\s*Una\s+.*$/i,
                /\s*:\s*.*$/i
            ];

            patterns.forEach(pattern => {
                name = name.replace(pattern, '');
            });

            name = name.trim();

            const words = name.split(/\s+/);
            if (words.length >= 2) {
                const firstTwo = words.slice(0, 2).join(' ');
                if (firstTwo.length > 3) {
                    return firstTwo;
                }
            }

            return words[0] || name;
        },

        displayGrouped: function() {
            const container = document.getElementById('groupedMoviesContainer');
            if (!container) return;

            let html = '';

            if (Object.keys(groupedMovies).length > 0) {
                html += '<h3 style="color: #fff; margin-bottom: 20px; font-size: 1.5rem;">Agrupaciones Detectadas</h3>';
                html += '<div class="sagas-admin-groups">';

                Object.keys(groupedMovies).sort().forEach(baseName => {
                    const movies = groupedMovies[baseName];
                    const defaultTitle = 'SAGA ' + baseName.toUpperCase();
                    const posters = movies.slice(0, 6).map(m => m.poster || '').filter(p => p);

                    const itemsWithType = movies.map(m => ({...m, type: 'movie'}));
                    html += `
                        <div class="sagas-admin-group-card" onclick="window.SagasAdminModal.open('${baseName}', ${JSON.stringify(itemsWithType).replace(/"/g, '&quot;')})">
                            <div class="sagas-admin-group-card-header">
                                <h3 class="sagas-admin-group-card-title">${window.SagasAdminUtils.escapeHtml(defaultTitle)}</h3>
                                <p class="sagas-admin-group-card-count">${movies.length} películas</p>
                            </div>
                            <div class="sagas-admin-group-card-posters">
                                ${posters.length > 0 ? posters.map(poster => `
                                    <img src="${window.SagasAdminUtils.escapeHtml(poster)}" alt="" class="sagas-admin-group-poster" onerror="this.style.display='none'">
                                `).join('') : '<div class="sagas-admin-group-poster-placeholder">Sin imágenes</div>'}
                            </div>
                        </div>
                    `;
                });

                html += '</div>';
            }

            if (ungroupedMovies.length > 0) {
                html += '<h3 style="color: #fff; margin: 40px 0 20px 0; font-size: 1.5rem;">Películas No Agrupadas</h3>';
                html += '<div class="sagas-admin-ungrouped-grid">';

                ungroupedMovies.forEach(movie => {
                    html += `
                        <div class="sagas-admin-ungrouped-item" data-movie-id="${movie.id}">
                            <input type="checkbox" class="ungrouped-checkbox" data-movie='${JSON.stringify(movie).replace(/"/g, '&quot;')}'>
                            ${movie.poster ? `
                                <img src="${window.SagasAdminUtils.escapeHtml(movie.poster)}" alt="${window.SagasAdminUtils.escapeHtml(movie.name)}" class="sagas-admin-ungrouped-poster" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="sagas-admin-ungrouped-poster-placeholder" style="display: none;">${window.SagasAdminUtils.escapeHtml(movie.name)}</div>
                            ` : `
                                <div class="sagas-admin-ungrouped-poster-placeholder">${window.SagasAdminUtils.escapeHtml(movie.name)}</div>
                            `}
                            <div class="sagas-admin-ungrouped-title">${window.SagasAdminUtils.escapeHtml(movie.name)}</div>
                        </div>
                    `;
                });

                html += '</div>';
                html += '<div class="sagas-admin-ungrouped-actions">';
                html += '<button class="sagas-admin-btn" onclick="window.SagasAdminCollection.createFromSelected()">';
                html += '<i class="fas fa-plus"></i> Crear Saga con Seleccionadas';
                html += '</button>';
                html += '</div>';
            }

            if (html === '') {
                html = '<div class="sagas-admin-message">No se encontraron agrupaciones</div>';
            }

            container.innerHTML = html;
        },

        createFromSelected: function() {
            const checkboxes = document.querySelectorAll('.ungrouped-checkbox:checked');
            if (checkboxes.length === 0) {
                alert('Por favor selecciona al menos una película');
                return;
            }

            const selectedMovies = Array.from(checkboxes).map(cb => {
                const movie = JSON.parse(cb.getAttribute('data-movie').replace(/&quot;/g, '"'));
                return {...movie, type: 'movie'};
            });

            if (typeof window.SagasAdminModal.open === 'function') {
                window.SagasAdminModal.open('Nueva Saga', selectedMovies);
            }
        }
    };
})();

