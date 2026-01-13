(function() {
    'use strict';

    function openSagaModal(baseName, items) {
        window.currentSagaItems = JSON.parse(JSON.stringify(items || []));
        window.currentSagaBaseName = baseName;
        window.allMoviesCache = window.allMoviesCache || [];
        window.allSeriesCache = window.allSeriesCache || [];
        window.currentSearchTab = 'movies';
        
        const modal = document.getElementById('sagaModal');
        if (!modal) {
            return;
        }
        
        updateItemsList();
        switchSearchTab('movies');
        
        const titleInput = document.getElementById('sagaTitle');
        if (titleInput) {
            titleInput.value = baseName === 'Nueva Saga' ? '' : 'SAGA ' + baseName.toUpperCase();
        }
        
        const imagePreview = document.getElementById('sagaImagePreview');
        if (imagePreview) {
            imagePreview.style.display = 'none';
            imagePreview.src = '';
        }
        
        const fileInput = document.getElementById('sagaImageFile');
        if (fileInput) {
            fileInput.value = '';
        }
        
        const searchMoviesInput = document.getElementById('sagaSearchMovies');
        if (searchMoviesInput) {
            searchMoviesInput.value = '';
            searchMoviesInput.removeEventListener('input', handleMoviesSearch);
            searchMoviesInput.addEventListener('input', handleMoviesSearch);
            updateSearchResults('', 'movies');
        }
        
        const searchSeriesInput = document.getElementById('sagaSearchSeries');
        if (searchSeriesInput) {
            searchSeriesInput.value = '';
            searchSeriesInput.removeEventListener('input', handleSeriesSearch);
            searchSeriesInput.addEventListener('input', handleSeriesSearch);
            updateSearchResults('', 'series');
        }
        
        initDropzone();
        
        const dropzoneContent = document.querySelector('.saga-dropzone-content');
        if (dropzoneContent) {
            dropzoneContent.style.display = 'flex';
        }
        
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
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
                    
                    if (backdrop.parentElement === document.body && modal.parentElement === document.body) {
                        const backdropIndex = Array.from(document.body.children).indexOf(backdrop);
                        const modalIndex = Array.from(document.body.children).indexOf(modal);
                        if (backdropIndex > modalIndex) {
                            document.body.insertBefore(backdrop, modal);
                        }
                    }
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
                
                const titleInput = document.getElementById('sagaTitle');
                if (titleInput) {
                    setTimeout(() => {
                        titleInput.focus();
                    }, 100);
                }
                
                initSortable();
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
                    bsModal.hide();
                }
            });
            
            try {
                bsModal.show();
                
                setTimeout(() => {
                    const backdrop = document.querySelector('.modal-backdrop');
                    const modalDialog = modal.querySelector('.modal-dialog');
                    const modalContent = modal.querySelector('.saga-modal-content');
                    
                    if (modalDialog && window.getComputedStyle(modalDialog).zIndex !== '10002') {
                        modalDialog.style.zIndex = '10002';
                    }
                    
                    if (modalContent && window.getComputedStyle(modalContent).zIndex !== '10003') {
                        modalContent.style.zIndex = '10003';
                    }
                    
                    if (backdrop && window.getComputedStyle(backdrop).zIndex !== '9999') {
                        backdrop.style.zIndex = '9999';
                    }
                    
                    if (modal.parentElement !== document.body) {
                        document.body.appendChild(modal);
                    }
                    
                    if (backdrop && backdrop.parentElement === document.body && modal.parentElement === document.body) {
                        const newBackdropIndex = Array.from(document.body.children).indexOf(backdrop);
                        const newModalIndex = Array.from(document.body.children).indexOf(modal);
                        if (newBackdropIndex > newModalIndex) {
                            document.body.insertBefore(backdrop, modal);
                        }
                    }
                }, 500);
            } catch (error) {
                // Silent error handling
            }
        } else {
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'sagaModalBackdrop';
            backdrop.style.pointerEvents = 'auto';
            backdrop.addEventListener('click', function(e) {
                e.stopPropagation();
                closeSagaModal();
            });
            document.body.appendChild(backdrop);
            modal.style.display = 'block';
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }
        
        if (window.allMoviesCache.length === 0) {
            loadAllMovies();
        }
        if (window.allSeriesCache.length === 0) {
            loadAllSeries();
        }
    }
    
    function initSortable() {
        const list = document.getElementById('sagaMoviesList');
        if (!list) return;
        
        let draggedElement = null;
        let placeholder = null;
        
        const items = Array.from(list.children).filter(item => !item.classList.contains('saga-empty-message'));
        
        items.forEach(item => {
            if (item.classList.contains('sortable-placeholder')) return;
            
            item.setAttribute('draggable', 'true');
            
            item.addEventListener('dragstart', function(e) {
                draggedElement = this;
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', '');
                
                this.classList.add('dragging');
                this.style.opacity = '0.5';
                
                placeholder = document.createElement('div');
                placeholder.className = 'saga-modal-movie-card sortable-placeholder';
                placeholder.style.height = this.offsetHeight + 'px';
                placeholder.innerHTML = '<div style="padding: 20px; text-align: center; color: rgba(255,255,255,0.5);">Soltar aquí</div>';
                
                list.insertBefore(placeholder, this.nextSibling);
            });
            
            item.addEventListener('dragend', function(e) {
                this.classList.remove('dragging');
                this.style.opacity = '1';
                if (placeholder && placeholder.parentNode) {
                    placeholder.parentNode.removeChild(placeholder);
                }
                draggedElement = null;
                placeholder = null;
            });
            
            item.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                
                if (this !== draggedElement && this !== placeholder && draggedElement !== null) {
                    const rect = this.getBoundingClientRect();
                    const next = (e.clientY - rect.top) / (rect.bottom - rect.top) > 0.5;
                    
                    if (next && this.nextSibling !== placeholder) {
                        list.insertBefore(placeholder, this.nextSibling);
                    } else if (!next && this.previousSibling !== placeholder) {
                        list.insertBefore(placeholder, this);
                    }
                }
            });
            
            item.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (draggedElement !== this && draggedElement !== null && placeholder) {
                    const placeholderIndex = Array.from(list.children).indexOf(placeholder);
                    
                    if (placeholderIndex !== -1) {
                        list.insertBefore(draggedElement, placeholder);
                        list.removeChild(placeholder);
                        
                        updateItemsOrder();
                        initSortable();
                    }
                }
                
                return false;
            });
        });
    }
    
    function updateItemsOrder() {
        const list = document.getElementById('sagaMoviesList');
        if (!list || !window.currentSagaItems) return;
        
        const newOrder = [];
        const items = Array.from(list.children).filter(item => 
            !item.classList.contains('sortable-placeholder') && 
            !item.classList.contains('saga-empty-message')
        );
        
        items.forEach((item, index) => {
            const itemId = item.getAttribute('data-item-id');
            const itemType = item.getAttribute('data-item-type');
            const found = window.currentSagaItems.find(i => String(i.id) === String(itemId) && i.type === itemType);
            if (found) {
                newOrder.push(found);
                const orderNumber = item.querySelector('.order-number');
                if (orderNumber) {
                    orderNumber.textContent = index + 1;
                }
            }
        });
        
        window.currentSagaItems = newOrder;
    }
    
    function updateItemsList() {
        const itemsList = document.getElementById('sagaMoviesList');
        if (!itemsList) return;
        
        if (!window.currentSagaItems || window.currentSagaItems.length === 0) {
            itemsList.innerHTML = '<div class="saga-empty-message">No hay contenido en esta saga</div>';
            return;
        }
        
        itemsList.innerHTML = window.currentSagaItems.map((item, index) => {
            const typeIcon = item.type === 'series' ? '<i class="fas fa-tv"></i>' : '<i class="fas fa-film"></i>';
            const typeLabel = item.type === 'series' ? 'Serie' : 'Película';
            
            return `
                <div class="saga-modal-movie-card" data-item-id="${item.id}" data-item-type="${item.type}" draggable="true">
                    <div class="saga-modal-movie-order">
                        <span class="order-number">${index + 1}</span>
                    </div>
                    ${item.poster ? `
                        <img src="${escapeHtml(item.poster)}" alt="${escapeHtml(item.name)}" class="saga-modal-movie-poster" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="saga-modal-movie-poster-placeholder" style="display: none;">Sin imagen</div>
                    ` : `
                        <div class="saga-modal-movie-poster-placeholder">Sin imagen</div>
                    `}
                    <div class="saga-modal-movie-info">
                        <div class="saga-modal-movie-type">${typeIcon} ${typeLabel}</div>
                        <div class="saga-modal-movie-name">${escapeHtml(item.name)}</div>
                        <div class="saga-modal-movie-id">ID: ${item.id}</div>
                    </div>
                    <button class="saga-modal-remove-btn" onclick="removeItemFromSaga(${index})" title="Quitar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
        }).join('');
    }
    
    function removeItemFromSaga(index) {
        if (window.currentSagaItems && window.currentSagaItems.length > index) {
            window.currentSagaItems.splice(index, 1);
            updateItemsList();
            initSortable();
            const activeTab = window.currentSearchTab || 'movies';
            const searchInput = activeTab === 'movies' ? 
                document.getElementById('sagaSearchMovies') : 
                document.getElementById('sagaSearchSeries');
            if (searchInput) {
                updateSearchResults(searchInput.value, activeTab);
            }
        }
    }
    
    function switchSearchTab(tab) {
        window.currentSearchTab = tab;
        
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
    }
    
    function initDropzone() {
        const dropzone = document.getElementById('sagaDropzone');
        const fileInput = document.getElementById('sagaImageFile');
        const preview = document.getElementById('sagaImagePreview');
        
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
                previewImage(fileInput);
            }
        });
        
        fileInput.addEventListener('change', function() {
            previewImage(this);
        });
    }
    
    function handleMoviesSearch(e) {
        updateSearchResults(e.target.value, 'movies');
    }
    
    function handleSeriesSearch(e) {
        updateSearchResults(e.target.value, 'series');
    }
    
    function loadAllMovies() {
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
                window.allMoviesCache = data.movies.map(m => ({...m, type: 'movie'}));
                const searchInput = document.getElementById('sagaSearchMovies');
                if (searchInput) {
                    updateSearchResults(searchInput.value, 'movies');
                }
            }
        })
        .catch(error => {
            // Silent error handling
        });
    }
    
    function loadAllSeries() {
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
                window.allSeriesCache = data.series.map(s => ({...s, type: 'series'}));
                const searchInput = document.getElementById('sagaSearchSeries');
                if (searchInput) {
                    updateSearchResults(searchInput.value, 'series');
                }
            }
        })
        .catch(error => {
            // Silent error handling
        });
    }
    
    function updateSearchResults(query, type) {
        const isMovies = type === 'movies';
        const resultsContainer = document.getElementById(isMovies ? 'sagaSearchResults' : 'sagaSearchSeriesResults');
        if (!resultsContainer) return;
        
        const cache = isMovies ? window.allMoviesCache : window.allSeriesCache;
        
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
            return window.currentSagaItems && window.currentSagaItems.some(i => String(i.id) === String(item.id) && i.type === item.type);
        };
        
        resultsContainer.innerHTML = filtered.map(item => {
            const typeIcon = item.type === 'series' ? '<i class="fas fa-tv"></i>' : '<i class="fas fa-film"></i>';
            const added = isAlreadyAdded(item);
            const btnClass = added ? 'saga-search-add-btn added' : 'saga-search-add-btn';
            const btnText = added ? '<i class="fas fa-check"></i> Añadido' : '<i class="fas fa-plus"></i> Añadir';
            
            return `
                <div class="saga-search-result-item" onclick="${!added ? `addItemToSaga(${JSON.stringify(item).replace(/"/g, '&quot;')})` : ''}">
                    ${item.poster ? `
                        <img src="${escapeHtml(item.poster)}" alt="${escapeHtml(item.name)}" class="saga-search-result-poster" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="saga-search-result-poster-placeholder" style="display: none;">Sin imagen</div>
                    ` : `
                        <div class="saga-search-result-poster-placeholder">Sin imagen</div>
                    `}
                    <div class="saga-search-result-info">
                        <div class="saga-search-result-type">${typeIcon} ${item.type === 'series' ? 'Serie' : 'Película'}</div>
                        <div class="saga-search-result-name">${escapeHtml(item.name)}</div>
                        <div class="saga-search-result-id">ID: ${item.id}</div>
                    </div>
                    <button class="${btnClass}" onclick="event.stopPropagation(); ${!added ? `addItemToSaga(${JSON.stringify(item).replace(/"/g, '&quot;')})` : ''}">
                        ${btnText}
                    </button>
                </div>
            `;
        }).join('');
    }
    
    function addItemToSaga(itemJson) {
        const item = typeof itemJson === 'string' ? JSON.parse(itemJson.replace(/&quot;/g, '"')) : itemJson;
        if (!window.currentSagaItems) {
            window.currentSagaItems = [];
        }
        
        const exists = window.currentSagaItems.some(i => String(i.id) === String(item.id) && i.type === item.type);
        if (!exists) {
            window.currentSagaItems.push(item);
            updateItemsList();
            initSortable();
            
            const activeTab = window.currentSearchTab || 'movies';
            const searchInput = activeTab === 'movies' ? 
                document.getElementById('sagaSearchMovies') : 
                document.getElementById('sagaSearchSeries');
            if (searchInput) {
                updateSearchResults(searchInput.value, activeTab);
            }
        }
    }
    
    function previewImage(input) {
        if (input && input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('sagaImagePreview');
                const dropzoneContent = document.querySelector('.saga-dropzone-content');
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
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function closeSagaModal() {
        const modal = document.getElementById('sagaModal');
        if (!modal) {
            return;
        }
        
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            } else {
                modal.classList.remove('show');
                modal.setAttribute('aria-hidden', 'true');
                modal.setAttribute('aria-modal', 'false');
                modal.style.display = 'none';
                document.body.style.overflow = '';
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
            }
        } else {
            modal.style.display = 'none';
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
            modal.setAttribute('aria-modal', 'false');
            const backdrop = document.getElementById('sagaModalBackdrop');
            if (backdrop) {
                backdrop.remove();
            }
            document.body.style.overflow = '';
        }
    }

    function saveSaga() {
        const title = document.getElementById('sagaTitle')?.value.trim();
        const imageFile = document.getElementById('sagaImageFile')?.files[0];
        
        if (!title) {
            alert('Por favor ingresa un título para la saga');
            return;
        }
        
        if (!window.currentSagaItems || window.currentSagaItems.length === 0) {
            alert('No hay contenido seleccionado');
            return;
        }
        
        const saveBtn = document.getElementById('saveSagaBtn');
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        }
        
        let imageUrl = '';
        
        if (imageFile) {
            const formData = new FormData();
            formData.append('action', 'upload_image');
            formData.append('image', imageFile);
            formData.append('saga_title', title);
            
            fetch('libs/endpoints/SagasAdmin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    imageUrl = data.image;
                    saveSagaData(title, imageUrl);
                } else {
                    alert('Error al subir imagen: ' + (data.error || 'Error desconocido'));
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Saga';
                    }
                }
            })
            .catch(error => {
                alert('Error al subir imagen');
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Saga';
                }
            });
        } else {
            saveSagaData(title, imageUrl);
        }
    }

    function saveSagaData(title, imageUrl) {
        const formData = new FormData();
        formData.append('action', 'save_saga');
        formData.append('title', title);
        formData.append('items', JSON.stringify(window.currentSagaItems));
        formData.append('image', imageUrl);
        
        fetch('libs/endpoints/SagasAdmin.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Saga guardada exitosamente');
                closeSagaModal();
                if (typeof window.loadSagas === 'function') {
                    window.loadSagas();
                }
            } else {
                alert('Error al guardar saga: ' + (data.error || 'Error desconocido'));
            }
            
            const saveBtn = document.getElementById('saveSagaBtn');
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Saga';
            }
        })
        .catch(error => {
            alert('Error al guardar saga');
            const saveBtn = document.getElementById('saveSagaBtn');
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Saga';
            }
        });
    }

    window.openSagaModal = openSagaModal;
    window.saveSaga = saveSaga;
    window.closeSagaModal = closeSagaModal;
    window.deleteSaga = typeof window.deleteSaga !== 'undefined' ? window.deleteSaga : function() {};
    window.previewImage = previewImage;
    window.loadSagas = typeof window.loadSagas !== 'undefined' ? window.loadSagas : function() {};
    window.removeItemFromSaga = removeItemFromSaga;
    window.addItemToSaga = addItemToSaga;
    window.switchSearchTab = switchSearchTab;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('sagaImageFile');
            if (imageInput) {
                imageInput.addEventListener('change', function() {
                    previewImage(this);
                });
            }
            
            const saveBtn = document.getElementById('saveSagaBtn');
            if (saveBtn) {
                saveBtn.addEventListener('click', saveSaga);
            }
        });
    } else {
        const imageInput = document.getElementById('sagaImageFile');
        if (imageInput) {
            imageInput.addEventListener('change', function() {
                previewImage(this);
            });
        }
        
        const saveBtn = document.getElementById('saveSagaBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', saveSaga);
        }
    }
})();
