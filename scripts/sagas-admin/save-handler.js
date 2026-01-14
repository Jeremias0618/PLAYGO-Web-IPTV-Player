(function() {
    'use strict';

    window.SagasAdminSave = {
        save: function() {
            const title = document.getElementById('sagaTitle')?.value.trim();
            const imageFile = document.getElementById('sagaImageFile')?.files[0];
            const titleInput = document.getElementById('sagaTitle');
            const saveBtn = document.getElementById('saveSagaBtn');

            if (!title) {
                alert('Por favor ingresa un título para la saga');
                return;
            }

            if (titleInput && titleInput.classList.contains('is-invalid')) {
                alert('El título de la saga ya está en uso. Por favor, elige otro nombre.');
                titleInput.focus();
                return;
            }

            if (!window.SagasAdminState.currentSagaItems || window.SagasAdminState.currentSagaItems.length === 0) {
                alert('No hay contenido seleccionado');
                return;
            }

            if (saveBtn && saveBtn.disabled) {
                return;
            }

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
                        window.SagasAdminSave.saveData(title, imageUrl);
                    } else {
                        alert('Error al subir imagen: ' + (data.error || 'Error desconocido'));
                        if (saveBtn) {
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Saga';
                        }
                    }
                })
                .catch(() => {
                    alert('Error al subir imagen');
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Saga';
                    }
                });
            } else {
                const imagePreview = document.getElementById('sagaImagePreview');
                if (imagePreview && imagePreview.style.display !== 'none' && imagePreview.src) {
                    try {
                        const url = new URL(imagePreview.src, window.location.origin);
                        imageUrl = url.pathname.replace(/^\//, '');
                    } catch (e) {
                        imageUrl = imagePreview.src.replace(/^https?:\/\/[^\/]+/, '').replace(/^\//, '');
                    }
                    if (typeof window.SagasAdminUtils !== 'undefined' && typeof window.SagasAdminUtils.normalizeImagePath === 'function') {
                        imageUrl = window.SagasAdminUtils.normalizeImagePath(imageUrl) || imageUrl;
                    }
                } else if (window.SagasAdminState.originalSagaState && window.SagasAdminState.originalSagaState.image) {
                    imageUrl = window.SagasAdminState.originalSagaState.image;
                }
                window.SagasAdminSave.saveData(title, imageUrl);
            }
        },

        saveData: function(title, imageUrl) {
            if (typeof window.SagasAdminPosters.loadMissing === 'function') {
                window.SagasAdminPosters.loadMissing().then(() => {
                    this.performSave(title, imageUrl);
                });
            } else {
                this.performSave(title, imageUrl);
            }
        },

        performSave: function(title, imageUrl) {
            const items = window.SagasAdminState.currentSagaItems.map((item, index) => ({
                id: item.id,
                title: item.name,
                poster: item.poster || '',
                type: item.type || 'movie',
                order: index + 1
            }));

            const formData = new FormData();
            formData.append('action', 'save_saga');
            formData.append('title', title);
            formData.append('items', JSON.stringify(items));
            formData.append('image', imageUrl);
            if (window.SagasAdminState.currentSagaId) {
                formData.append('saga_id', String(window.SagasAdminState.currentSagaId));
            }

            fetch('libs/endpoints/SagasAdmin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                const saveBtn = document.getElementById('saveSagaBtn');
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Saga';
                }

                if (data.success) {
                    if (window.SagasAdminState.currentSagaId && window.SagasAdminState.originalSagaState) {
                        const titleInput = document.getElementById('sagaTitle');
                        const imagePreview = document.getElementById('sagaImagePreview');
                        if (titleInput) {
                            window.SagasAdminState.originalSagaState.title = titleInput.value.trim();
                        }
                        if (imagePreview && imagePreview.style.display !== 'none') {
                            window.SagasAdminState.originalSagaState.image = imagePreview.src;
                        } else {
                            window.SagasAdminState.originalSagaState.image = null;
                        }
                        window.SagasAdminState.originalSagaState.items = JSON.parse(JSON.stringify(window.SagasAdminState.currentSagaItems));
                    }
                    if (typeof window.SagasAdminModal.showSuccess === 'function') {
                        window.SagasAdminModal.showSuccess();
                    }
                    if (typeof window.SagasAdminSagas.load === 'function') {
                        try {
                            window.SagasAdminSagas.load();
                        } catch (e) {
                            console.error('Error calling loadSagas:', e);
                        }
                    }
                } else {
                    alert('Error al guardar saga: ' + (data.error || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error saving saga:', error);
                alert('Error al guardar saga: ' + (error.message || 'Error desconocido'));
                const saveBtn = document.getElementById('saveSagaBtn');
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Saga';
                }
            });
        }
    };
})();

