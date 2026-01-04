(function() {
    'use strict';

    function hasActiveFilters() {
        const urlParams = new URLSearchParams(window.location.search);
        const genero = urlParams.get('genero') || '';
        const rating = urlParams.get('rating') || '';
        const ratingMin = urlParams.get('rating_min') || '';
        const ratingMax = urlParams.get('rating_max') || '';
        const year = urlParams.get('year') || '';
        const yearMin = urlParams.get('year_min') || '';
        const yearMax = urlParams.get('year_max') || '';
        const orden = urlParams.get('orden') || '';
        
        return genero !== '' || 
               rating !== '' ||
               (ratingMin !== '' && ratingMax !== '') ||
               year !== '' ||
               (yearMin !== '' && yearMax !== '') ||
               orden !== '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (hasActiveFilters()) {
            const popularSection = document.getElementById('popularSection');
            if (popularSection) {
                popularSection.style.display = 'none';
            }
        }
    });
})();

