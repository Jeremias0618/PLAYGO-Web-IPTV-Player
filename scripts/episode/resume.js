document.addEventListener('DOMContentLoaded', function() {
    const episodeKey = window.episodeKey;
    const episodeTitle = window.episodeTitle;
    
    if (!episodeKey || !episodeTitle) return;
    
    function showResumeNotification(time, onAccept, onCancel) {
        if (document.getElementById('resumeNotifBg')) return;
        const bg = document.createElement('div');
        bg.id = 'resumeNotifBg';
        bg.style.position = 'fixed';
        bg.style.left = '0';
        bg.style.top = '0';
        bg.style.width = '100vw';
        bg.style.height = '100vh';
        bg.style.background = 'rgba(0,0,0,0.85)';
        bg.style.zIndex = '999999';
        bg.style.display = 'flex';
        bg.style.alignItems = 'center';
        bg.style.justifyContent = 'center';

        const notif = document.createElement('div');
        notif.id = 'resumeNotif';
        notif.style.background = '#232027';
        notif.style.color = '#fff';
        notif.style.padding = '32px 38px';
        notif.style.borderRadius = '16px';
        notif.style.boxShadow = '0 8px 32px #000a';
        notif.style.fontSize = '1.18rem';
        notif.style.display = 'flex';
        notif.style.flexDirection = 'column';
        notif.style.alignItems = 'center';
        notif.style.gap = '28px';
        notif.style.maxWidth = '90vw';
        notif.style.textAlign = 'center';

        const min = Math.floor(time/60);
        const sec = Math.floor(time%60).toString().padStart(2,'0');
        notif.innerHTML = `
            <span style="font-size:1.15rem;">¿Deseas continuar viendo <b>${episodeTitle}</b> desde el minuto <b>${min}:${sec}</b>?</span>
            <div style="display:flex;gap:18px;">
                <button id="resumeAccept" style="background:#e50914;color:#fff;border:none;border-radius:8px;padding:10px 28px;font-size:1.1rem;cursor:pointer;margin-right:10px;">Aceptar</button>
                <button id="resumeCancel" style="background:#444;color:#fff;border:none;border-radius:8px;padding:10px 28px;font-size:1.1rem;cursor:pointer;">Cancelar</button>
            </div>
        `;
        bg.appendChild(notif);
        document.body.appendChild(bg);

        document.getElementById('resumeAccept').onclick = function() {
            bg.remove();
            onAccept();
        };
        document.getElementById('resumeCancel').onclick = function() {
            bg.remove();
            if (onCancel) onCancel();
        };
        document.addEventListener('keydown', function escListener(e) {
            if (e.key === "Escape") {
                bg.remove();
                document.removeEventListener('keydown', escListener);
                if (onCancel) onCancel();
            }
        });
    }

    let video = document.getElementById('plyr-video');
    if (window.player && typeof window.player.on === "function") {
        window.player.on('timeupdate', function() {
            localStorage.setItem(episodeKey, Math.floor(window.player.currentTime));
        });
        window.player.on('ended', function() {
            localStorage.removeItem(episodeKey);
        });
        const lastTime = parseInt(localStorage.getItem(episodeKey) || "0");
        if (lastTime > 10) {
            window.player.pause();
            showResumeNotification(lastTime, function() {
                window.player.currentTime = lastTime;
                window.player.play();
            });
        }
    } else if (video) {
        video.addEventListener('timeupdate', function() {
            localStorage.setItem(episodeKey, Math.floor(video.currentTime));
        });
        video.addEventListener('ended', function() {
            localStorage.removeItem(episodeKey);
        });
        const lastTime = parseInt(localStorage.getItem(episodeKey) || "0");
        if (lastTime > 10) {
            video.pause();
            showResumeNotification(lastTime, function() {
                video.currentTime = lastTime;
                video.play();
            });
        }
    }
});

