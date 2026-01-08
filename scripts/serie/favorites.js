(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        const btnFav = document.getElementById('btnFavorito');
        const favText = document.getElementById('favText');
        if (!btnFav || !favText) return;
        
        let isFav = false;
        const serieId = window.serieId || '';
        const serieName = window.serieName || '';
        const serieImg = window.serieImg || '';
        const serieBackdrop = window.serieBackdrop || '';
        const serieYear = window.serieYear || '';
        const serieRating = window.serieRating || '';

        if (!serieId) return;

        fetch('libs/endpoints/UserData.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=fav_check&id=${serieId}&tipo=serie`
        })
        .then(res => res.json())
        .then(data => {
            if (data.is_fav) {
                isFav = true;
                favText.textContent = 'Favorito';
                btnFav.classList.add('favorito-active');
            }
        });

        btnFav.addEventListener('click', function() {
            const action = isFav ? 'fav_remove' : 'fav_add';
            fetch('libs/endpoints/UserData.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=${action}&id=${serieId}&nombre=${encodeURIComponent(serieName)}&img=${encodeURIComponent(serieImg)}&backdrop=${encodeURIComponent(serieBackdrop)}&ano=${encodeURIComponent(serieYear)}&rate=${encodeURIComponent(serieRating)}&tipo=serie`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    isFav = !isFav;
                    favText.textContent = isFav ? 'Favorito' : 'Agregar a Favoritos';
                    btnFav.classList.toggle('favorito-active', isFav);
                }
            });
        });
    });
})();

