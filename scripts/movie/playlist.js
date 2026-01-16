(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        const btnPlaylist = document.getElementById('btnPlaylist');
        const tooltipContainer = document.getElementById('playlistTooltip');
        if (!btnPlaylist || !tooltipContainer) return;
        
        const movieId = window.movieId || '';
        const movieName = window.movieName || '';
        const movieImg = window.movieImg || '';
        const movieBackdrop = window.movieBackdrop || '';
        const movieYear = window.movieYear || '';
        const movieRating = window.movieRating || '';
        const movieTipo = window.movieTipo || 'movie';
        
        if (!movieId) return;
        
        let playlists = {};
        let isTooltipOpen = false;
        
        function loadPlaylists() {
            return fetch('libs/endpoints/UserData.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=playlist_list'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    playlists = data.playlists || {};
                    if (Object.keys(playlists).length === 0) {
                        playlists = {'VER MÁS TARDE': []};
                    }
                    if (!playlists['VER MÁS TARDE']) {
                        playlists['VER MÁS TARDE'] = [];
                    }
                    updatePlaylistButtonState();
                }
                return playlists;
            });
        }
        
        function checkIfInPlaylist(playlistName) {
            if (!playlists[playlistName]) return false;
            for (const item of playlists[playlistName]) {
                if (item.id == movieId && item.type == movieTipo) {
                    return true;
                }
            }
            return false;
        }
        
        function checkIfInAnyPlaylist() {
            for (const playlistName in playlists) {
                if (checkIfInPlaylist(playlistName)) {
                    return true;
                }
            }
            return false;
        }
        
        function updatePlaylistButtonState() {
            const isInAny = checkIfInAnyPlaylist();
            const icon = btnPlaylist.querySelector('i.fa-bookmark');
            const span = btnPlaylist.querySelector('span');
            
            if (isInAny) {
                btnPlaylist.classList.add('playlist-active');
                if (icon) icon.style.color = '#5dc1b9';
                if (span) span.style.color = '#5dc1b9';
            } else {
                btnPlaylist.classList.remove('playlist-active');
                if (icon) icon.style.color = '#fff';
                if (span) span.style.color = '#fff';
            }
        }
        
        function showPlaylistTooltip() {
            if (isTooltipOpen) {
                hidePlaylistTooltip();
                return;
            }
            
            loadPlaylists().then(() => {
                const sortedPlaylists = Object.keys(playlists).sort((a, b) => {
                    if (a === 'VER MÁS TARDE') return -1;
                    if (b === 'VER MÁS TARDE') return 1;
                    return a.localeCompare(b);
                });
                
                let tooltipHtml = `
                    <div style="background: #232027; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.6); min-width: 320px; max-width: 400px; overflow: hidden;">
                        <div style="padding: 16px 20px; border-bottom: 1px solid #444;">
                            <h3 style="margin: 0; font-size: 1.2rem; color: #fff; font-weight: 600;">Guardar en...</h3>
                        </div>
                        <div style="max-height: 400px; overflow-y: auto;">
                `;
                
                for (const playlistName of sortedPlaylists) {
                    const isInPlaylist = checkIfInPlaylist(playlistName);
                    tooltipHtml += `
                        <div class="playlist-item" 
                             data-playlist="${playlistName.replace(/"/g, '&quot;')}" 
                             style="display: flex; align-items: center; padding: 12px 20px; cursor: pointer; transition: background 0.2s; border-bottom: 1px solid #2a2a2a;"
                             onmouseover="this.style.background='#2a2a2a'"
                             onmouseout="this.style.background='transparent'">
                            <div style="width: 40px; height: 40px; background: #2a2a2a; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 12px; flex-shrink: 0;">
                                <i class="fa fa-list" style="color: #888; font-size: 1.2rem;"></i>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-size: 1rem; color: #fff; font-weight: 500; margin-bottom: 4px;">${playlistName}</div>
                                <div style="font-size: 0.85rem; color: #888;">Privada</div>
                            </div>
                            <div style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                ${isInPlaylist ? '<i class="fa fa-check" style="color: #e50914; font-size: 1.1rem;"></i>' : '<div style="width: 20px; height: 20px; border: 2px solid #666; border-radius: 4px;"></div>'}
                            </div>
                        </div>
                    `;
                }
                
                tooltipHtml += `
                        </div>
                        <div style="padding: 12px 20px; border-top: 1px solid #444;">
                            <button id="newPlaylistBtn" style="width: 100%; padding: 10px; background: transparent; border: 1px solid #444; border-radius: 8px; color: #fff; cursor: pointer; font-size: 1rem; transition: all 0.2s;"
                                    onmouseover="this.style.background='#2a2a2a'; this.style.borderColor='#666'"
                                    onmouseout="this.style.background='transparent'; this.style.borderColor='#444'">
                                <i class="fa fa-plus" style="margin-right: 8px;"></i> Nueva playlist
                            </button>
                        </div>
                    </div>
                `;
                
                tooltipContainer.innerHTML = tooltipHtml;
                tooltipContainer.style.display = 'block';
                isTooltipOpen = true;
                
                const playlistItems = tooltipContainer.querySelectorAll('.playlist-item');
                playlistItems.forEach(item => {
                    item.addEventListener('click', function() {
                        const playlistName = this.getAttribute('data-playlist');
                        handlePlaylistSelect(playlistName);
                    });
                });
                
                document.getElementById('newPlaylistBtn').addEventListener('click', function(e) {
                    e.stopPropagation();
                    showCreatePlaylistModal();
                });
            });
        }
        
        function hidePlaylistTooltip() {
            tooltipContainer.style.display = 'none';
            isTooltipOpen = false;
        }
        
        function handlePlaylistSelect(playlistName) {
            const isInPlaylist = checkIfInPlaylist(playlistName);
            const action = isInPlaylist ? 'playlist_remove' : 'playlist_add';
            
            fetch('libs/endpoints/UserData.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=${action}&playlist_name=${encodeURIComponent(playlistName)}&id=${movieId}&nombre=${encodeURIComponent(movieName)}&img=${encodeURIComponent(movieImg)}&backdrop=${encodeURIComponent(movieBackdrop)}&ano=${encodeURIComponent(movieYear)}&rate=${encodeURIComponent(movieRating)}&tipo=${movieTipo}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadPlaylists().then(() => {
                        updatePlaylistButtonState();
                        showPlaylistTooltip();
                    });
                }
            });
        }
        
        function showCreatePlaylistModal() {
            if (document.getElementById('createPlaylistModalBg')) return;
            
            const bg = document.createElement('div');
            bg.id = 'createPlaylistModalBg';
            bg.style.position = 'fixed';
            bg.style.left = '0';
            bg.style.top = '0';
            bg.style.width = '100vw';
            bg.style.height = '100vh';
            bg.style.background = 'rgba(0,0,0,0.85)';
            bg.style.zIndex = '100001';
            bg.style.display = 'flex';
            bg.style.alignItems = 'center';
            bg.style.justifyContent = 'center';
            bg.style.opacity = '0';
            bg.style.transition = 'opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
            bg.style.backdropFilter = 'blur(8px)';
            bg.style.webkitBackdropFilter = 'blur(8px)';
            
            const modal = document.createElement('div');
            modal.id = 'createPlaylistModal';
            modal.style.background = '#232027';
            modal.style.color = '#fff';
            modal.style.padding = '32px';
            modal.style.borderRadius = '16px';
            modal.style.boxShadow = '0 8px 32px #000a';
            modal.style.minWidth = '400px';
            modal.style.maxWidth = '90vw';
            modal.style.transform = 'scale(0.85) translateY(20px)';
            modal.style.opacity = '0';
            modal.style.transition = 'transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
            
            modal.innerHTML = `
                <h3 style="margin: 0 0 24px 0; font-size: 1.4rem; text-align: center;">Nueva playlist</h3>
                <input type="text" id="newPlaylistNameInput" placeholder="Elige un título" 
                       style="width: 100%; padding: 12px; background: #2a2a2a; border: 1px solid #444; border-radius: 8px; color: #fff; font-size: 1rem; margin-bottom: 24px; box-sizing: border-box;">
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button id="cancelCreateBtn" 
                            style="padding: 10px 24px; background: #444; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 1rem; transition: background 0.2s;">
                        Cancelar
                    </button>
                    <button id="confirmCreateBtn" 
                            style="padding: 10px 24px; background: #e50914; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 1rem; transition: background 0.2s;">
                        Crear
                    </button>
                </div>
            `;
            
            bg.appendChild(modal);
            document.body.appendChild(bg);
            
            setTimeout(() => {
                bg.style.opacity = '1';
                modal.style.transform = 'scale(1) translateY(0)';
                modal.style.opacity = '1';
            }, 10);
            
            const nameInput = document.getElementById('newPlaylistNameInput');
            setTimeout(() => {
                nameInput.focus();
            }, 400);
            
            function closeModal() {
                bg.style.opacity = '0';
                modal.style.transform = 'scale(0.85) translateY(20px)';
                modal.style.opacity = '0';
                setTimeout(() => {
                    bg.remove();
                }, 400);
            }
            
            document.getElementById('cancelCreateBtn').onclick = closeModal;
            
            document.getElementById('confirmCreateBtn').onclick = function() {
                const newName = nameInput.value.trim();
                if (!newName) {
                    alert('Por favor ingresa un nombre para la playlist');
                    return;
                }
                const existingPlaylists = Object.keys(playlists);
                if (existingPlaylists.includes(newName)) {
                    alert('Ya existe una playlist con ese nombre. Por favor elige otro nombre.');
                    nameInput.focus();
                    nameInput.select();
                    return;
                }
                createPlaylist(newName);
            };
            
            nameInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    document.getElementById('confirmCreateBtn').click();
                }
            });
            
            bg.onclick = function(e) {
                if (e.target === bg) {
                    closeModal();
                }
            };
            
            document.addEventListener('keydown', function escHandler(e) {
                if (e.key === 'Escape') {
                    closeModal();
                    document.removeEventListener('keydown', escHandler);
                }
            });
        }
        
        function createPlaylist(name) {
            fetch('libs/endpoints/UserData.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=playlist_create&playlist_name=${encodeURIComponent(name)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    playlists = data.playlists || playlists;
                    const bg = document.getElementById('createPlaylistModalBg');
                    if (bg) {
                        const modal = document.getElementById('createPlaylistModal');
                        if (modal) {
                            bg.style.opacity = '0';
                            modal.style.transform = 'scale(0.85) translateY(20px)';
                            modal.style.opacity = '0';
                            setTimeout(() => {
                                bg.remove();
                                hidePlaylistTooltip();
                                setTimeout(() => {
                                    showPlaylistTooltip();
                                }, 100);
                            }, 400);
                        } else {
                            bg.remove();
                            hidePlaylistTooltip();
                            setTimeout(() => {
                                showPlaylistTooltip();
                            }, 100);
                        }
                    }
                } else if (data.error === 'playlist_exists') {
                    alert('Ya existe una playlist con ese nombre. Por favor elige otro nombre.');
                    const nameInput = document.getElementById('newPlaylistNameInput');
                    if (nameInput) {
                        nameInput.focus();
                        nameInput.select();
                    }
                } else if (data.error === 'empty_name') {
                    alert('Por favor ingresa un nombre para la playlist');
                    const nameInput = document.getElementById('newPlaylistNameInput');
                    if (nameInput) {
                        nameInput.focus();
                    }
                }
            });
        }
        
        loadPlaylists().then(() => {
            updatePlaylistButtonState();
        });
        
        btnPlaylist.addEventListener('click', function(e) {
            e.stopPropagation();
            showPlaylistTooltip();
        });
        
        document.addEventListener('click', function(e) {
            if (isTooltipOpen && !tooltipContainer.contains(e.target) && !btnPlaylist.contains(e.target)) {
                hidePlaylistTooltip();
            }
        });
    });
})();
