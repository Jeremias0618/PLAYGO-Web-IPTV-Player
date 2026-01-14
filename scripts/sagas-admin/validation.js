(function() {
    'use strict';

    let validationTimeout;

    window.SagasAdminValidation = {
        validateTitle: function() {
            const titleInput = document.getElementById('sagaTitle');
            if (!titleInput) return;

            clearTimeout(validationTimeout);
            validationTimeout = setTimeout(() => {
                const title = titleInput.value.trim();
                const saveBtn = document.getElementById('saveSagaBtn');

                if (!title) {
                    titleInput.classList.remove('is-invalid');
                    const existingFeedback = titleInput.parentElement.querySelector('.invalid-feedback');
                    if (existingFeedback) {
                        existingFeedback.remove();
                    }

                    if (saveBtn) {
                        const hasItems = window.SagasAdminState.currentSagaItems && window.SagasAdminState.currentSagaItems.length > 0;
                        saveBtn.disabled = !hasItems;
                        saveBtn.style.opacity = !hasItems ? '0.5' : '1';
                        saveBtn.style.cursor = !hasItems ? 'not-allowed' : 'pointer';
                    }
                    return;
                }

                const checkTitleBody = 'action=check_title&title=' + encodeURIComponent(title);
                const finalBody = window.SagasAdminState.currentSagaId ? 
                    checkTitleBody + '&saga_id=' + encodeURIComponent(window.SagasAdminState.currentSagaId) : 
                    checkTitleBody;

                fetch('libs/endpoints/SagasAdmin.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: finalBody
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    const saveBtn = document.getElementById('saveSagaBtn');

                    if (data.exists) {
                        titleInput.classList.add('is-invalid');
                        let feedback = titleInput.parentElement.querySelector('.invalid-feedback');
                        if (!feedback) {
                            feedback = document.createElement('div');
                            feedback.className = 'invalid-feedback';
                            titleInput.parentElement.appendChild(feedback);
                        }
                        feedback.textContent = 'Ya existe una saga con ese nombre';

                        if (saveBtn) {
                            saveBtn.disabled = true;
                            saveBtn.style.opacity = '0.5';
                            saveBtn.style.cursor = 'not-allowed';
                        }
                    } else {
                        titleInput.classList.remove('is-invalid');
                        const existingFeedback = titleInput.parentElement.querySelector('.invalid-feedback');
                        if (existingFeedback) {
                            existingFeedback.remove();
                        }

                        if (saveBtn) {
                            const hasItems = window.SagasAdminState.currentSagaItems && window.SagasAdminState.currentSagaItems.length > 0;
                            saveBtn.disabled = !hasItems || !title.trim();
                            saveBtn.style.opacity = (!hasItems || !title.trim()) ? '0.5' : '1';
                            saveBtn.style.cursor = (!hasItems || !title.trim()) ? 'not-allowed' : 'pointer';
                        }
                    }
                })
                .catch(() => {});
            }, 500);
        },

        updateSaveButtonState: function() {
            const saveBtn = document.getElementById('saveSagaBtn');
            const titleInput = document.getElementById('sagaTitle');

            if (!saveBtn || !titleInput) return;

            const title = titleInput.value.trim();
            const hasItems = window.SagasAdminState.currentSagaItems && window.SagasAdminState.currentSagaItems.length > 0;
            const isInvalid = titleInput.classList.contains('is-invalid');

            saveBtn.disabled = !title || !hasItems || isInvalid;
            saveBtn.style.opacity = (!title || !hasItems || isInvalid) ? '0.5' : '1';
            saveBtn.style.cursor = (!title || !hasItems || isInvalid) ? 'not-allowed' : 'pointer';
        },

        hasUnsavedChanges: function() {
            if (!window.SagasAdminState.currentSagaId) {
                return false;
            }

            if (!window.SagasAdminState.originalSagaState) {
                return false;
            }

            const titleInput = document.getElementById('sagaTitle');
            const currentTitle = titleInput ? titleInput.value.trim() : '';
            const originalTitle = (window.SagasAdminState.originalSagaState.title || '').trim();

            if (currentTitle !== originalTitle) {
                return true;
            }

            const currentItems = window.SagasAdminState.currentSagaItems || [];
            const originalItems = window.SagasAdminState.originalSagaState.items || [];

            if (currentItems.length !== originalItems.length) {
                return true;
            }

            for (let i = 0; i < currentItems.length; i++) {
                const current = currentItems[i];
                const original = originalItems[i];

                if (!original) {
                    return true;
                }

                if (String(current.id) !== String(original.id) || 
                    (current.type || 'movie') !== (original.type || 'movie') ||
                    (current.name || '') !== (original.name || '')) {
                    return true;
                }
            }

            const normalizeImagePath = function(path) {
                if (typeof window.SagasAdminUtils !== 'undefined' && typeof window.SagasAdminUtils.normalizeImagePath === 'function') {
                    return window.SagasAdminUtils.normalizeImagePath(path);
                }
                if (!path) return null;
                let normalized = path.replace(/^https?:\/\/[^\/]+/, '').replace(/^\//, '');
                const pathParts = normalized.split('/');
                const assetsIndex = pathParts.indexOf('assets');
                if (assetsIndex >= 0) {
                    normalized = pathParts.slice(assetsIndex).join('/');
                }
                return normalized || null;
            };

            const imagePreview = document.getElementById('sagaImagePreview');
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

            let originalImage = window.SagasAdminState.originalSagaState.image || null;
            if (originalImage) {
                originalImage = originalImage.replace(/^https?:\/\/[^\/]+/, '').replace(/^\//, '');
                if (originalImage === '') {
                    originalImage = null;
                }
            }

            const normalizedCurrentImage = normalizeImagePath(currentImage);
            const normalizedOriginalImage = normalizeImagePath(originalImage);

            if (normalizedCurrentImage !== normalizedOriginalImage) {
                if ((normalizedCurrentImage === null || normalizedCurrentImage === '') && (normalizedOriginalImage === null || normalizedOriginalImage === '')) {
                    return false;
                }
                return true;
            }

            return false;
        }
    };
})();


