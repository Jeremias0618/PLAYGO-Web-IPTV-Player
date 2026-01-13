(function() {
    'use strict';

    window.SagasAdminSagas = {
        load: function() {
            const container = document.getElementById('savedSagasContainer');
            if (!container) return;

            container.innerHTML = '<h3 style="color: #fff; margin-bottom: 20px; font-size: 1.5rem;">Sagas Guardadas</h3><div class="sagas-admin-message">Cargando sagas...</div>';

            fetch('libs/endpoints/SagasAdmin.php?action=get_sagas')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.sagas) {
                        this.display(data.sagas);
                    } else {
                        container.innerHTML = '<h3 style="color: #fff; margin-bottom: 20px; font-size: 1.5rem;">Sagas Guardadas</h3><div class="sagas-admin-message">No hay sagas guardadas</div>';
                    }
                })
                .catch(() => {
                    container.innerHTML = '<h3 style="color: #fff; margin-bottom: 20px; font-size: 1.5rem;">Sagas Guardadas</h3><div class="sagas-admin-message">Error al cargar las sagas</div>';
                });
        },

        display: function(sagas) {
            const container = document.getElementById('savedSagasContainer');
            if (!container) return;

            if (!sagas || sagas.length === 0) {
                container.innerHTML = '<h3 style="color: #fff; margin-bottom: 20px; font-size: 1.5rem;">Sagas Guardadas</h3><div class="sagas-admin-message">No hay sagas guardadas</div>';
                return;
            }

            let html = '<h3 style="color: #fff; margin-bottom: 20px; font-size: 1.5rem;">Sagas Guardadas</h3>';
            html += '<div class="sagas-admin-grid">';

            sagas.forEach(saga => {
                const imageUrl = saga.image || 'assets/image/placeholder.jpg';
                const itemCount = saga.items ? saga.items.length : 0;
                const createdDate = saga.created_at ? new Date(saga.created_at).toLocaleDateString('es-ES') : '';

                html += `
                    <div class="saga-card" data-saga-id="${saga.id}">
                        <div class="saga-card-image">
                            <img src="${imageUrl}" alt="${window.SagasAdminUtils.escapeHtml(saga.title)}" onerror="this.src='assets/image/placeholder.jpg'">
                            <div class="saga-card-overlay">
                                <button class="saga-card-btn saga-card-btn-edit" onclick="window.SagasAdminSagas.edit('${saga.id}')" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="saga-card-btn saga-card-btn-delete" onclick="window.SagasAdminSagas.delete('${saga.id}')" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="saga-card-info">
                            <h4 class="saga-card-title">${window.SagasAdminUtils.escapeHtml(saga.title)}</h4>
                            <div class="saga-card-meta">
                                <span class="saga-card-count"><i class="fas fa-film"></i> ${itemCount} ${itemCount === 1 ? 'película' : 'películas'}</span>
                                ${createdDate ? `<span class="saga-card-date"><i class="fas fa-calendar"></i> ${createdDate}</span>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            container.innerHTML = html;
        },

        delete: function(sagaId) {
            if (!confirm('¿Estás seguro de que deseas eliminar esta saga?')) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'delete_saga');
            formData.append('saga_id', sagaId);

            fetch('libs/endpoints/SagasAdmin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.load();
                } else {
                    alert('Error al eliminar saga: ' + (data.error || 'Error desconocido'));
                }
            })
            .catch(() => {
                alert('Error al eliminar saga');
            });
        },

        edit: function(sagaId) {
            fetch('libs/endpoints/SagasAdmin.php?action=get_sagas')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.sagas) {
                        const saga = data.sagas.find(s => String(s.id) === String(sagaId));
                        if (saga) {
                            const sagaItems = saga.items.map(item => ({
                                id: item.id,
                                name: item.title,
                                type: item.type || 'movie',
                                poster: item.poster || ''
                            }));
                            const baseName = saga.title.replace(/^SAGA /, '');
                            
                            if (typeof window.SagasAdminModal.open === 'function') {
                                window.SagasAdminModal.open(baseName, sagaItems, String(sagaId));
                            }

                            setTimeout(() => {
                                const titleInput = document.getElementById('sagaTitle');
                                if (titleInput) {
                                    titleInput.value = saga.title;
                                }

                                const imagePreview = document.getElementById('sagaImagePreview');
                                const dropzone = document.getElementById('sagaDropzone');
                                const dropzoneContent = dropzone?.querySelector('.saga-dropzone-content');
                                if (saga.image && imagePreview && dropzone) {
                                    imagePreview.src = saga.image;
                                    imagePreview.style.display = 'block';
                                    if (dropzoneContent) {
                                        dropzoneContent.style.display = 'none';
                                    }
                                }

                                window.SagasAdminState.originalSagaState = {
                                    title: saga.title,
                                    items: JSON.parse(JSON.stringify(sagaItems)),
                                    image: saga.image || null
                                };

                                if (typeof window.SagasAdminPosters.loadItems === 'function') {
                                    window.SagasAdminPosters.loadItems(sagaItems);
                                }
                            }, 200);
                        }
                    }
                })
                .catch(() => {
                    alert('Error al cargar la saga para editar');
                });
        }
    };
})();

