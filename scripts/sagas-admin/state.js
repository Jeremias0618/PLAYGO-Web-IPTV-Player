(function() {
    'use strict';

    window.SagasAdminState = {
        currentSagaItems: [],
        currentSagaBaseName: '',
        currentSagaId: null,
        allMoviesCache: [],
        allSeriesCache: [],
        currentSearchTab: 'movies',
        originalSagaState: null,
        sagaHasUnsavedChanges: false,

        init: function() {
            this.currentSagaItems = [];
            this.currentSagaBaseName = '';
            this.currentSagaId = null;
            this.allMoviesCache = this.allMoviesCache || [];
            this.allSeriesCache = this.allSeriesCache || [];
            this.currentSearchTab = 'movies';
            this.originalSagaState = null;
            this.sagaHasUnsavedChanges = false;
        },

        reset: function() {
            this.currentSagaItems = [];
            this.currentSagaId = null;
            this.originalSagaState = null;
            this.sagaHasUnsavedChanges = false;
        }
    };
})();

