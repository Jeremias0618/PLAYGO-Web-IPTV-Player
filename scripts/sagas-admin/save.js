(function() {
    'use strict';

    function openSagaModal(baseName, movies) {
        window.currentSagaMovies = movies;
        window.currentSagaBaseName = baseName;
        
        const modal = document.getElementById('sagaModal');
        if (!modal) return;
        
        const moviesList = document.getElementById('sagaMoviesList');
        if (moviesList) {
            moviesList.innerHTML = movies.map(movie => `
                <div class="saga-modal-movie-item">
                    <span>${escapeHtml(movie.name)}</span>
                    <span class="saga-modal-movie-id">ID: ${movie.id}</span>
                </div>
            `).join('');
        }
        
        const titleInput = document.getElementById('sagaTitle');
        if (titleInput) {
            titleInput.value = 'SAGA ' + baseName.toUpperCase();
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
        
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        } else {
            modal.style.display = 'block';
        }
    }
    
    function closeSagaModal() {
        const modal = document.getElementById('sagaModal');
        if (!modal) return;
        
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        } else {
            modal.style.display = 'none';
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('sagaImagePreview');
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function saveSaga() {
        const title = document.getElementById('sagaTitle')?.value.trim();
        const imageFile = document.getElementById('sagaImageFile')?.files[0];
        
        if (!title) {
            alert('Por favor ingresa un título para la saga');
            return;
        }
        
        if (!window.currentSagaMovies || window.currentSagaMovies.length === 0) {
            alert('No hay películas seleccionadas');
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
                console.error('Error:', error);
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
        formData.append('movies', JSON.stringify(window.currentSagaMovies));
        formData.append('image', imageUrl);
        
        fetch('libs/endpoints/SagasAdmin.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Saga guardada exitosamente');
                const modal = document.getElementById('sagaModal');
                if (modal && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) {
                        bsModal.hide();
                    }
                }
                loadSagas();
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
            console.error('Error:', error);
            alert('Error al guardar saga');
            const saveBtn = document.getElementById('saveSagaBtn');
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Saga';
            }
        });
    }

    function loadSagas() {
        fetch('libs/endpoints/SagasAdmin.php?action=get_sagas')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySagas(data.sagas);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function displaySagas(sagas) {
        const container = document.getElementById('savedSagasContainer');
        if (!container) return;
        
        if (!sagas || sagas.length === 0) {
            container.innerHTML = '<div class="sagas-admin-message">No hay sagas guardadas</div>';
            return;
        }
        
        let html = '<div class="sagas-admin-saved-list">';
        sagas.forEach(saga => {
            html += `
                <div class="sagas-admin-saved-item">
                    <div class="sagas-admin-saved-image">
                        ${saga.image ? `<img src="${escapeHtml(saga.image)}" alt="${escapeHtml(saga.title)}">` : '<div class="sagas-admin-no-image">Sin imagen</div>'}
                    </div>
                    <div class="sagas-admin-saved-info">
                        <h3>${escapeHtml(saga.title)}</h3>
                        <p>${saga.movies ? saga.movies.length : 0} películas</p>
                        <p class="sagas-admin-saved-date">Creada: ${escapeHtml(saga.created_at || 'N/A')}</p>
                    </div>
                    <button class="sagas-admin-btn-delete" onclick="deleteSaga('${escapeHtml(saga.id)}')">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    }

    function deleteSaga(sagaId) {
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
                alert('Saga eliminada exitosamente');
                loadSagas();
            } else {
                alert('Error al eliminar saga: ' + (data.error || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar saga');
        });
    }

    window.openSagaModal = openSagaModal;
    window.saveSaga = saveSaga;
    window.closeSagaModal = closeSagaModal;
    window.deleteSaga = deleteSaga;
    window.previewImage = previewImage;
    window.loadSagas = loadSagas;

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
            
            loadSagas();
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
        
        loadSagas();
    }
})();

