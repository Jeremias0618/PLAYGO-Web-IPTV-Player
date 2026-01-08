(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownItems = document.querySelectorAll('.season-dropdown-item');
        dropdownItems.forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const season = this.getAttribute('data-season');
                document.querySelectorAll('.season-tabs .nav-link').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('show', 'active'));
                document.getElementById('season-dropdown-tab').classList.add('active');
                const pane = document.getElementById('season-' + season);
                if (pane) {
                    pane.classList.add('show', 'active');
                    document.getElementById('season-dropdown-tab').textContent = 'Temporada ' + season;
                }
            });
        });
        document.querySelectorAll('.season-tabs .nav-link:not(.dropdown-toggle)').forEach(function(tabBtn) {
            tabBtn.addEventListener('click', function() {
                const dropdownTab = document.getElementById('season-dropdown-tab');
                if (dropdownTab) dropdownTab.textContent = 'Temporada';
            });
        });
    });
})();

