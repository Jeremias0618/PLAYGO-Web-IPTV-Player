(function() {
    'use strict';

    function isIOS() {
        return /iPad|iPhone|iPod/.test(navigator.userAgent) || 
               (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    }

    function isSafariIOS() {
        return isIOS() && /Safari/.test(navigator.userAgent) && !/CriOS|FxiOS|OPiOS|mercury/.test(navigator.userAgent);
    }

    function getIOSVersion() {
        var match = navigator.userAgent.match(/OS (\d+)_(\d+)_?(\d+)?/);
        if (match) {
            return parseInt(match[1]);
        }
        return 0;
    }

    function showManualPlayButton() {
        var videoElement = document.getElementById('livevideo');
        if (!videoElement || videoElement.querySelector('.ios-video-controls')) {
            return;
        }
        
        var controls = document.createElement('div');
        controls.className = 'ios-video-controls';
        controls.innerHTML = `
            <button class="ios-play-button" onclick="window.channelPlayerPlayVideo()">
                <i class="fas fa-play"></i>
            </button>
        `;
        videoElement.appendChild(controls);
    }

    window.channelPlayerPlayVideo = function() {
        var video = document.getElementById('nativeVideo') || document.getElementById('fallbackVideo');
        if (video) {
            video.play().catch(function(e) {
                console.log('Play manual falló:', e);
            });
        }
        var controls = document.querySelector('.ios-video-controls');
        if (controls) controls.remove();
    };

    function tryHLSJS(streamUrl) {
        var hlsScript = document.createElement('script');
        hlsScript.src = './scripts/vendors/hls.min.js';
        hlsScript.onload = function() {
            if (typeof Hls !== 'undefined' && Hls.isSupported()) {
                var video = document.getElementById('nativeVideo');
                if (!video) return;
                
                var hls = new Hls({
                    enableWorker: true,
                    lowLatencyMode: true,
                    backBufferLength: 90,
                    maxBufferLength: 30,
                    maxMaxBufferLength: 600,
                    maxBufferSize: 60 * 1000 * 1000,
                    maxBufferHole: 0.5,
                    highBufferWatchdogPeriod: 2,
                    nudgeOffset: 0.2,
                    nudgeMaxRetry: 5,
                    maxFragLookUpTolerance: 0.25,
                    liveSyncDurationCount: 3,
                    liveMaxLatencyDurationCount: 10
                });
                
                hls.loadSource(streamUrl);
                hls.attachMedia(video);
                
                hls.on(Hls.Events.MANIFEST_PARSED, function() {
                    video.play().catch(function(e) {
                        showManualPlayButton();
                    });
                });
                
                hls.on(Hls.Events.ERROR, function(event, data) {
                    if (data.fatal) {
                        fallbackToJWPlayer(streamUrl, window.channelPlayerConfig?.posterImage || '');
                    }
                });
            } else {
                fallbackToJWPlayer(streamUrl, window.channelPlayerConfig?.posterImage || '');
            }
        };
        
        hlsScript.onerror = function() {
            fallbackToJWPlayer(streamUrl, window.channelPlayerConfig?.posterImage || '');
        };
        
        document.head.appendChild(hlsScript);
    }

    function fallbackToJWPlayer(streamUrl, posterImage) {
        var videoElement = document.getElementById('livevideo');
        if (!videoElement) return;
        
        videoElement.innerHTML = '<div id="jwplayerContainer"></div>';
        
        if (typeof jwplayer === 'undefined') {
            return;
        }
        
        jwplayer.key = "";
        jwplayer("jwplayerContainer").setup({
            file: streamUrl,
            image: posterImage,
            width: "100%",
            aspectratio: "16:9",
            autostart: true,
            mute: false,
            stretching: "fill",
            hlshtml: true,
            primary: "html5",
            fallback: true,
            preload: "metadata",
            hls: {
                lowLatencyMode: true,
                backBufferLength: 90
            }
        });
    }

    function setupJWPlayer(streamUrl, posterImage) {
        var videoElement = document.getElementById('livevideo');
        if (!videoElement || typeof jwplayer === 'undefined') {
            return;
        }
        
        jwplayer.key = "";
        jwplayer("livevideo").setup({
            file: streamUrl,
            image: posterImage,
            width: "100%",
            aspectratio: "16:9",
            autostart: true,
            mute: false,
            stretching: "fill",
            hlshtml: true,
            primary: "html5",
            fallback: true,
            preload: "metadata",
            hls: {
                lowLatencyMode: true,
                backBufferLength: 90
            }
        });

        jwplayer("livevideo").on('error', function(e) {
            if (e.code === 101104) {
                var videoElement = document.getElementById('livevideo');
                videoElement.innerHTML = `
                    <video 
                        id="fallbackVideo" 
                        controls 
                        autoplay 
                        muted 
                        playsinline 
                        webkit-playsinline
                        style="width: 100%; height: 100%; background: #000;"
                        poster="${posterImage}"
                    >
                        <source src="${streamUrl}" type="application/x-mpegURL">
                        Tu navegador no soporta el elemento de video.
                    </video>
                `;
                
                var fallbackVideo = document.getElementById('fallbackVideo');
                if (fallbackVideo) {
                    fallbackVideo.play().catch(function(error) {
                        console.log('Fallback video autoplay falló:', error);
                    });
                }
            }
        });
    }

    function initPlayer() {
        if (!window.channelPlayerConfig) {
            return;
        }

        var streamUrl = window.channelPlayerConfig.streamUrl;
        var posterImage = window.channelPlayerConfig.posterImage;
        var iosVersion = getIOSVersion();

        if (isIOS()) {
            var videoElement = document.getElementById('livevideo');
            if (!videoElement) return;
            
            videoElement.innerHTML = `
                <video 
                    id="nativeVideo" 
                    controls 
                    autoplay 
                    muted 
                    playsinline 
                    webkit-playsinline
                    x-webkit-airplay="allow"
                    style="width: 100%; height: 100%; background: #000;"
                    poster="${posterImage}"
                    preload="metadata"
                >
                    <source src="${streamUrl}" type="application/x-mpegURL">
                    Tu navegador no soporta el elemento de video.
                </video>
            `;

            var video = document.getElementById('nativeVideo');
            if (!video) return;
            
            if (iosVersion >= 10) {
                video.setAttribute('webkit-playsinline', 'true');
                video.setAttribute('playsinline', 'true');
            }
            
            video.addEventListener('loadedmetadata', function() {
                video.play().catch(function(error) {
                    video.muted = true;
                    video.play().catch(function(e) {
                        showManualPlayButton();
                    });
                });
            });

            video.addEventListener('error', function(e) {
                var errorCode = video.error ? video.error.code : 'unknown';
                
                if (errorCode === 4 || errorCode === 'MEDIA_ELEMENT_ERROR') {
                    tryHLSJS(streamUrl);
                } else {
                    fallbackToJWPlayer(streamUrl, posterImage);
                }
            });

            if (isSafariIOS()) {
                tryHLSJS(streamUrl);
            }
        } else {
            setupJWPlayer(streamUrl, posterImage);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPlayer);
    } else {
        initPlayer();
    }
})();

