(function() {
    'use strict';

    function hasActiveFilters() {
        const genero = document.getElementById('genero')?.value || '';
        const ratingMin = document.getElementById('rating_min')?.value || '';
        const ratingMax = document.getElementById('rating_max')?.value || '';
        const yearMin = document.getElementById('year_min')?.value || '';
        const yearMax = document.getElementById('year_max')?.value || '';
        const orden = document.getElementById('orden')?.value || '';
        
        return genero !== '' || 
               (ratingMin !== '' && ratingMax !== '') || 
               (yearMin !== '' && yearMax !== '') || 
               orden !== '';
    }

    function updateClearButton() {
        const limpiarBtn = document.getElementById('limpiarFiltrosBtn');
        if (limpiarBtn) {
            if (hasActiveFilters()) {
                limpiarBtn.disabled = false;
                limpiarBtn.style.opacity = '1';
                limpiarBtn.style.cursor = 'pointer';
            } else {
                limpiarBtn.disabled = true;
                limpiarBtn.style.opacity = '0.5';
                limpiarBtn.style.cursor = 'not-allowed';
            }
        }
    }
    
    window.updateClearButton = updateClearButton;

    function updateOrderDirectionButton() {
        const ordenSelect = document.getElementById('orden');
        const ordenBtn = document.getElementById('ordenDirectionBtn');
        const ordenIcon = document.getElementById('ordenDirectionIcon');
        const ordenDirInput = document.getElementById('orden_dir');
        
        if (ordenSelect && ordenBtn && ordenIcon && ordenDirInput) {
            const ordenValue = ordenSelect.value;
            
            if (ordenValue !== '') {
                ordenBtn.style.display = 'block';
                const currentDir = ordenDirInput.value || 'asc';
                
                if (currentDir === 'asc') {
                    ordenIcon.className = 'fa-solid fa-arrow-up';
                    ordenBtn.title = 'Ordenar descendente (Z-A, Mayor-Menor)';
                } else {
                    ordenIcon.className = 'fa-solid fa-arrow-down';
                    ordenBtn.title = 'Ordenar ascendente (A-Z, Menor-Mayor)';
                }
            } else {
                ordenBtn.style.display = 'none';
            }
        }
    }

    const limpiarFiltrosBtn = document.getElementById('limpiarFiltrosBtn');
    if (limpiarFiltrosBtn) {
        limpiarFiltrosBtn.onclick = function() {
            if (!this.disabled) {
                window.location.href = window.location.pathname;
            }
        };
    }

    const ordenDirectionBtn = document.getElementById('ordenDirectionBtn');
    if (ordenDirectionBtn) {
        ordenDirectionBtn.onclick = function() {
            const ordenDirInput = document.getElementById('orden_dir');
            if (ordenDirInput) {
                const currentDir = ordenDirInput.value || 'asc';
                ordenDirInput.value = currentDir === 'asc' ? 'desc' : 'asc';
                
                const filtrosForm = document.getElementById('filtrosForm');
                if (filtrosForm) {
                    filtrosForm.submit();
                }
            }
        };
    }

    const ordenSelect = document.getElementById('orden');
    if (ordenSelect) {
        ordenSelect.addEventListener('change', function() {
            const ordenDirInput = document.getElementById('orden_dir');
            if (ordenDirInput && this.value === '') {
                ordenDirInput.value = 'asc';
            }
            updateOrderDirectionButton();
            updateClearButton();
        });
    }

    const generoSelect = document.getElementById('genero');
    if (generoSelect) {
        generoSelect.addEventListener('change', updateClearButton);
    }

    const ratingMinInput = document.getElementById('rating_min');
    const ratingMaxInput = document.getElementById('rating_max');
    if (ratingMinInput) ratingMinInput.addEventListener('change', updateClearButton);
    if (ratingMaxInput) ratingMaxInput.addEventListener('change', updateClearButton);

    const yearMinInput = document.getElementById('year_min');
    const yearMaxInput = document.getElementById('year_max');
    if (yearMinInput) yearMinInput.addEventListener('change', updateClearButton);
    if (yearMaxInput) yearMaxInput.addEventListener('change', updateClearButton);

    updateClearButton();
    updateOrderDirectionButton();
})();
