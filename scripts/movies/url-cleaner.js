(function() {
    'use strict';

    function cleanURL() {
        const urlParams = new URLSearchParams(window.location.search);
        const cleanedParams = new URLSearchParams();
        let hasChanges = false;
        
        const rating = urlParams.get('rating') || '';
        const year = urlParams.get('year') || '';
        
        for (const [key, value] of urlParams.entries()) {
            if (value === null || value === '' || value === undefined) {
                hasChanges = true;
                continue;
            }
            
            if (key === 'rating') {
                cleanedParams.append('rating', value);
                continue;
            }
            
            if (key === 'rating_min' || key === 'rating_max') {
                if (rating === '') {
                    const min = urlParams.get('rating_min') || '';
                    const max = urlParams.get('rating_max') || '';
                    if (min !== '' && max !== '') {
                        if (key === 'rating_min') {
                            cleanedParams.append('rating_min', min);
                            cleanedParams.append('rating_max', max);
                        }
                    } else {
                        hasChanges = true;
                    }
                } else {
                    hasChanges = true;
                }
                continue;
            }
            
            if (key === 'year') {
                cleanedParams.append('year', value);
                continue;
            }
            
            if (key === 'year_min' || key === 'year_max') {
                if (year === '') {
                    const min = urlParams.get('year_min') || '';
                    const max = urlParams.get('year_max') || '';
                    if (min !== '' && max !== '') {
                        if (key === 'year_min') {
                            cleanedParams.append('year_min', min);
                            cleanedParams.append('year_max', max);
                        }
                    } else {
                        hasChanges = true;
                    }
                } else {
                    hasChanges = true;
                }
                continue;
            }
            
            if (key === 'orden_dir') {
                const orden = urlParams.get('orden') || '';
                if (orden !== '') {
                    cleanedParams.append(key, value);
                } else {
                    hasChanges = true;
                }
                continue;
            }
            
            cleanedParams.append(key, value);
        }
        
        if (hasChanges) {
            const newUrl = cleanedParams.toString() 
                ? `${window.location.pathname}?${cleanedParams.toString()}`
                : window.location.pathname;
            window.history.replaceState({}, '', newUrl);
        }
    }

    document.addEventListener('DOMContentLoaded', cleanURL);
})();

