(function() {
    function initChannelNavigation() {
        const canalesGrid = document.querySelector('.canales-grid');
        if (!canalesGrid || window.innerWidth > 600) {
            return;
        }

        canalesGrid.querySelectorAll('.canal-title').forEach(span => {
            const card = span.closest('.canal-card');
            if (!card) return;
            const link = card.querySelector('.canal-play');
            if (!link) return;
            span.style.cursor = 'pointer';
            span.onclick = function() {
                window.location = link.href;
            };
        });

        canalesGrid.querySelectorAll('.canal-card').forEach(card => {
            const link = card.querySelector('.canal-play');
            if (!link) return;
            card.onclick = function(e) {
                if (e.target.classList.contains('canal-title')) return;
                window.location = link.href;
            };
        });

        canalesGrid.querySelectorAll('.canal-play').forEach(link => {
            const href = link.getAttribute('href');
            const match = href.match(/stream=(\d+)/);
            if (match) {
                link.setAttribute('href', 'canal.php?stream=' + match[1]);
            }
        });
    }

    if (typeof window !== 'undefined') {
        window.ChannelNavigation = initChannelNavigation;
    }
})();

