(function() {
    function initMobileCategories(categoriasCanales) {
        const mobileCategoriasBtn = document.getElementById('mobileCategoriasBtn');
        const mobileCategoriasDropdown = document.getElementById('mobileCategoriasDropdown');
        const mobileCategoriasClear = document.getElementById('mobileCategoriasClear');

        if (!mobileCategoriasBtn || !mobileCategoriasDropdown || window.innerWidth > 600) {
            return null;
        }

        let categoriaSeleccionada = '';

        function renderMobileCategoriasDropdown() {
            if (!mobileCategoriasDropdown) return;
            let html = `<div class="cat-option" data-id="">Todos</div>`;
            categoriasCanales.forEach(cat => {
                const selectedClass = cat.id === categoriaSeleccionada ? ' selected' : '';
                html += `<div class="cat-option${selectedClass}" data-id="${cat.id}">${cat.nombre}</div>`;
            });
            mobileCategoriasDropdown.innerHTML = html;
        }

        mobileCategoriasBtn.onclick = function() {
            mobileCategoriasDropdown.classList.toggle('active');
        };

        mobileCategoriasDropdown.onclick = function(e) {
            const option = e.target.closest('.cat-option');
            if (!option) return;
            categoriaSeleccionada = option.getAttribute('data-id') || '';
            renderMobileCategoriasDropdown();
            if (window.channelFilterInstance) {
                window.channelFilterInstance.setCategoria(categoriaSeleccionada);
            }
            mobileCategoriasDropdown.classList.remove('active');
        };

        if (mobileCategoriasClear) {
            mobileCategoriasClear.onclick = function() {
                categoriaSeleccionada = '';
                renderMobileCategoriasDropdown();
                if (window.channelFilterInstance) {
                    window.channelFilterInstance.setCategoria('');
                }
            };
        }

        document.addEventListener('click', function(e){
            if (!mobileCategoriasDropdown.contains(e.target) && !mobileCategoriasBtn.contains(e.target)) {
                mobileCategoriasDropdown.classList.remove('active');
            }
        });

        renderMobileCategoriasDropdown();

        return {
            setCategoria: function(catId) {
                categoriaSeleccionada = catId || '';
                renderMobileCategoriasDropdown();
            }
        };
    }

    if (typeof window !== 'undefined') {
        window.MobileCategories = initMobileCategories;
    }
})();

