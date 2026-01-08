(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        let historialGuardado = false;
        
        if (!window.episodeHistoryData) return;
        
        const serieId = window.episodeHistoryData.serieId || '';
        const serieName = window.episodeHistoryData.serieName || '';
        const posterImg = window.episodeHistoryData.posterImg || '';
        const backdrop = window.episodeHistoryData.backdrop || '';
        const ano = window.episodeHistoryData.ano || '';
        const rate = window.episodeHistoryData.rate || '';
        
        if (!serieId) return;
        
        function guardarHistorial() {
            if (historialGuardado) return;
            historialGuardado = true;
            fetch('libs/endpoints/UserData.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=hist_add&id=${serieId}&nombre=${encodeURIComponent(serieName)}&img=${encodeURIComponent(posterImg)}&backdrop=${encodeURIComponent(backdrop)}&ano=${encodeURIComponent(ano)}&rate=${encodeURIComponent(rate)}&tipo=serie`
            });
        }
        
        if (document.getElementById('plyr-video') && window.Plyr) {
            let plyrInterval = setInterval(function() {
                if (window.player && typeof window.player.on === "function") {
                    clearInterval(plyrInterval);
                    window.player.on('play', guardarHistorial);
                }
            }, 200);
        } else if (document.querySelector('video#plyr-video') === null && document.querySelector('video')) {
            const video = document.querySelector('video');
            video.addEventListener('play', guardarHistorial);
        }
    });
})();
