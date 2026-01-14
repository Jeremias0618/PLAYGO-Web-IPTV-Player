(function() {
    'use strict';

    function getRandomSagaWallpaper() {
        const wallpapers = [
            'assets/image/wallpaper_02.webp',
            'assets/image/wallpaper_03.webp',
            'assets/image/wallpaper_04.webp',
            'assets/image/wallpaper_05.webp',
            'assets/image/wallpaper_channels.webp'
        ];
        return wallpapers[Math.floor(Math.random() * wallpapers.length)];
    }

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
                const imageUrl = saga.image || getRandomSagaWallpaper();
                const fallbackImage = getRandomSagaWallpaper();
                const itemCount = saga.items ? saga.items.length : 0;
                const createdDate = saga.created_at ? new Date(saga.created_at).toLocaleDateString('es-ES') : '';

                html += `
                    <div class="saga-card" data-saga-id="${saga.id}">
                        <div class="saga-card-image">
                            <img src="${imageUrl}" alt="${window.SagasAdminUtils.escapeHtml(saga.title)}" onerror="this.src='${fallbackImage}'">
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
            if (typeof window.SagasAdminEditModal.open === 'function') {
                window.SagasAdminEditModal.open(sagaId);
            }
        }
    };
})();

