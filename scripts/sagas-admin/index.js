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

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

