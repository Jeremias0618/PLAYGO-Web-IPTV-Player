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
    
    const isAndroid = /Android/.test(navigator.userAgent);
    
    let savedOrientation = null;
    
    function lockOrientation(orientation) {
        if (!screen || !screen.orientation) return;
        
        try {
            if (screen.orientation && screen.orientation.lock) {
                screen.orientation.lock(orientation).catch(function(err) {
                    console.log('Orientation lock failed:', err);
                });
            } else if (screen.lockOrientation) {
                screen.lockOrientation(orientation);
            } else if (screen.mozLockOrientation) {
                screen.mozLockOrientation(orientation);
            } else if (screen.msLockOrientation) {
                screen.msLockOrientation(orientation);
            }
        } catch (e) {
            console.log('Orientation lock error:', e);
        }
    }
    
    function unlockOrientation() {
        if (!screen) return;
        
        try {
            if (screen.orientation && screen.orientation.unlock) {
                screen.orientation.unlock();
            } else if (screen.unlockOrientation) {
                screen.unlockOrientation();
            } else if (screen.mozUnlockOrientation) {
                screen.mozUnlockOrientation();
            } else if (screen.msUnlockOrientation) {
                screen.msUnlockOrientation();
            }
        } catch (e) {
            console.log('Orientation unlock error:', e);
        }
    }
    
    function handleFullscreenChange(isFullscreen) {
        if (isAndroid && isFullscreen) {
            savedOrientation = screen.orientation ? screen.orientation.angle : null;
            lockOrientation('landscape');
        } else if (isAndroid && !isFullscreen) {
            unlockOrientation();
        }
    }
    
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
            const isFullscreen = !!document.webkitFullscreenElement;
            toggleHeaderVisibility(isFullscreen);
            handleFullscreenChange(isFullscreen);
        });
        
        document.addEventListener('fullscreenchange', function() {
            const isFullscreen = !!document.fullscreenElement;
            toggleHeaderVisibility(isFullscreen);
            handleFullscreenChange(isFullscreen);
        });
        
        if (window.player && typeof window.player.on === "function") {
            window.player.on('enterfullscreen', function() {
                toggleHeaderVisibility(true);
                handleFullscreenChange(true);
            });
            
            window.player.on('exitfullscreen', function() {
                toggleHeaderVisibility(false);
                handleFullscreenChange(false);
            });
        }
        
        const videos = document.querySelectorAll('video');
        videos.forEach(video => {
            video.addEventListener('webkitbeginfullscreen', function() {
                toggleHeaderVisibility(true);
                handleFullscreenChange(true);
            });
            
            video.addEventListener('webkitendfullscreen', function() {
                toggleHeaderVisibility(false);
                handleFullscreenChange(false);
            });
        });
    } else if (isAndroid) {
        document.addEventListener('webkitfullscreenchange', function() {
            handleFullscreenChange(!!document.webkitFullscreenElement);
        });
        
        document.addEventListener('fullscreenchange', function() {
            handleFullscreenChange(!!document.fullscreenElement);
        });
        
        if (window.player && typeof window.player.on === "function") {
            window.player.on('enterfullscreen', function() {
                handleFullscreenChange(true);
            });
            
            window.player.on('exitfullscreen', function() {
                handleFullscreenChange(false);
            });
        }
        
        const videos = document.querySelectorAll('video');
        videos.forEach(video => {
            video.addEventListener('webkitbeginfullscreen', function() {
                handleFullscreenChange(true);
            });
            
            video.addEventListener('webkitendfullscreen', function() {
                handleFullscreenChange(false);
            });
        });
    }
});

