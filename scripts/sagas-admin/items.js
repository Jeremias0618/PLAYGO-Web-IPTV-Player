(function() {
    'use strict';

    let draggedElement = null;
    let placeholder = null;
    let sortableInitialized = false;
    let listDropHandler = null;
    let listDragoverHandler = null;

    window.SagasAdminItems = {
        initSortable: function() {
            const list = document.getElementById('sagaMoviesList');
            if (!list) return;

            if (sortableInitialized) {
                const oldItems = Array.from(list.children).filter(item => 
                    !item.classList.contains('saga-empty-message') && 
                    !item.classList.contains('sortable-placeholder')
                );

                oldItems.forEach(item => {
                    const newItem = item.cloneNode(true);
                    item.parentNode.replaceChild(newItem, item);
                });

                if (listDropHandler) {
                    list.removeEventListener('drop', listDropHandler);
                }
                if (listDragoverHandler) {
                    list.removeEventListener('dragover', listDragoverHandler);
                }
            }

            const items = Array.from(list.children).filter(item => 
                !item.classList.contains('saga-empty-message') && 
                !item.classList.contains('sortable-placeholder')
            );

            items.forEach((item) => {
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

                    if (placeholder && placeholder.parentNode && draggedElement) {
                        const placeholderIndex = Array.from(list.children).indexOf(placeholder);
                        if (placeholderIndex !== -1) {
                            list.insertBefore(draggedElement, placeholder);
                            list.removeChild(placeholder);
                            window.SagasAdminItems.updateOrder();
                        }
                    } else if (placeholder && placeholder.parentNode) {
                        placeholder.parentNode.removeChild(placeholder);
                    }

                    draggedElement = null;
                    placeholder = null;
                });

                item.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';

                    if (this !== draggedElement && this !== placeholder && draggedElement !== null && placeholder) {
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

                    if (draggedElement !== this && draggedElement !== null && placeholder && placeholder.parentNode) {
                        const placeholderIndex = Array.from(list.children).indexOf(placeholder);
                        if (placeholderIndex !== -1) {
                            list.insertBefore(draggedElement, placeholder);
                            list.removeChild(placeholder);
                            window.SagasAdminItems.updateOrder();
                        }
                    }

                    return false;
                });
            });

            listDragoverHandler = function(e) {
                if (placeholder && draggedElement) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                }
            };

            listDropHandler = function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (draggedElement && placeholder && placeholder.parentNode) {
                    const placeholderIndex = Array.from(list.children).indexOf(placeholder);
                    if (placeholderIndex !== -1) {
                        list.insertBefore(draggedElement, placeholder);
                        list.removeChild(placeholder);
                        window.SagasAdminItems.updateOrder();
                    }
                }

                if (draggedElement) {
                    draggedElement.classList.remove('dragging');
                    draggedElement.style.opacity = '1';
                }

                draggedElement = null;
                placeholder = null;

                return false;
            };

            list.addEventListener('dragover', listDragoverHandler);
            list.addEventListener('drop', listDropHandler);

            sortableInitialized = true;
        },

        updateOrder: function() {
            const list = document.getElementById('sagaMoviesList');
            if (!list || !window.SagasAdminState.currentSagaItems) return;

            const newOrder = [];
            const items = Array.from(list.children).filter(item => 
                !item.classList.contains('sortable-placeholder') && 
                !item.classList.contains('saga-empty-message')
            );

            items.forEach((item, index) => {
                const itemId = item.getAttribute('data-item-id');
                const itemType = item.getAttribute('data-item-type');
                const found = window.SagasAdminState.currentSagaItems.find(i => 
                    String(i.id) === String(itemId) && i.type === itemType
                );
                if (found) {
                    newOrder.push(found);
                    const orderNumber = item.querySelector('.order-number');
                    if (orderNumber) {
                        orderNumber.textContent = index + 1;
                    }
                }
            });

            window.SagasAdminState.currentSagaItems = newOrder;
        },

        updateList: function() {
            const itemsList = document.getElementById('sagaMoviesList');
            if (!itemsList) return;

            if (!window.SagasAdminState.currentSagaItems || window.SagasAdminState.currentSagaItems.length === 0) {
                itemsList.innerHTML = '<div class="saga-empty-message">No hay contenido en esta saga</div>';
                sortableInitialized = false;
                return;
            }

            itemsList.innerHTML = window.SagasAdminState.currentSagaItems.map((item, index) => {
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
                        <button class="saga-modal-remove-btn" onclick="window.SagasAdminItems.remove(${index})" title="Quitar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            }).join('');

            sortableInitialized = false;
            setTimeout(() => {
                this.initSortable();
            }, 100);
        },

        remove: function(index) {
            if (window.SagasAdminState.currentSagaItems && window.SagasAdminState.currentSagaItems.length > index) {
                window.SagasAdminState.currentSagaItems.splice(index, 1);
                this.updateList();
                if (typeof window.SagasAdminValidation.updateSaveButtonState === 'function') {
                    window.SagasAdminValidation.updateSaveButtonState();
                }
                const activeTab = window.SagasAdminState.currentSearchTab || 'movies';
                const searchInput = activeTab === 'movies' ? 
                    document.getElementById('sagaSearchMovies') : 
                    document.getElementById('sagaSearchSeries');
                if (searchInput && typeof window.SagasAdminSearch.updateResults === 'function') {
                    window.SagasAdminSearch.updateResults(searchInput.value, activeTab);
                }
            }
        },

        add: function(itemJson) {
            const item = typeof itemJson === 'string' ? JSON.parse(itemJson.replace(/&quot;/g, '"')) : itemJson;
            if (!window.SagasAdminState.currentSagaItems) {
                window.SagasAdminState.currentSagaItems = [];
            }

            const exists = window.SagasAdminState.currentSagaItems.some(i => 
                String(i.id) === String(item.id) && i.type === item.type
            );
            if (!exists) {
                window.SagasAdminState.currentSagaItems.push(item);
                this.updateList();
                if (typeof window.SagasAdminValidation.updateSaveButtonState === 'function') {
                    window.SagasAdminValidation.updateSaveButtonState();
                }

                const activeTab = window.SagasAdminState.currentSearchTab || 'movies';
                const searchInput = activeTab === 'movies' ? 
                    document.getElementById('sagaSearchMovies') : 
                    document.getElementById('sagaSearchSeries');
                if (searchInput && typeof window.SagasAdminSearch.updateResults === 'function') {
                    window.SagasAdminSearch.updateResults(searchInput.value, activeTab);
                }
            }
        }
    };
})();

