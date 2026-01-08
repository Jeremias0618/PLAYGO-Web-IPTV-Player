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

document.addEventListener('DOMContentLoaded', function() {
    const header = document.querySelector('.header');
    const navbarOverlay = document.querySelector('.navbar-overlay');
    
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) || 
                  (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    
    if (isIOS) {
        function toggleHeaderVisibility(isFullscreen) {
            if (isFullscreen) {
                if (header) header.style.display = 'none';
                if (navbarOverlay) navbarOverlay.style.display = 'none';
            } else {
                if (header) header.style.display = 'block';
                if (navbarOverlay) navbarOverlay.style.display = 'block';
            }
        }
        
        document.addEventListener('webkitfullscreenchange', function() {
            toggleHeaderVisibility(!!document.webkitFullscreenElement);
        });
        
        document.addEventListener('fullscreenchange', function() {
            toggleHeaderVisibility(!!document.fullscreenElement);
        });
        
        if (window.player && typeof window.player.on === "function") {
            window.player.on('enterfullscreen', function() {
                toggleHeaderVisibility(true);
            });
            
            window.player.on('exitfullscreen', function() {
                toggleHeaderVisibility(false);
            });
        }
        
        const videos = document.querySelectorAll('video');
        videos.forEach(video => {
            video.addEventListener('webkitbeginfullscreen', function() {
                toggleHeaderVisibility(true);
            });
            
            video.addEventListener('webkitendfullscreen', function() {
                toggleHeaderVisibility(false);
            });
        });
    }
});

