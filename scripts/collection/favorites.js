(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        const favButtons = document.querySelectorAll('.collection-btn-fav');
        favButtons.forEach(function(btn) {
            const movieId = btn.getAttribute('data-movie-id');
            const favText = btn.querySelector('.fav-text-' + movieId);
            let isFav = false;

            fetch('libs/endpoints/UserData.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=fav_check&id=' + movieId + '&tipo=pelicula'
            })
            .then(res => res.json())
            .then(data => {
                if (data.is_fav) {
                    isFav = true;
                    if (favText) favText.textContent = 'Favorito';
                    btn.classList.add('favorito-active');
                }
            })
            .catch(err => {});

            btn.addEventListener('click', function() {
                const action = isFav ? 'fav_remove' : 'fav_add';
                const movieName = btn.getAttribute('data-movie-name');
                const movieImg = btn.getAttribute('data-movie-img');
                const movieYear = btn.getAttribute('data-movie-year');
                const movieRating = btn.getAttribute('data-movie-rating');

                fetch('libs/endpoints/UserData.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=' + action + '&id=' + movieId + '&nombre=' + encodeURIComponent(movieName) + '&img=' + encodeURIComponent(movieImg) + '&ano=' + encodeURIComponent(movieYear) + '&rate=' + encodeURIComponent(movieRating) + '&tipo=pelicula'
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        isFav = !isFav;
                        if (favText) favText.textContent = isFav ? 'Favorito' : 'Agregar a Favoritos';
                        btn.classList.toggle('favorito-active', isFav);
                    }
                })
                .catch(err => {});
            });
        });
    });
})();

