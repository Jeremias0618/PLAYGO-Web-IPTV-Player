(function() {
    'use strict';

    function loadWikipediaInfo() {
        if (!window.channelWikipediaConfig) {
            return;
        }

        var canal = window.channelWikipediaConfig.canal;
        var loading = document.getElementById("infoCanalLoading");
        
        if (!loading) {
            return;
        }

        fetch("https://es.wikipedia.org/w/api.php?action=query&prop=extracts&explaintext=1&format=json&titles=" + encodeURIComponent(canal) + "&origin=*")
            .then(resp => resp.json())
            .then(data => {
                let pages = data.query.pages;
                let found = false;
                for (let key in pages) {
                    if (pages[key].extract && pages[key].extract.trim().length > 0) {
                        let parrafo = pages[key].extract.split('\n').find(p => p.trim().length > 0);
                        loading.innerText = parrafo ? parrafo : pages[key].extract;
                        found = true;
                        break;
                    }
                }
                if (!found) {
                    fetch("https://es.wikipedia.org/w/api.php?action=query&list=search&srsearch=" + encodeURIComponent(canal + " canal tv") + "&utf8=&format=json&origin=*")
                        .then(resp => resp.json())
                        .then(data2 => {
                            if(data2.query && data2.query.search && data2.query.search.length > 0) {
                                var pageTitle = data2.query.search[0].title;
                                fetch("https://es.wikipedia.org/w/api.php?action=query&prop=extracts&explaintext=1&format=json&titles=" + encodeURIComponent(pageTitle) + "&origin=*")
                                    .then(resp => resp.json())
                                    .then(data3 => {
                                        let pages2 = data3.query.pages;
                                        let found2 = false;
                                        for (let key2 in pages2) {
                                            if (pages2[key2].extract && pages2[key2].extract.trim().length > 0) {
                                                let parrafo2 = pages2[key2].extract.split('\n').find(p => p.trim().length > 0);
                                                loading.innerText = parrafo2 ? parrafo2 : pages2[key2].extract;
                                                found2 = true;
                                                break;
                                            }
                                        }
                                        if (!found2) {
                                            loading.innerText = "No se encontró información en internet.";
                                        }
                                    })
                                    .catch(() => {
                                        loading.innerText = "No se encontró información en internet.";
                                    });
                            } else {
                                loading.innerText = "No se encontró información en internet.";
                            }
                        })
                        .catch(() => {
                            loading.innerText = "No se encontró información en internet.";
                        });
                }
            })
            .catch(() => {
                loading.innerText = "No se encontró información en internet.";
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadWikipediaInfo);
    } else {
        loadWikipediaInfo();
    }
})();

