(function() {
    function initChannelFilter(categoriasCanales) {
        const canalesGrid = document.querySelector('.canales-grid');
        if (!canalesGrid) {
            return null;
        }

        let categoriaSeleccionada = '';

        function filtrarCanalesPorCategoria() {
            const cards = canalesGrid.querySelectorAll('.canal-card');
            cards.forEach(card => {
                const catId = card.getAttribute('data-cat');
                if (!categoriaSeleccionada || categoriaSeleccionada === catId) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });

            let url = window.location.origin + window.location.pathname;
            if (categoriaSeleccionada) {
                const catObj = categoriasCanales.find(c => c.id === categoriaSeleccionada);
                const catg = catObj ? encodeURIComponent(catObj.nombre) : '';
                url += `?id=${categoriaSeleccionada}&catg=${catg}`;
            }
            history.replaceState(null, '', url);
        }

        return {
            setCategoria: function(catId) {
                categoriaSeleccionada = catId || '';
                filtrarCanalesPorCategoria();
            },
            getCategoria: function() {
                return categoriaSeleccionada;
            }
        };
    }

    if (typeof window !== 'undefined') {
        window.ChannelFilter = initChannelFilter;
    }
})();

