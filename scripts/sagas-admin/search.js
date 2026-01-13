(function() {
    'use strict';

    window.SagasAdminSearch = {
        loadAllMovies: function() {
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
                    window.SagasAdminState.allMoviesCache = data.movies.map(m => ({...m, type: 'movie'}));
                    const searchInput = document.getElementById('sagaSearchMovies');
                    if (searchInput) {
                        this.updateResults(searchInput.value, 'movies');
                    }
                }
            })
            .catch(() => {});
        },

        loadAllSeries: function() {
            fetch('libs/endpoints/SagasAdmin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=collect_series'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.SagasAdminState.allSeriesCache = data.series.map(s => ({...s, type: 'series'}));
                    const searchInput = document.getElementById('sagaSearchSeries');
                    if (searchInput) {
                        this.updateResults(searchInput.value, 'series');
                    }
                }
            })
            .catch(() => {});
        },

        updateResults: function(query, type) {
            const isMovies = type === 'movies';
            const resultsContainer = document.getElementById(isMovies ? 'sagaSearchResults' : 'sagaSearchSeriesResults');
            if (!resultsContainer) return;

            const cache = isMovies ? window.SagasAdminState.allMoviesCache : window.SagasAdminState.allSeriesCache;

            if (!cache || cache.length === 0) {
                resultsContainer.innerHTML = '<div class="saga-search-message">Cargando ' + (isMovies ? 'películas' : 'series') + '...</div>';
                return;
            }

            const queryLower = query.toLowerCase().trim();
            const filtered = cache.filter(item => {
                if (!item.name || !item.id) return false;
                return item.name.toLowerCase().includes(queryLower);
            }).slice(0, 20);

            if (filtered.length === 0) {
                resultsContainer.innerHTML = '<div class="saga-search-message">No se encontraron ' + (isMovies ? 'películas' : 'series') + '</div>';
                return;
            }

            const isAlreadyAdded = (item) => {
                return window.SagasAdminState.currentSagaItems && 
                    window.SagasAdminState.currentSagaItems.some(i => 
                        String(i.id) === String(item.id) && i.type === item.type
                    );
            };

            resultsContainer.innerHTML = filtered.map(item => {
                const typeIcon = item.type === 'series' ? '<i class="fas fa-tv"></i>' : '<i class="fas fa-film"></i>';
                const added = isAlreadyAdded(item);
                const btnClass = added ? 'saga-search-add-btn added' : 'saga-search-add-btn';
                const btnText = added ? '<i class="fas fa-check"></i> Añadido' : '<i class="fas fa-plus"></i> Añadir';

                return `
                    <div class="saga-search-result-item" onclick="${!added ? `window.SagasAdminItems.add(${JSON.stringify(item).replace(/"/g, '&quot;')})` : ''}">
                        ${item.poster ? `
                            <img src="${window.SagasAdminUtils.escapeHtml(item.poster)}" alt="${window.SagasAdminUtils.escapeHtml(item.name)}" class="saga-search-result-poster" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="saga-search-result-poster-placeholder" style="display: none;">Sin imagen</div>
                        ` : `
                            <div class="saga-search-result-poster-placeholder">Sin imagen</div>
                        `}
                        <div class="saga-search-result-info">
                            <div class="saga-search-result-type">${typeIcon} ${item.type === 'series' ? 'Serie' : 'Película'}</div>
                            <div class="saga-search-result-name">${window.SagasAdminUtils.escapeHtml(item.name)}</div>
                            <div class="saga-search-result-id">ID: ${item.id}</div>
                        </div>
                        <button class="${btnClass}" onclick="event.stopPropagation(); ${!added ? `window.SagasAdminItems.add(${JSON.stringify(item).replace(/"/g, '&quot;')})` : ''}">
                            ${btnText}
                        </button>
                    </div>
                `;
            }).join('');
        },

        switchTab: function(tab) {
            window.SagasAdminState.currentSearchTab = tab;

            const moviesTab = document.querySelector('.saga-segmented-btn[data-tab="movies"]');
            const seriesTab = document.querySelector('.saga-segmented-btn[data-tab="series"]');
            const moviesContent = document.getElementById('searchMoviesTab');
            const seriesContent = document.getElementById('searchSeriesTab');
            const moviesInput = document.getElementById('sagaSearchMovies');
            const seriesInput = document.getElementById('sagaSearchSeries');

            if (moviesTab && seriesTab && moviesContent && seriesContent) {
                if (tab === 'movies') {
                    moviesTab.classList.add('active');
                    seriesTab.classList.remove('active');
                    moviesContent.classList.add('active');
                    seriesContent.classList.remove('active');
                    if (moviesInput) moviesInput.style.display = 'block';
                    if (seriesInput) seriesInput.style.display = 'none';

                    if (moviesInput) {
                        setTimeout(() => moviesInput.focus(), 100);
                    }
                } else {
                    seriesTab.classList.add('active');
                    moviesTab.classList.remove('active');
                    seriesContent.classList.add('active');
                    moviesContent.classList.remove('active');
                    if (moviesInput) moviesInput.style.display = 'none';
                    if (seriesInput) seriesInput.style.display = 'block';

                    if (seriesInput) {
                        setTimeout(() => seriesInput.focus(), 100);
                    }
                }
            }
        },

        handleMoviesSearch: function(e) {
            window.SagasAdminSearch.updateResults(e.target.value, 'movies');
        },

        handleSeriesSearch: function(e) {
            window.SagasAdminSearch.updateResults(e.target.value, 'series');
        }
    };
})();

