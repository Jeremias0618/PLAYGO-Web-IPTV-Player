(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        const btnFav = document.getElementById('btnFavorito');
        const favText = document.getElementById('favText');
        if (!btnFav || !favText) return;
        
        let isFav = false;
        const movieId = window.movieId || '';
        const movieName = window.movieName || '';
        const movieImg = window.movieImg || '';
        const movieBackdrop = window.movieBackdrop || '';
        const movieYear = window.movieYear || '';
        const movieRating = window.movieRating || '';
        
        if (!movieId) return;
        
        fetch('libs/endpoints/UserData.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=fav_check&id=${movieId}&tipo=pelicula`
        })
        .then(res => res.json())
        .then(data => {
            if (data.is_fav) {
                isFav = true;
                favText.textContent = 'Favorito';
                btnFav.classList.add('favorito-active');
                const icon = btnFav.querySelector('i.fa-star');
                if (icon) icon.style.color = '#ffd700';
                favText.style.color = '#ffd700';
            }
        });
        
        btnFav.addEventListener('click', function() {
            const action = isFav ? 'fav_remove' : 'fav_add';
            fetch('libs/endpoints/UserData.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=${action}&id=${movieId}&nombre=${encodeURIComponent(movieName)}&img=${encodeURIComponent(movieImg)}&backdrop=${encodeURIComponent(movieBackdrop)}&ano=${encodeURIComponent(movieYear)}&rate=${encodeURIComponent(movieRating)}&tipo=pelicula`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    isFav = !isFav;
                    favText.textContent = isFav ? 'Favorito' : 'Agregar a Favoritos';
                    btnFav.classList.toggle('favorito-active', isFav);
                    const icon = btnFav.querySelector('i.fa-star');
                    if (icon) icon.style.color = isFav ? '#ffd700' : '#fff';
                    favText.style.color = isFav ? '#ffd700' : '#fff';
                }
            });
        });
    });
})();

