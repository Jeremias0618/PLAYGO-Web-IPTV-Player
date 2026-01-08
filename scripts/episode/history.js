document.addEventListener('DOMContentLoaded', function() {
    if (window.episodeHistoryData) {
        fetch('libs/endpoints/UserData.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=hist_add&id=${window.episodeHistoryData.serieId}&nombre=${encodeURIComponent(window.episodeHistoryData.serieName)}&img=${encodeURIComponent(window.episodeHistoryData.posterImg)}&backdrop=${encodeURIComponent(window.episodeHistoryData.backdrop)}&ano=${encodeURIComponent(window.episodeHistoryData.ano)}&rate=${encodeURIComponent(window.episodeHistoryData.rate)}&tipo=serie`
        });
    }
});

