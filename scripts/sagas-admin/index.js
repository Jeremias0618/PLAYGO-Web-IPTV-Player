(function() {
    'use strict';

    function init() {
        const imageInput = document.getElementById('sagaImageFile');
        if (imageInput) {
            imageInput.addEventListener('change', function() {
                if (typeof window.SagasAdminImage.preview === 'function') {
                    window.SagasAdminImage.preview(this);
                }
            });
        }

        const saveBtn = document.getElementById('saveSagaBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
                if (typeof window.SagasAdminSave.save === 'function') {
                    window.SagasAdminSave.save();
                }
            });
        }

        if (typeof window.SagasAdminModal.setupCloseButtons === 'function') {
            window.SagasAdminModal.setupCloseButtons();
        }

        const collectBtn = document.getElementById('collectMoviesBtn');
        if (collectBtn) {
            collectBtn.addEventListener('click', function() {
                if (typeof window.SagasAdminCollection.collect === 'function') {
                    window.SagasAdminCollection.collect();
                }
            });
        }

        if (typeof window.SagasAdminSagas.load === 'function') {
            window.SagasAdminSagas.load();
        }
    }

    window.SagasAdmin = {
        init: init
    };

    window.openSagaModal = function(baseName, items, sagaId) {
        if (typeof window.SagasAdminModal.open === 'function') {
            window.SagasAdminModal.open(baseName, items, sagaId);
        }
    };

    window.saveSaga = function() {
        if (typeof window.SagasAdminSave.save === 'function') {
            window.SagasAdminSave.save();
        }
    };

    window.closeSagaModal = function(force) {
        if (typeof window.SagasAdminModal.close === 'function') {
            window.SagasAdminModal.close(force);
        }
    };

    window.closeAllModals = function() {
        if (typeof window.SagasAdminModal.closeAll === 'function') {
            window.SagasAdminModal.closeAll();
        }
    };

    window.deleteSaga = function(sagaId) {
        if (typeof window.SagasAdminSagas.delete === 'function') {
            window.SagasAdminSagas.delete(sagaId);
        }
    };

    window.editSaga = function(sagaId) {
        if (typeof window.SagasAdminSagas.edit === 'function') {
            window.SagasAdminSagas.edit(sagaId);
        }
    };

    window.loadSagas = function() {
        if (typeof window.SagasAdminSagas.load === 'function') {
            window.SagasAdminSagas.load();
        }
    };

    window.removeItemFromSaga = function(index) {
        if (typeof window.SagasAdminItems.remove === 'function') {
            window.SagasAdminItems.remove(index);
        }
    };

    window.addItemToSaga = function(itemJson) {
        if (typeof window.SagasAdminItems.add === 'function') {
            window.SagasAdminItems.add(itemJson);
        }
    };

    window.switchSearchTab = function(tab) {
        if (typeof window.SagasAdminSearch.switchTab === 'function') {
            window.SagasAdminSearch.switchTab(tab);
        }
    };

    window.collectMovies = function() {
        if (typeof window.SagasAdminCollection.collect === 'function') {
            window.SagasAdminCollection.collect();
        }
    };

    window.createSagaFromSelected = function() {
        if (typeof window.SagasAdminCollection.createFromSelected === 'function') {
            window.SagasAdminCollection.createFromSelected();
        }
    };

    window.switchEditSearchTab = function(tab) {
        if (typeof window.SagasAdminEditSearch.switchTab === 'function') {
            window.SagasAdminEditSearch.switchTab(tab);
        }
    };

    window.saveEditSaga = function() {
        const title = document.getElementById('sagaEditTitle')?.value.trim();
        const imageFile = document.getElementById('sagaEditImageFile')?.files[0];
        const saveBtn = document.getElementById('saveEditSagaBtn');

        if (!title) {
            alert('Por favor ingresa un título para la saga');
            return;
        }

        if (!window.SagasAdminEditItems.currentItems || window.SagasAdminEditItems.currentItems().length === 0) {
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
                    saveEditSagaData(title, imageUrl);
                } else {
                    alert('Error al subir imagen: ' + (data.error || 'Error desconocido'));
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
                    }
                }
            })
            .catch(() => {
                alert('Error al subir imagen');
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
                }
            });
        } else {
            const imagePreview = document.getElementById('sagaEditImagePreview');
            if (imagePreview && imagePreview.style.display !== 'none' && imagePreview.src) {
                try {
                    const url = new URL(imagePreview.src, window.location.origin);
                    imageUrl = url.pathname.replace(/^\//, '');
                } catch (e) {
                    imageUrl = imagePreview.src.replace(/^https?:\/\/[^\/]+/, '').replace(/^\//, '');
                }
            } else if (editOriginalState) {
                imageUrl = editOriginalState.image || '';
            }
            saveEditSagaData(title, imageUrl);
        }

        function saveEditSagaData(title, imageUrl) {
            const items = window.SagasAdminEditItems.currentItems().map((item, index) => ({
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
            if (editCurrentSagaId) {
                formData.append('saga_id', editCurrentSagaId);
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
                const saveBtn = document.getElementById('saveEditSagaBtn');
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
                }

                if (data.success) {
                    if (typeof window.SagasAdminEditModal.close === 'function') {
                        window.SagasAdminEditModal.close();
                    }
                    if (typeof window.SagasAdminSagas.load === 'function') {
                        window.SagasAdminSagas.load();
                    }
                } else {
                    alert('Error al guardar saga: ' + (data.error || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error saving saga:', error);
                alert('Error al guardar saga: ' + (error.message || 'Error desconocido'));
                const saveBtn = document.getElementById('saveEditSagaBtn');
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
                }
            });
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

