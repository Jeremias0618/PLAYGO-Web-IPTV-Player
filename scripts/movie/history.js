(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        let historialGuardado = false;
        const movieId = window.movieId || '';
        const movieName = window.movieName || '';
        const movieImg = window.movieImg || '';
        const movieYear = window.movieYear || '';
        const movieRating = window.movieRating || '';
        
        if (!movieId) return;
        
        function guardarHistorial() {
            if (historialGuardado) return;
            historialGuardado = true;
            fetch('db/base.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=hist_add&id=${movieId}&nombre=${encodeURIComponent(movieName)}&img=${encodeURIComponent(movieImg)}&ano=${encodeURIComponent(movieYear)}&rate=${encodeURIComponent(movieRating)}&tipo=pelicula`
            });
        }
        
        if (window.player && typeof window.player.on === "function") {
            window.player.on('play', guardarHistorial);
        } else if (document.querySelector('video#plyr-video') === null && document.querySelector('video')) {
            const video = document.querySelector('video');
            video.addEventListener('play', guardarHistorial);
        }
    });
})();

