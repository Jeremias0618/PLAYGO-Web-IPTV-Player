(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        const movieKey = window.movieKey || '';
        const movieTitle = window.movieTitle || '';
        
        if (!movieKey || !movieTitle) {
            return;
        }
        
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
                <span style="font-size:1.15rem;">¿Deseas continuar viendo <b>${movieTitle}</b> desde el minuto <b>${min}:${sec}</b>?</span>
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
        
        const movieId = window.movieId || '';
        const movieTipo = window.movieTipo || 'movie';
        let saveProgressTimeout = null;
        let lastSavedTime = 0;
        
        function saveProgressToServer(time, immediate) {
            if (time === lastSavedTime && !immediate) return;
            
            if (saveProgressTimeout && !immediate) {
                clearTimeout(saveProgressTimeout);
            }
            
            const saveFunction = function() {
                fetch('libs/endpoints/UserData.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=progress_save&id=${movieId}&tipo=${movieTipo}&time=${time}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        lastSavedTime = time;
                    }
                })
                .catch(function() {});
            };
            
            if (immediate) {
                saveFunction();
            } else {
                saveProgressTimeout = setTimeout(saveFunction, 1000);
            }
        }
        
        function getProgressFromServer(callback) {
            fetch('libs/endpoints/UserData.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=progress_get&id=${movieId}&tipo=${movieTipo}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.time > 0) {
                    callback(data.time);
                } else {
                    callback(0);
                }
            })
            .catch(function() {
                callback(0);
            });
        }
        
        function removeProgress() {
            lastSavedTime = 0;
            fetch('libs/endpoints/UserData.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=progress_remove&id=${movieId}&tipo=${movieTipo}`
            }).catch(function() {});
        }
        
        if (document.getElementById('plyr-video') && window.Plyr) {
            let plyrInterval = setInterval(function() {
                if (window.player && typeof window.player.on === "function") {
                    clearInterval(plyrInterval);
                    const plyrPlayer = window.player;
                    
                    getProgressFromServer(function(serverTime) {
                        const lastTime = serverTime;
                        if (lastTime > 10) {
                            const videoElement = plyrPlayer.media;
                            if (!videoElement) {
                                return;
                            }
                            
                            const resumePlayback = function() {
                                const setTimeAndPlay = function() {
                                    if (videoElement.readyState >= 2 && videoElement.duration > 0) {
                                        videoElement.currentTime = lastTime;
                                        const onSeeked = function() {
                                            videoElement.removeEventListener('seeked', onSeeked);
                                            setTimeout(function() {
                                                plyrPlayer.play();
                                            }, 100);
                                        };
                                        videoElement.addEventListener('seeked', onSeeked);
                                        if (Math.abs(videoElement.currentTime - lastTime) < 1) {
                                            setTimeout(function() {
                                                plyrPlayer.play();
                                            }, 100);
                                        }
                                    } else {
                                        const onCanPlay = function() {
                                            videoElement.removeEventListener('canplay', onCanPlay);
                                            videoElement.currentTime = lastTime;
                                            const onSeeked = function() {
                                                videoElement.removeEventListener('seeked', onSeeked);
                                                setTimeout(function() {
                                                    plyrPlayer.play();
                                                }, 100);
                                            };
                                            videoElement.addEventListener('seeked', onSeeked);
                                        };
                                        videoElement.addEventListener('canplay', onCanPlay);
                                    }
                                };
                                
                                if (videoElement.readyState >= 2 && videoElement.duration > 0) {
                                    setTimeAndPlay();
                                } else {
                                    const onMetadataLoaded = function() {
                                        videoElement.removeEventListener('loadedmetadata', onMetadataLoaded);
                                        setTimeAndPlay();
                                    };
                                    videoElement.addEventListener('loadedmetadata', onMetadataLoaded);
                                }
                            };
                            
                            plyrPlayer.pause();
                            showResumeNotification(lastTime, function() {
                                resumePlayback();
                            });
                        }
                    });
                    
                    plyrPlayer.on('timeupdate', function() {
                        const currentTime = Math.floor(plyrPlayer.currentTime);
                        saveProgressToServer(currentTime, false);
                    });
                    
                    plyrPlayer.on('seeked', function() {
                        const currentTime = Math.floor(plyrPlayer.currentTime);
                        saveProgressToServer(currentTime, true);
                    });
                    
                    plyrPlayer.on('ended', function() {
                        removeProgress();
                    });
                }
            }, 200);
        } else if (document.querySelector('video#plyr-video') === null && document.querySelector('video')) {
            const video = document.querySelector('video');
            
            getProgressFromServer(function(serverTime) {
                const lastTime = serverTime;
                if (lastTime > 10) {
                    const resumePlayback = function() {
                        const setTimeAndPlay = function() {
                            if (video.readyState >= 2 && video.duration > 0) {
                                video.currentTime = lastTime;
                                const onSeeked = function() {
                                    video.removeEventListener('seeked', onSeeked);
                                    setTimeout(function() {
                                        video.play();
                                    }, 100);
                                };
                                video.addEventListener('seeked', onSeeked);
                                if (Math.abs(video.currentTime - lastTime) < 1) {
                                    setTimeout(function() {
                                        video.play();
                                    }, 100);
                                }
                            } else {
                                const onCanPlay = function() {
                                    video.removeEventListener('canplay', onCanPlay);
                                    video.currentTime = lastTime;
                                    const onSeeked = function() {
                                        video.removeEventListener('seeked', onSeeked);
                                        setTimeout(function() {
                                            video.play();
                                        }, 100);
                                    };
                                    video.addEventListener('seeked', onSeeked);
                                };
                                video.addEventListener('canplay', onCanPlay);
                            }
                        };
                        
                        if (video.readyState >= 2 && video.duration > 0) {
                            setTimeAndPlay();
                        } else {
                            const onMetadataLoaded = function() {
                                video.removeEventListener('loadedmetadata', onMetadataLoaded);
                                setTimeAndPlay();
                            };
                            video.addEventListener('loadedmetadata', onMetadataLoaded);
                        }
                    };
                    
                    video.pause();
                    showResumeNotification(lastTime, function() {
                        resumePlayback();
                    });
                }
            });
            
            video.addEventListener('timeupdate', function() {
                const currentTime = Math.floor(video.currentTime);
                saveProgressToServer(currentTime, false);
            });
            
            video.addEventListener('seeked', function() {
                const currentTime = Math.floor(video.currentTime);
                saveProgressToServer(currentTime, true);
            });
            
            video.addEventListener('ended', function() {
                removeProgress();
            });
        }
    });
})();

