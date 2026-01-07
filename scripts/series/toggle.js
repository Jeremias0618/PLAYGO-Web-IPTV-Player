(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('filtrosToggleBtn');
        const filtrosForm = document.getElementById('filtrosForm');
        const toggleText = document.getElementById('filtrosToggleText');
        
        if (toggleBtn && filtrosForm && toggleText) {
            toggleBtn.addEventListener('click', function() {
                if (filtrosForm.classList.contains('filtros-hidden')) {
                    filtrosForm.classList.remove('filtros-hidden');
                    toggleText.textContent = 'Ocultar Filtros';
                } else {
                    filtrosForm.classList.add('filtros-hidden');
                    toggleText.textContent = 'Mostrar Filtros';
                }
            });
        }
    });
})();

