(function() {
    function initChannelFilter(categoriasCanales) {
        const canalesGrid = document.querySelector('.canales-grid');
        if (!canalesGrid) {
            return null;
        }

        let categoriaSeleccionada = '';

        function filtrarCanalesPorCategoria() {
            const cards = canalesGrid.querySelectorAll('.canal-card');
            let visibleCount = 0;
            
            cards.forEach((card) => {
                const catId = card.getAttribute('data-cat');
                const catIdStr = String(catId);
                const categoriaStr = String(categoriaSeleccionada);
                
                if (!categoriaSeleccionada || categoriaStr === catIdStr) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (categoriaSeleccionada && visibleCount === 0) {
                const noResultsMsg = canalesGrid.querySelector('.no-results-message');
                if (!noResultsMsg) {
                    const msg = document.createElement('div');
                    msg.className = 'no-results-message';
                    msg.style.cssText = 'color:#fff;font-size:1.2rem;text-align:center;padding:40px 20px;grid-column:1/-1;';
                    msg.textContent = 'No hay canales en esta categoría.';
                    canalesGrid.appendChild(msg);
                }
            } else {
                const noResultsMsg = canalesGrid.querySelector('.no-results-message');
                if (noResultsMsg) {
                    noResultsMsg.remove();
                }
            }

            let url = window.location.origin + window.location.pathname;
            if (categoriaSeleccionada) {
                const catObj = categoriasCanales.find(c => String(c.id) === String(categoriaSeleccionada));
                const catg = catObj ? encodeURIComponent(catObj.nombre) : '';
                url += `?id=${categoriaSeleccionada}&catg=${catg}`;
            } else {
                url += '?catg=TV%20en%20Vivo';
            }
            history.replaceState(null, '', url);
        }

        return {
            setCategoria: function(catId) {
                categoriaSeleccionada = catId ? String(catId) : '';
                filtrarCanalesPorCategoria();
            },
            getCategoria: function() {
                return categoriaSeleccionada;
            },
            getCategoriaNombre: function() {
                if (!categoriaSeleccionada) return '';
                const catObj = categoriasCanales.find(c => String(c.id) === String(categoriaSeleccionada));
                return catObj ? catObj.nombre : '';
            }
        };
    }

    if (typeof window !== 'undefined') {
        window.ChannelFilter = initChannelFilter;
    }
})();

