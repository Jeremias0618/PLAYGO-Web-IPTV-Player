(function() {
    function initMobileCategories(categoriasCanales) {
        const mobileCategoriasBtn = document.getElementById('mobileCategoriasBtn');
        const mobileCategoriasDropdown = document.getElementById('mobileCategoriasDropdown');
        const mobileCategoriasClear = document.getElementById('mobileCategoriasClear');

        if (!mobileCategoriasBtn || !mobileCategoriasDropdown) {
            return null;
        }
        
        if (window.innerWidth > 600) {
            return null;
        }

        let categoriaSeleccionada = '';

        function updateMobileCategoriasButton() {
            if (!mobileCategoriasBtn) return;
            const urlParams = new URLSearchParams(window.location.search);
            const id = urlParams.get('id');
            const catg = urlParams.get('catg');
            
            if (id && catg && catg !== 'TV en Vivo') {
                const catNombre = decodeURIComponent(catg);
                mobileCategoriasBtn.innerHTML = `<i class="fa fa-list"></i> Categorías: ${catNombre}`;
            } else {
                mobileCategoriasBtn.innerHTML = `<i class="fa fa-list"></i> Categorías`;
            }
        }

        function renderMobileCategoriasDropdown() {
            if (!mobileCategoriasDropdown) {
                return;
            }
            
            let html = `<div class="cat-option" data-id="">Todos</div>`;
            categoriasCanales.forEach(cat => {
                const selectedClass = String(cat.id) === String(categoriaSeleccionada) ? ' selected' : '';
                html += `<div class="cat-option${selectedClass}" data-id="${cat.id}">${cat.nombre}</div>`;
            });
            mobileCategoriasDropdown.innerHTML = html;
        }

        mobileCategoriasBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            mobileCategoriasDropdown.classList.toggle('active');
        };

        if (mobileCategoriasDropdown) {
            mobileCategoriasDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
                const option = e.target.closest('.cat-option');
                
                if (!option) {
                    return;
                }
                
                mobileCategoriasDropdown.classList.remove('active');
                
                const catId = option.getAttribute('data-id') || '';
                const catNombre = option.textContent.trim();
                
                if (catId === '') {
                    window.location.href = 'channels.php?catg=TV%20en%20Vivo';
                } else {
                    const catObj = categoriasCanales.find(c => String(c.id) === String(catId));
                    const catg = catObj ? encodeURIComponent(catObj.nombre) : encodeURIComponent(catNombre);
                    window.location.href = `channels.php?id=${catId}&catg=${catg}`;
                }
            });
        }

        if (mobileCategoriasClear) {
            mobileCategoriasClear.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                window.location.href = 'channels.php?catg=TV%20en%20Vivo';
            };
        }

        document.addEventListener('click', function(e){
            if (!mobileCategoriasDropdown.contains(e.target) && !mobileCategoriasBtn.contains(e.target)) {
                mobileCategoriasDropdown.classList.remove('active');
            }
        });

        renderMobileCategoriasDropdown();
        updateMobileCategoriasButton();

        return {
            setCategoria: function(catId) {
                categoriaSeleccionada = catId ? String(catId) : '';
                renderMobileCategoriasDropdown();
                updateMobileCategoriasButton();
            }
        };
    }

    if (typeof window !== 'undefined') {
        window.MobileCategories = initMobileCategories;
    }
})();

