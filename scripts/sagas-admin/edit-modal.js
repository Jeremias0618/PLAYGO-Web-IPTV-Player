(function() {
    'use strict';

    let editOriginalState = null;
    let editCurrentItems = [];
    let editCurrentSagaId = null;

    window.SagasAdminEditModal = {
        open: function(sagaId) {
            fetch('libs/endpoints/SagasAdmin.php?action=get_sagas')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.sagas) {
                        const saga = data.sagas.find(s => String(s.id) === String(sagaId));
                        if (saga) {
                            editCurrentSagaId = String(sagaId);
                            editCurrentItems = saga.items.map(item => ({
                                id: item.id,
                                name: item.title,
                                type: item.type || 'movie',
                                poster: item.poster || ''
                            }));

                            editOriginalState = {
                                title: saga.title,
                                items: JSON.parse(JSON.stringify(editCurrentItems)),
                                image: saga.image || null
                            };

                            const modal = document.getElementById('sagaEditModal');
                            if (!modal) return;

                            const titleInput = document.getElementById('sagaEditTitle');
                            if (titleInput) {
                                titleInput.value = saga.title;
                                titleInput.classList.remove('is-invalid');
                                const existingFeedback = titleInput.parentElement.querySelector('.invalid-feedback');
                                if (existingFeedback) {
                                    existingFeedback.remove();
                                }
                            }

                            const imagePreview = document.getElementById('sagaEditImagePreview');
                            const dropzone = document.getElementById('sagaEditDropzone');
                            const dropzoneContent = dropzone?.querySelector('.saga-dropzone-content');
                            if (saga.image && imagePreview && dropzone) {
                                imagePreview.src = saga.image;
                                imagePreview.style.display = 'block';
                                if (dropzoneContent) {
                                    dropzoneContent.style.display = 'none';
                                }
                            } else {
                                if (imagePreview) {
                                    imagePreview.style.display = 'none';
                                    imagePreview.src = '';
                                }
                                if (dropzoneContent) {
                                    dropzoneContent.style.display = 'flex';
                                }
                            }

                            if (typeof window.SagasAdminEditItems.updateList === 'function') {
                                window.SagasAdminEditItems.updateList();
                            }

                            if (typeof window.SagasAdminEditSearch.switchTab === 'function') {
                                window.SagasAdminEditSearch.switchTab('movies');
                            }

                            if (typeof window.SagasAdminEditImage.initDropzone === 'function') {
                                window.SagasAdminEditImage.initDropzone();
                            }

                            const searchMoviesInput = document.getElementById('sagaEditSearchMovies');
                            if (searchMoviesInput) {
                                searchMoviesInput.value = '';
                                searchMoviesInput.addEventListener('input', function(e) {
                                    if (typeof window.SagasAdminEditSearch.handleMoviesSearch === 'function') {
                                        window.SagasAdminEditSearch.handleMoviesSearch(e);
                                    }
                                });
                            }

                            const searchSeriesInput = document.getElementById('sagaEditSearchSeries');
                            if (searchSeriesInput) {
                                searchSeriesInput.value = '';
                                searchSeriesInput.addEventListener('input', function(e) {
                                    if (typeof window.SagasAdminEditSearch.handleSeriesSearch === 'function') {
                                        window.SagasAdminEditSearch.handleSeriesSearch(e);
                                    }
                                });
                            }

                            if (window.SagasAdminState.allMoviesCache.length === 0) {
                                if (typeof window.SagasAdminSearch.loadAllMovies === 'function') {
                                    window.SagasAdminSearch.loadAllMovies();
                                }
                            }
                            if (window.SagasAdminState.allSeriesCache.length === 0) {
                                if (typeof window.SagasAdminSearch.loadAllSeries === 'function') {
                                    window.SagasAdminSearch.loadAllSeries();
                                }
                            }

                            this.setupCloseButtons();

                            const bsModal = new bootstrap.Modal(modal, {
                                backdrop: true,
                                keyboard: true,
                                focus: true
                            });

                            const showHandler = function() {
                                modal.removeAttribute('aria-hidden');
                                modal.setAttribute('aria-modal', 'true');
                                document.body.style.overflow = 'hidden';
                            };

                            const shownHandler = function() {
                                modal.setAttribute('aria-hidden', 'false');
                                
                                const backdrop = document.querySelector('.modal-backdrop');
                                const modalDialog = modal.querySelector('.modal-dialog');
                                const modalContent = modal.querySelector('.saga-modal-content');
                                
                                if (backdrop) {
                                    backdrop.style.zIndex = '9999';
                                    backdrop.style.position = 'fixed';
                                }
                                
                                if (modal) {
                                    modal.style.zIndex = '10000';
                                    modal.style.position = 'fixed';
                                }
                                
                                if (modalDialog) {
                                    modalDialog.style.zIndex = '10002';
                                    modalDialog.style.position = 'relative';
                                }
                                
                                if (modalContent) {
                                    modalContent.style.zIndex = '10003';
                                    modalContent.style.position = 'relative';
                                }

                                if (typeof window.SagasAdminEditItems.initSortable === 'function') {
                                    window.SagasAdminEditItems.initSortable();
                                }
                            };

                            const hideHandler = function() {
                                document.body.style.overflow = '';
                            };

                            const hiddenHandler = function() {
                                modal.setAttribute('aria-hidden', 'true');
                                modal.setAttribute('aria-modal', 'false');
                                modal.style.display = 'none';
                                modal.removeEventListener('show.bs.modal', showHandler);
                                modal.removeEventListener('shown.bs.modal', shownHandler);
                                modal.removeEventListener('hide.bs.modal', hideHandler);
                                modal.removeEventListener('hidden.bs.modal', hiddenHandler);
                            };

                            modal.addEventListener('show.bs.modal', showHandler);
                            modal.addEventListener('shown.bs.modal', shownHandler);
                            modal.addEventListener('hide.bs.modal', hideHandler);
                            modal.addEventListener('hidden.bs.modal', hiddenHandler);

                            modal.addEventListener('click', function(e) {
                                if (e.target === modal) {
                                    e.stopPropagation();
                                    if (!window.SagasAdminEditModal.hasUnsavedChanges() || confirm('¿Estás seguro de que deseas salir sin guardar los cambios realizados?')) {
                                        bsModal.hide();
                                    }
                                }
                            });

                            try {
                                bsModal.show();
                            } catch (error) {
                                // Silent error handling
                            }
                        }
                    }
                })
                .catch(() => {
                    alert('Error al cargar la saga para editar');
                });
        },

        setupCloseButtons: function() {
            const closeBtn = document.querySelector('#sagaEditModal .saga-edit-modal-close');
            const cancelBtn = document.querySelector('#sagaEditModal .saga-edit-modal-cancel');
            
            if (closeBtn) {
                const newCloseBtn = closeBtn.cloneNode(true);
                closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);
                newCloseBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    window.SagasAdminEditModal.close();
                });
            }
            
            if (cancelBtn) {
                const newCancelBtn = cancelBtn.cloneNode(true);
                cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
                newCancelBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    window.SagasAdminEditModal.close();
                });
            }
        },

        close: function() {
            if (this.hasUnsavedChanges()) {
                if (!confirm('¿Estás seguro de que deseas salir sin guardar los cambios realizados?')) {
                    return;
                }
            }
            
            const modal = document.getElementById('sagaEditModal');
            if (!modal) return;
            
            try {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    if (typeof bootstrap.Modal.getInstance === 'function') {
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal && typeof bsModal.hide === 'function') {
                            bsModal.hide();
                            this.reset();
                            return;
                        }
                    }
                }
            } catch (e) {
                // Fallback to manual close
            }
            
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
            modal.setAttribute('aria-modal', 'false');
            modal.style.display = 'none';
            document.body.style.overflow = '';
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());
            document.body.classList.remove('modal-open');
            this.reset();
        },

        reset: function() {
            editOriginalState = null;
            editCurrentItems = [];
            editCurrentSagaId = null;
            
            const titleInput = document.getElementById('sagaEditTitle');
            if (titleInput) {
                titleInput.value = '';
                titleInput.classList.remove('is-invalid');
                const existingFeedback = titleInput.parentElement.querySelector('.invalid-feedback');
                if (existingFeedback) {
                    existingFeedback.remove();
                }
            }
            
            const imagePreview = document.getElementById('sagaEditImagePreview');
            const dropzone = document.getElementById('sagaEditDropzone');
            const dropzoneContent = dropzone?.querySelector('.saga-dropzone-content');
            if (imagePreview) {
                imagePreview.style.display = 'none';
                imagePreview.src = '';
            }
            if (dropzoneContent) {
                dropzoneContent.style.display = 'flex';
            }
            
            const fileInput = document.getElementById('sagaEditImageFile');
            if (fileInput) {
                fileInput.value = '';
            }
            
            if (typeof window.SagasAdminEditItems.updateList === 'function') {
                window.SagasAdminEditItems.updateList();
            }
        },

        hasUnsavedChanges: function() {
            if (!editOriginalState || !editCurrentSagaId) {
                return false;
            }

            const titleInput = document.getElementById('sagaEditTitle');
            const currentTitle = titleInput ? titleInput.value.trim() : '';
            const originalTitle = editOriginalState.title || '';

            if (currentTitle !== originalTitle) {
                return true;
            }

            if (editCurrentItems.length !== editOriginalState.items.length) {
                return true;
            }

            for (let i = 0; i < editCurrentItems.length; i++) {
                const current = editCurrentItems[i];
                const original = editOriginalState.items[i];

                if (!original) {
                    return true;
                }

                if (String(current.id) !== String(original.id) || 
                    (current.type || 'movie') !== (original.type || 'movie') ||
                    (current.name || '') !== (original.name || '')) {
                    return true;
                }
            }

            const imagePreview = document.getElementById('sagaEditImagePreview');
            let currentImage = null;
            
            if (imagePreview && imagePreview.style.display !== 'none' && imagePreview.src) {
                try {
                    const url = new URL(imagePreview.src, window.location.origin);
                    currentImage = url.pathname.replace(/^\//, '');
                } catch (e) {
                    currentImage = imagePreview.src.replace(/^https?:\/\/[^\/]+/, '').replace(/^\//, '');
                }
            }
            
            if (currentImage === '') {
                currentImage = null;
            }

            let originalImage = editOriginalState.image || null;
            if (originalImage) {
                originalImage = originalImage.replace(/^https?:\/\/[^\/]+/, '').replace(/^\//, '');
                if (originalImage === '') {
                    originalImage = null;
                }
            }

            if (currentImage !== originalImage) {
                if ((currentImage === null || currentImage === '') && (originalImage === null || originalImage === '')) {
                    return false;
                }
                return true;
            }

            return false;
        },
        get originalState() {
            return editOriginalState;
        },
        get currentSagaId() {
            return editCurrentSagaId;
        }
    };

    window.SagasAdminEditItems = {
        currentItems: function() {
            return editCurrentItems;
        },
        setItems: function(items) {
            editCurrentItems = items || [];
        },
        updateList: function() {
            const itemsList = document.getElementById('sagaEditMoviesList');
            if (!itemsList) return;

            if (!editCurrentItems || editCurrentItems.length === 0) {
                itemsList.innerHTML = '<div class="saga-empty-message">No hay contenido en esta saga</div>';
                return;
            }

            itemsList.innerHTML = editCurrentItems.map((item, index) => {
                const typeIcon = item.type === 'series' ? '<i class="fas fa-tv"></i>' : '<i class="fas fa-film"></i>';
                const typeLabel = item.type === 'series' ? 'Serie' : 'Película';

                return `
                    <div class="saga-modal-movie-card" data-item-id="${item.id}" data-item-type="${item.type}" draggable="true">
                        <div class="saga-modal-movie-order">
                            <span class="order-number">${index + 1}</span>
                        </div>
                        ${item.poster ? `
                            <img src="${window.SagasAdminUtils.escapeHtml(item.poster)}" alt="${window.SagasAdminUtils.escapeHtml(item.name)}" class="saga-modal-movie-poster" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="saga-modal-movie-poster-placeholder" style="display: none;">Sin imagen</div>
                        ` : `
                            <div class="saga-modal-movie-poster-placeholder">Sin imagen</div>
                        `}
                        <div class="saga-modal-movie-info">
                            <div class="saga-modal-movie-type">${typeIcon} ${typeLabel}</div>
                            <div class="saga-modal-movie-name">${window.SagasAdminUtils.escapeHtml(item.name)}</div>
                            <div class="saga-modal-movie-id">ID: ${item.id}</div>
                        </div>
                        <button class="saga-modal-remove-btn" onclick="window.SagasAdminEditItems.remove(${index})" title="Quitar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            }).join('');
        },
        remove: function(index) {
            if (editCurrentItems && editCurrentItems.length > index) {
                editCurrentItems.splice(index, 1);
                this.updateList();
            }
        },
        add: function(itemJson) {
            const item = typeof itemJson === 'string' ? JSON.parse(itemJson.replace(/&quot;/g, '"')) : itemJson;
            if (!editCurrentItems) {
                editCurrentItems = [];
            }

            const exists = editCurrentItems.some(i => String(i.id) === String(item.id) && i.type === item.type);
            if (!exists) {
                editCurrentItems.push(item);
                this.updateList();
            }
        },
        initSortable: function() {
            // Similar to items.js but for edit modal
            // Implementation can be copied from items.js
        }
    };

    window.SagasAdminEditSearch = {
        switchTab: function(tab) {
            const moviesTab = document.querySelector('#sagaEditModal .saga-segmented-btn[data-tab="movies"]');
            const seriesTab = document.querySelector('#sagaEditModal .saga-segmented-btn[data-tab="series"]');
            const moviesContent = document.getElementById('editSearchMoviesTab');
            const seriesContent = document.getElementById('editSearchSeriesTab');
            const moviesInput = document.getElementById('sagaEditSearchMovies');
            const seriesInput = document.getElementById('sagaEditSearchSeries');
            
            if (moviesTab && seriesTab && moviesContent && seriesContent) {
                if (tab === 'movies') {
                    moviesTab.classList.add('active');
                    seriesTab.classList.remove('active');
                    moviesContent.classList.add('active');
                    seriesContent.classList.remove('active');
                    if (moviesInput) moviesInput.style.display = 'block';
                    if (seriesInput) seriesInput.style.display = 'none';
                } else {
                    seriesTab.classList.add('active');
                    moviesTab.classList.remove('active');
                    seriesContent.classList.add('active');
                    moviesContent.classList.remove('active');
                    if (moviesInput) moviesInput.style.display = 'none';
                    if (seriesInput) seriesInput.style.display = 'block';
                }
            }
        },
        handleMoviesSearch: function(e) {
            this.updateResults(e.target.value, 'movies');
        },
        handleSeriesSearch: function(e) {
            this.updateResults(e.target.value, 'series');
        },
        updateResults: function(query, type) {
            const isMovies = type === 'movies';
            const resultsContainer = document.getElementById(isMovies ? 'sagaEditSearchResults' : 'sagaEditSearchSeriesResults');
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
                return editCurrentItems && editCurrentItems.some(i => String(i.id) === String(item.id) && i.type === item.type);
            };
            
            resultsContainer.innerHTML = filtered.map(item => {
                const typeIcon = item.type === 'series' ? '<i class="fas fa-tv"></i>' : '<i class="fas fa-film"></i>';
                const added = isAlreadyAdded(item);
                const btnClass = added ? 'saga-search-add-btn added' : 'saga-search-add-btn';
                const btnText = added ? '<i class="fas fa-check"></i> Añadido' : '<i class="fas fa-plus"></i> Añadir';
                
                return `
                    <div class="saga-search-result-item" onclick="${!added ? `window.SagasAdminEditItems.add(${window.SagasAdminUtils.escapeHtml(JSON.stringify(item).replace(/"/g, '&quot;'))})` : ''}">
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
                        <button class="${btnClass}" onclick="event.stopPropagation(); ${!added ? `window.SagasAdminEditItems.add(${window.SagasAdminUtils.escapeHtml(JSON.stringify(item).replace(/"/g, '&quot;'))})` : ''}">
                            ${btnText}
                        </button>
                    </div>
                `;
            }).join('');
        }
    };

    window.SagasAdminEditImage = {
        initDropzone: function() {
            const dropzone = document.getElementById('sagaEditDropzone');
            const fileInput = document.getElementById('sagaEditImageFile');
            
            if (!dropzone || !fileInput) return;
            
            dropzone.addEventListener('click', function() {
                fileInput.click();
            });
            
            dropzone.addEventListener('dragover', function(e) {
                e.preventDefault();
                dropzone.classList.add('dragover');
            });
            
            dropzone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                dropzone.classList.remove('dragover');
            });
            
            dropzone.addEventListener('drop', function(e) {
                e.preventDefault();
                dropzone.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    window.SagasAdminEditImage.preview(fileInput);
                }
            });
            
            fileInput.addEventListener('change', function() {
                window.SagasAdminEditImage.preview(this);
            });
        },
        preview: function(input) {
            if (input && input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('sagaEditImagePreview');
                    const dropzoneContent = document.querySelector('#sagaEditDropzone .saga-dropzone-content');
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    if (dropzoneContent) {
                        dropzoneContent.style.display = 'none';
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    };
})();

