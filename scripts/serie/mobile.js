(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        const btnMenu = document.querySelector('.header__btn');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
        const closeMenuBtn = document.getElementById('closeMobileMenu');

        function openMenu() {
            mobileMenu.classList.add('active');
            mobileMenuOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeMenu() {
            mobileMenu.classList.remove('active');
            mobileMenuOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        if(btnMenu && mobileMenu && mobileMenuOverlay) {
            btnMenu.addEventListener('click', openMenu);
            mobileMenuOverlay.addEventListener('click', closeMenu);
            if(closeMenuBtn) closeMenuBtn.addEventListener('click', closeMenu);
        }
    });
})();

