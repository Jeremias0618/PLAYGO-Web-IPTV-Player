(function() {
    'use strict';

    window.SagasAdminPosters = {
        loadItems: function(items) {
            if (!items || items.length === 0) {
                if (typeof window.SagasAdminItems.updateList === 'function') {
                    window.SagasAdminItems.updateList();
                }
                return;
            }

            const itemsToLoad = items.filter(item => !item.poster || item.poster === '');
            if (itemsToLoad.length === 0) {
                if (typeof window.SagasAdminItems.updateList === 'function') {
                    window.SagasAdminItems.updateList();
                }
                return;
            }

            const loadPostersFromCache = () => {
                itemsToLoad.forEach(item => {
                    let poster = '';

                    if (item.type === 'movie' && window.SagasAdminState.allMoviesCache) {
                        const cachedMovie = window.SagasAdminState.allMoviesCache.find(m => 
                            String(m.id) === String(item.id)
                        );
                        if (cachedMovie && cachedMovie.poster) {
                            poster = cachedMovie.poster;
                        }
                    } else if (item.type === 'series' && window.SagasAdminState.allSeriesCache) {
                        const cachedSeries = window.SagasAdminState.allSeriesCache.find(s => 
                            String(s.id) === String(item.id)
                        );
                        if (cachedSeries && cachedSeries.poster) {
                            poster = cachedSeries.poster;
                        }
                    }

                    if (poster) {
                        const sagaItem = window.SagasAdminState.currentSagaItems.find(i => 
                            String(i.id) === String(item.id) && i.type === item.type
                        );
                        if (sagaItem) {
                            sagaItem.poster = poster;
                        }
                    }
                });

                if (typeof window.SagasAdminItems.updateList === 'function') {
                    window.SagasAdminItems.updateList();
                }
            };

            const needsMovies = itemsToLoad.some(item => item.type === 'movie');
            const needsSeries = itemsToLoad.some(item => item.type === 'series');

            const promises = [];

            if (needsMovies && !window.SagasAdminState.allMoviesCache) {
                promises.push(
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
                        }
                    })
                    .catch(() => {})
                );
            }

            if (needsSeries && !window.SagasAdminState.allSeriesCache) {
                promises.push(
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
                        }
                    })
                    .catch(() => {})
                );
            }

            if (promises.length > 0) {
                Promise.all(promises).then(() => {
                    loadPostersFromCache();
                });
            } else {
                loadPostersFromCache();
            }
        },

        loadMissing: function() {
            return new Promise((resolve) => {
                const itemsNeedingPosters = window.SagasAdminState.currentSagaItems.filter(item => 
                    !item.poster || item.poster === ''
                );

                if (itemsNeedingPosters.length === 0) {
                    resolve();
                    return;
                }

                const needsMovies = itemsNeedingPosters.some(item => item.type === 'movie');
                const needsSeries = itemsNeedingPosters.some(item => item.type === 'series');

                const promises = [];

                if (needsMovies && !window.SagasAdminState.allMoviesCache) {
                    promises.push(
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
                            }
                        })
                        .catch(() => {})
                    );
                }

                if (needsSeries && !window.SagasAdminState.allSeriesCache) {
                    promises.push(
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
                            }
                        })
                        .catch(() => {})
                    );
                }

                if (promises.length > 0) {
                    Promise.all(promises).then(() => {
                        itemsNeedingPosters.forEach(item => {
                            let poster = '';

                            if (item.type === 'movie' && window.SagasAdminState.allMoviesCache) {
                                const cachedMovie = window.SagasAdminState.allMoviesCache.find(m => 
                                    String(m.id) === String(item.id)
                                );
                                if (cachedMovie && cachedMovie.poster) {
                                    poster = cachedMovie.poster;
                                }
                            } else if (item.type === 'series' && window.SagasAdminState.allSeriesCache) {
                                const cachedSeries = window.SagasAdminState.allSeriesCache.find(s => 
                                    String(s.id) === String(item.id)
                                );
                                if (cachedSeries && cachedSeries.poster) {
                                    poster = cachedSeries.poster;
                                }
                            }

                            if (poster) {
                                const sagaItem = window.SagasAdminState.currentSagaItems.find(i => 
                                    String(i.id) === String(item.id) && i.type === item.type
                                );
                                if (sagaItem) {
                                    sagaItem.poster = poster;
                                }
                            }
                        });
                        resolve();
                    });
                } else {
                    itemsNeedingPosters.forEach(item => {
                        let poster = '';

                        if (item.type === 'movie' && window.SagasAdminState.allMoviesCache) {
                            const cachedMovie = window.SagasAdminState.allMoviesCache.find(m => 
                                String(m.id) === String(item.id)
                            );
                            if (cachedMovie && cachedMovie.poster) {
                                poster = cachedMovie.poster;
                            }
                        } else if (item.type === 'series' && window.SagasAdminState.allSeriesCache) {
                            const cachedSeries = window.SagasAdminState.allSeriesCache.find(s => 
                                String(s.id) === String(item.id)
                            );
                            if (cachedSeries && cachedSeries.poster) {
                                poster = cachedSeries.poster;
                            }
                        }

                        if (poster) {
                            const sagaItem = window.SagasAdminState.currentSagaItems.find(i => 
                                String(i.id) === String(item.id) && i.type === item.type
                            );
                            if (sagaItem) {
                                sagaItem.poster = poster;
                            }
                        }
                    });
                    resolve();
                }
            });
        }
    };
})();

