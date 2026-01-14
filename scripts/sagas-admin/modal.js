(function() {
    'use strict';

    window.SagasAdminModal = {
        setupCloseButtons: function() {
            const closeBtn = document.querySelector('#sagaModal .saga-modal-close');
            const cancelBtn = document.querySelector('#sagaModal .saga-modal-btn-secondary[data-bs-dismiss="modal"]');
            
            if (closeBtn) {
                const newCloseBtn = closeBtn.cloneNode(true);
                closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);
                newCloseBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (typeof window.SagasAdminModal.close === 'function') {
                        window.SagasAdminModal.close(false);
                    }
                });
            }
            
            if (cancelBtn) {
                const newCancelBtn = cancelBtn.cloneNode(true);
                cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
                newCancelBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (typeof window.SagasAdminModal.close === 'function') {
                        window.SagasAdminModal.close(false);
                    }
                });
            }
        },

        open: function(baseName, items, sagaId) {
            window.SagasAdminState.currentSagaItems = JSON.parse(JSON.stringify(items || []));
            window.SagasAdminState.currentSagaBaseName = baseName;
            window.SagasAdminState.currentSagaId = sagaId ? String(sagaId) : null;
            window.SagasAdminState.allMoviesCache = window.SagasAdminState.allMoviesCache || [];
            window.SagasAdminState.allSeriesCache = window.SagasAdminState.allSeriesCache || [];
            window.SagasAdminState.currentSearchTab = 'movies';
            
            if (!window.SagasAdminState.originalSagaState) {
                const titleValue = baseName === 'Nueva Saga' ? '' : 'SAGA ' + baseName.toUpperCase();
                window.SagasAdminState.originalSagaState = {
                    title: titleValue,
                    items: JSON.parse(JSON.stringify(items || [])),
                    image: null
                };
            }
            window.SagasAdminState.sagaHasUnsavedChanges = false;
            
            const modal = document.getElementById('sagaModal');
            if (!modal) return;
            
            if (typeof window.SagasAdminItems.updateList === 'function') {
                window.SagasAdminItems.updateList();
            }
            if (typeof window.SagasAdminSearch.switchTab === 'function') {
                window.SagasAdminSearch.switchTab('movies');
            }
            
            setTimeout(() => {
                this.setupCloseButtons();
            }, 100);
            
            const titleInput = document.getElementById('sagaTitle');
            if (titleInput) {
                titleInput.value = baseName === 'Nueva Saga' ? '' : 'SAGA ' + baseName.toUpperCase();
                titleInput.classList.remove('is-invalid');
                const existingFeedback = titleInput.parentElement.querySelector('.invalid-feedback');
                if (existingFeedback) {
                    existingFeedback.remove();
                }
                
                titleInput.addEventListener('input', function() {
                    if (typeof window.SagasAdminValidation.validateTitle === 'function') {
                        window.SagasAdminValidation.validateTitle();
                    }
                    if (typeof window.SagasAdminValidation.updateSaveButtonState === 'function') {
                        window.SagasAdminValidation.updateSaveButtonState();
                    }
                });
                
                if (titleInput.value.trim()) {
                    if (typeof window.SagasAdminValidation.validateTitle === 'function') {
                        window.SagasAdminValidation.validateTitle();
                    }
                }
                
                if (typeof window.SagasAdminValidation.updateSaveButtonState === 'function') {
                    window.SagasAdminValidation.updateSaveButtonState();
                }
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
                searchMoviesInput.removeEventListener('input', window.SagasAdminSearch.handleMoviesSearch);
                searchMoviesInput.addEventListener('input', window.SagasAdminSearch.handleMoviesSearch);
                if (typeof window.SagasAdminSearch.updateResults === 'function') {
                    window.SagasAdminSearch.updateResults('', 'movies');
                }
            }
            
            const searchSeriesInput = document.getElementById('sagaSearchSeries');
            if (searchSeriesInput) {
                searchSeriesInput.value = '';
                searchSeriesInput.removeEventListener('input', window.SagasAdminSearch.handleSeriesSearch);
                searchSeriesInput.addEventListener('input', window.SagasAdminSearch.handleSeriesSearch);
                if (typeof window.SagasAdminSearch.updateResults === 'function') {
                    window.SagasAdminSearch.updateResults('', 'series');
                }
            }
            
            if (typeof window.SagasAdminImage.initDropzone === 'function') {
                window.SagasAdminImage.initDropzone();
            }
            
            const dropzoneContent = document.querySelector('.saga-dropzone-content');
            if (dropzoneContent) {
                dropzoneContent.style.display = 'flex';
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
                    
                    if (typeof window.SagasAdminItems.initSortable === 'function') {
                        window.SagasAdminItems.initSortable();
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
                        if (typeof window.SagasAdminValidation !== 'undefined' && typeof window.SagasAdminValidation.hasUnsavedChanges === 'function') {
                            if (!window.SagasAdminValidation.hasUnsavedChanges() || confirm('¿Estás seguro de que deseas salir sin guardar los cambios realizados?')) {
                                bsModal.hide();
                            }
                        } else {
                            bsModal.hide();
                        }
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
                    if (typeof window.SagasAdminModal.close === 'function') {
                        window.SagasAdminModal.close(false);
                    }
                });
                document.body.appendChild(backdrop);
                modal.style.display = 'block';
                modal.classList.add('show');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }
        },

        close: function(force) {
            if (!force && typeof window.SagasAdminValidation !== 'undefined' && typeof window.SagasAdminValidation.hasUnsavedChanges === 'function') {
                if (window.SagasAdminValidation.hasUnsavedChanges()) {
                    if (!confirm('¿Estás seguro de que deseas salir sin guardar los cambios realizados?')) {
                        return;
                    }
                }
            }
            
            const modal = document.getElementById('sagaModal');
            if (!modal) return;
            
            try {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    if (typeof bootstrap.Modal.getInstance === 'function') {
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal && typeof bsModal.hide === 'function') {
                            bsModal.hide();
                            if (typeof window.SagasAdminModal.reset === 'function') {
                                window.SagasAdminModal.reset();
                            }
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
            if (typeof window.SagasAdminModal.reset === 'function') {
                window.SagasAdminModal.reset();
            }
        },

        reset: function() {
            window.SagasAdminState.reset();
            
            const titleInput = document.getElementById('sagaTitle');
            if (titleInput) {
                titleInput.value = '';
                titleInput.classList.remove('is-invalid');
                const existingFeedback = titleInput.parentElement.querySelector('.invalid-feedback');
                if (existingFeedback) {
                    existingFeedback.remove();
                }
            }
            
            const imagePreview = document.getElementById('sagaImagePreview');
            const dropzone = document.getElementById('sagaDropzone');
            const dropzoneContent = dropzone?.querySelector('.saga-dropzone-content');
            if (imagePreview) {
                imagePreview.style.display = 'none';
                imagePreview.src = '';
            }
            if (dropzoneContent) {
                dropzoneContent.style.display = 'flex';
            }
            
            const fileInput = document.getElementById('sagaImageFile');
            if (fileInput) {
                fileInput.value = '';
            }
            
            if (typeof window.SagasAdminItems.updateList === 'function') {
                window.SagasAdminItems.updateList();
            }
        },

        showSuccess: function() {
            const sagaModal = document.getElementById('sagaModal');
            const successModal = document.getElementById('sagaSuccessModal');
            if (!successModal) return;
            
            const closeSagaModalFirst = () => {
                if (sagaModal) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal && typeof bootstrap.Modal.getInstance === 'function') {
                        try {
                            const bsSagaModal = bootstrap.Modal.getInstance(sagaModal);
                            if (bsSagaModal) {
                                bsSagaModal.hide();
                            } else {
                                sagaModal.classList.remove('show');
                                sagaModal.setAttribute('aria-hidden', 'true');
                                sagaModal.style.display = 'none';
                            }
                        } catch (e) {
                            sagaModal.classList.remove('show');
                            sagaModal.setAttribute('aria-hidden', 'true');
                            sagaModal.style.display = 'none';
                        }
                    } else {
                        sagaModal.style.display = 'none';
                        sagaModal.classList.remove('show');
                        sagaModal.setAttribute('aria-hidden', 'true');
                    }
                }
                
                setTimeout(() => {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        try {
                            const bsModal = new bootstrap.Modal(successModal, {
                                backdrop: true,
                                keyboard: false
                            });
                            bsModal.show();
                        } catch (e) {
                            successModal.style.display = 'block';
                            successModal.classList.add('show');
                            successModal.setAttribute('aria-hidden', 'false');
                            successModal.setAttribute('aria-modal', 'true');
                            document.body.style.overflow = 'hidden';
                        }
                    } else {
                        successModal.style.display = 'block';
                        successModal.classList.add('show');
                        successModal.setAttribute('aria-hidden', 'false');
                        successModal.setAttribute('aria-modal', 'true');
                        document.body.style.overflow = 'hidden';
                    }
                }, 300);
            };
            
            closeSagaModalFirst();
        },

        closeAll: function() {
            const sagaModal = document.getElementById('sagaModal');
            const successModal = document.getElementById('sagaSuccessModal');
            
            const closeModal = (modalElement) => {
                if (!modalElement) return;
                
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal && typeof bootstrap.Modal.getInstance === 'function') {
                    try {
                        const bsModal = bootstrap.Modal.getInstance(modalElement);
                        if (bsModal) {
                            bsModal.hide();
                            return;
                        }
                    } catch (e) {
                        // Fallback to manual close
                    }
                }
                
                modalElement.classList.remove('show');
                modalElement.setAttribute('aria-hidden', 'true');
                modalElement.setAttribute('aria-modal', 'false');
                modalElement.style.display = 'none';
            };
            
            closeModal(successModal);
            closeModal(sagaModal);
            
            setTimeout(() => {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());
                document.body.style.overflow = '';
                document.body.classList.remove('modal-open');
            }, 300);
        }
    };
})();

