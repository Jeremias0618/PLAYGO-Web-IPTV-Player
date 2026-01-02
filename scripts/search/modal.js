(function() {
    'use strict';

    if (!window.PLAYGO_SEARCH_DATA) {
        return;
    }

    const peliculas = window.PLAYGO_SEARCH_DATA.peliculas || [];
    const series = window.PLAYGO_SEARCH_DATA.series || [];
    const canales = window.PLAYGO_SEARCH_DATA.canales || [];

    const openSearchModal = document.getElementById('openSearchModal');
    const closeSearchModal = document.getElementById('closeSearchModal');
    const modalBuscador = document.getElementById('modalBuscador');
    const modalBuscadorInput = document.getElementById('modalBuscadorInput');
    const modalBuscadorBtn = document.getElementById('modalBuscadorBtn');
    const modalBuscadorResults = document.getElementById('modalBuscadorResults');
    const searchFilters = document.getElementById('searchFilters');

    if (!openSearchModal || !closeSearchModal || !modalBuscador || !modalBuscadorInput || !modalBuscadorResults || !searchFilters) {
        return;
    }

    let currentFilter = 'all';
    let searchTimeout;

    function showModalBuscador() {
        modalBuscador.classList.add('active');
        document.body.classList.add('modal-open');
        document.documentElement.classList.add('modal-open');
        setTimeout(() => {
            modalBuscadorInput.focus();
            modalBuscadorInput.select();
        }, 300);
    }

    function hideModalBuscador() {
        modalBuscador.classList.remove('active');
        document.body.classList.remove('modal-open');
        document.documentElement.classList.remove('modal-open');
        setTimeout(() => {
            modalBuscadorInput.value = '';
            modalBuscadorResults.innerHTML = '';
            resetFilters();
        }, 300);
    }

    function resetFilters() {
        currentFilter = 'all';
        searchFilters.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        const allBtn = searchFilters.querySelector('[data-filter="all"]');
        if (allBtn) {
            allBtn.classList.add('active');
        }
    }

    function normalizarTexto(texto) {
        return (texto || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    const baseTokenTranslations = {
        'spider': ['arana'],
        'man': ['hombre', 'varon'],
        'men': ['hombres'],
        'woman': ['mujer'],
        'women': ['mujeres'],
        'girl': ['chica', 'nina'],
        'boy': ['chico', 'nino'],
        'kid': ['nino', 'nina'],
        'kids': ['ninos', 'ninas'],
        'child': ['nino', 'nina'],
        'children': ['ninos', 'ninas'],
        'hero': ['heroe'],
        'heroes': ['heroes'],
        'villain': ['villano'],
        'villains': ['villanos'],
        'king': ['rey'],
        'queen': ['reina'],
        'lord': ['senor'],
        'lady': ['dama', 'senora'],
        'ring': ['anillo'],
        'rings': ['anillos'],
        'throne': ['trono'],
        'thrones': ['tronos'],
        'game': ['juego'],
        'games': ['juegos'],
        'house': ['casa', 'hogar'],
        'home': ['casa', 'hogar'],
        'war': ['guerra'],
        'world': ['mundo'],
        'planet': ['planeta'],
        'star': ['estrella'],
        'stars': ['estrellas'],
        'galaxy': ['galaxia'],
        'guardians': ['guardianes'],
        'guardian': ['guardian'],
        'fast': ['rapido', 'rapidos'],
        'furious': ['furioso', 'furiosos'],
        'black': ['negro', 'negra', 'negros', 'negras'],
        'white': ['blanco', 'blanca', 'blancos', 'blancas'],
        'red': ['rojo', 'roja', 'rojos', 'rojas'],
        'blue': ['azul', 'azules'],
        'green': ['verde', 'verdes'],
        'yellow': ['amarillo', 'amarilla', 'amarillos', 'amarillas'],
        'purple': ['morado', 'morada', 'morados', 'moradas'],
        'pink': ['rosa', 'rosado', 'rosada'],
        'night': ['noche'],
        'day': ['dia'],
        'light': ['luz'],
        'dark': ['oscuro', 'oscura', 'oscuros', 'oscuras'],
        'shadow': ['sombra'],
        'shadows': ['sombras'],
        'sun': ['sol'],
        'moon': ['luna'],
        'sea': ['mar'],
        'ocean': ['oceano'],
        'pirate': ['pirata'],
        'pirates': ['piratas'],
        'caribbean': ['caribe'],
        'captain': ['capitan'],
        'america': ['america'],
        'iron': ['hierro'],
        'panther': ['pantera'],
        'witch': ['bruja'],
        'witches': ['brujas'],
        'wizard': ['mago'],
        'wizards': ['magos'],
        'witcher': ['brujo'],
        'devil': ['diablo'],
        'angel': ['angel'],
        'angels': ['angeles'],
        'demon': ['demonio'],
        'demons': ['demonios'],
        'dragon': ['dragon'],
        'dragons': ['dragones'],
        'fire': ['fuego'],
        'ice': ['hielo'],
        'blood': ['sangre'],
        'stone': ['piedra'],
        'stones': ['piedras'],
        'sword': ['espada'],
        'swords': ['espadas'],
        'shield': ['escudo'],
        'shields': ['escudos'],
        'love': ['amor'],
        'hate': ['odio'],
        'death': ['muerte'],
        'dead': ['muerto', 'muerta', 'muertos', 'muertas'],
        'alive': ['vivo', 'viva', 'vivos', 'vivas'],
        'life': ['vida'],
        'future': ['futuro'],
        'past': ['pasado'],
        'present': ['presente'],
        'legend': ['leyenda'],
        'legends': ['leyendas'],
        'hunt': ['caza'],
        'hunter': ['cazador'],
        'hunters': ['cazadores'],
        'ghost': ['fantasma'],
        'ghosts': ['fantasmas'],
        'spirit': ['espiritu'],
        'spirits': ['espiritus'],
        'dream': ['sueno'],
        'dreams': ['suenos'],
        'judge': ['juez'],
        'justice': ['justicia'],
        'league': ['liga'],
        'thief': ['ladron'],
        'thieves': ['ladrones'],
        'heist': ['atraco', 'robo'],
        'money': ['dinero'],
        'paper': ['papel'],
        'train': ['tren'],
        'bus': ['autobus'],
        'car': ['auto', 'carro', 'coche'],
        'cars': ['autos', 'carros', 'coches'],
        'road': ['carretera'],
        'street': ['calle'],
        'city': ['ciudad'],
        'town': ['pueblo'],
        'village': ['aldea'],
        'forest': ['bosque'],
        'mountain': ['montana'],
        'mountains': ['montanas'],
        'river': ['rio'],
        'valley': ['valle'],
        'desert': ['desierto'],
        'island': ['isla'],
        'islands': ['islas'],
        'kingdom': ['reino'],
        'empire': ['imperio'],
        'rebel': ['rebelde'],
        'rebels': ['rebeldes'],
        'revolution': ['revolucion'],
        'freedom': ['libertad'],
        'universe': ['universo'],
        'space': ['espacio'],
        'alien': ['alienigena'],
        'aliens': ['alienigenas'],
        'robot': ['robot'],
        'robots': ['robots'],
        'machine': ['maquina'],
        'machines': ['maquinas'],
        'code': ['codigo'],
        'matrix': ['matriz'],
        'mission': ['mision'],
        'impossible': ['imposible'],
        'spy': ['espia'],
        'agent': ['agente'],
        'agents': ['agentes'],
        'police': ['policia'],
        'cop': ['policia'],
        'detective': ['detective'],
        'case': ['caso'],
        'files': ['expedientes'],
        'story': ['historia'],
        'stories': ['historias'],
        'chapter': ['capitulo'],
        'chapters': ['capitulos'],
        'season': ['temporada'],
        'seasons': ['temporadas'],
        'episode': ['episodio'],
        'episodes': ['episodios'],
        'part': ['parte'],
        'parts': ['partes'],
        'return': ['regreso'],
        'rise': ['ascenso'],
        'fall': ['caida'],
        'awakening': ['despertar'],
        'revenge': ['venganza'],
        'avenger': ['vengador'],
        'avengers': ['vengadores'],
        'soldier': ['soldado'],
        'soldiers': ['soldados'],
        'warrior': ['guerrero'],
        'warriors': ['guerreros'],
        'legendary': ['legendario', 'legendaria'],
        'eternal': ['eterno', 'eterna'],
        'eternals': ['eternos', 'eternas'],
        'eternity': ['eternidad'],
        'clown': ['payaso'],
        'bear': ['oso'],
        'wolf': ['lobo'],
        'wolves': ['lobos'],
        'lion': ['leon'],
        'lions': ['leones'],
        'tiger': ['tigre'],
        'tigers': ['tigres'],
        'shark': ['tiburon'],
        'sharks': ['tiburones'],
        'monster': ['monstruo'],
        'monsters': ['monstruos'],
        'giant': ['gigante'],
        'giants': ['gigantes'],
        'tiny': ['pequeno', 'pequena', 'pequenos', 'pequenas'],
        'small': ['pequeno', 'pequena'],
        'big': ['grande'],
        'great': ['gran', 'grande'],
        'incredible': ['increible'],
        'amazing': ['asombroso', 'asombrosa'],
        'fantastic': ['fantastico', 'fantastica'],
        'infinity': ['infinito', 'infinita'],
        'battle': ['batalla'],
        'battles': ['batallas'],
        'fight': ['pelea'],
        'fighter': ['luchador'],
        'fighters': ['luchadores'],
        'guard': ['guardia'],
        'guards': ['guardias']
    };

    const rawTokenTranslations = Object.assign(
        {},
        baseTokenTranslations,
        (typeof window !== 'undefined' && window.PLAYGO_SEARCH_DICTIONARY) || {}
    );

    function buildTokenSynonymMap(rawMap) {
        const map = {};
        Object.entries(rawMap).forEach(([key, values]) => {
            const normalizedKey = normalizarTexto(key);
            if (!normalizedKey) {
                return;
            }
            const normalizedValues = (Array.isArray(values) ? values : [values])
                .map(value => normalizarTexto(value))
                .filter(Boolean);

            if (!map[normalizedKey]) {
                map[normalizedKey] = new Set();
            }

            normalizedValues.forEach(val => {
                if (val !== normalizedKey) {
                    map[normalizedKey].add(val);
                }
                if (!map[val]) {
                    map[val] = new Set();
                }
                if (val !== normalizedKey) {
                    map[val].add(normalizedKey);
                }
            });
        });

        return Object.fromEntries(
            Object.entries(map).map(([token, set]) => [token, Array.from(set)])
        );
    }

    const tokenSynonymMap = buildTokenSynonymMap(rawTokenTranslations);

    function getTokenVariants(token, enableSynonyms) {
        const normalizedToken = normalizarTexto(token);
        if (!normalizedToken) {
            return [];
        }
        const variants = new Set([normalizedToken]);
        if (enableSynonyms && tokenSynonymMap[normalizedToken]) {
            tokenSynonymMap[normalizedToken].forEach(variant => variants.add(variant));
        }
        return Array.from(variants);
    }

    function buildSearchTokens(nombre) {
        if (!nombre) {
            return [];
        }
        const tokens = normalizarTexto(nombre)
            .split(' ')
            .map(token => token.trim())
            .filter(token => token.length >= 3);

        const result = new Set(tokens);
        tokens.forEach(token => {
            (tokenSynonymMap[token] || []).forEach(variant => {
                if (variant.length >= 3) {
                    result.add(variant);
                }
            });
        });

        return Array.from(result);
    }

    function enrichSearchItems(items) {
        return items.map(item => {
            const nombre = item.nombre || '';
            const nombreLower = nombre.toLowerCase();
            const nombreNormalizado = normalizarTexto(nombre);
            const searchTokens = buildSearchTokens(nombre);
            return Object.assign({}, item, {
                nombreLower,
                nombreNormalizado,
                searchTokens,
                searchTokenSet: new Set(searchTokens)
            });
        });
    }

    const peliculasIndex = enrichSearchItems(peliculas || []);
    const seriesIndex = enrichSearchItems(series || []);
    const canalesIndex = enrichSearchItems(canales || []);

    function matchesSearchItem(item, queryLower, queryNormalizado, queryTokenVariants) {
        if (!item || !item.nombre) {
            return false;
        }

        if (queryLower && item.nombreLower.includes(queryLower)) {
            return true;
        }

        if (queryNormalizado && item.nombreNormalizado.includes(queryNormalizado)) {
            return true;
        }

        if (!queryTokenVariants.length) {
            return false;
        }

        const tokenSet = item.searchTokenSet;
        return queryTokenVariants.every(variants =>
            variants.some(variant => tokenSet.has(variant))
        );
    }

    function renderBuscadorResults(query) {
        query = (query || '').trim();
        const queryNormalizado = normalizarTexto(query);
        let html = '';
        let totalResults = 0;
        const queryLower = query.toLowerCase();
        const queryTokens = queryNormalizado
            .split(' ')
            .map(token => token.trim())
            .filter(token => token.length >= 3);
        const enableSynonyms = queryTokens.some(token => token.length >= 4);
        const queryTokenVariants = queryTokens.map(token => getTokenVariants(token, enableSynonyms));

        const showMovies = currentFilter === 'all' || currentFilter === 'movies';
        const showSeries = currentFilter === 'all' || currentFilter === 'series';
        const showChannels = currentFilter === 'all' || currentFilter === 'channels';

        if (showMovies) {
            const pelis = peliculasIndex.filter(p => matchesSearchItem(p, queryLower, queryNormalizado, queryTokenVariants));
            if (pelis.length > 0) {
                html += `<div class="modal-buscador-section">
                    <h3><i class="fas fa-film"></i> PELÍCULAS (${pelis.length})</h3>
                    <div class="modal-buscador-grid">`;
                pelis.slice(0, 12).forEach(p => {
                    html += `<div class="modal-buscador-card">
                        <a href="movie.php?stream=${p.id}&streamtipo=movie">
                            <img src="${p.img}" alt="${p.nombre}" onerror="this.src='assets/logo/logo.png'">
                            <span>${p.nombre}</span>
                        </a>
                    </div>`;
                });
                html += `</div></div>`;
                totalResults += pelis.length;
            }
        }

        if (showSeries) {
            const sers = seriesIndex.filter(s => matchesSearchItem(s, queryLower, queryNormalizado, queryTokenVariants));
            if (sers.length > 0) {
                html += `<div class="modal-buscador-section">
                    <h3><i class="fas fa-tv"></i> SERIES (${sers.length})</h3>
                    <div class="modal-buscador-grid">`;
                sers.slice(0, 12).forEach(s => {
                    html += `<div class="modal-buscador-card">
                        <a href="serie.php?stream=${s.id}&streamtipo=serie">
                            <img src="${s.img}" alt="${s.nombre}" onerror="this.src='assets/logo/logo.png'">
                            <span>${s.nombre}</span>
                        </a>
                    </div>`;
                });
                html += `</div></div>`;
                totalResults += sers.length;
            }
        }

        if (showChannels) {
            const chans = canalesIndex.filter(c => matchesSearchItem(c, queryLower, queryNormalizado, queryTokenVariants));
            if (chans.length > 0) {
                html += `<div class="modal-buscador-section">
                    <h3><i class="fas fa-broadcast-tower"></i> TV EN VIVO (${chans.length})</h3>
                    <div class="modal-buscador-grid">`;
                chans.slice(0, 12).forEach(c => {
                    html += `<div class="modal-buscador-card">
                        <a href="channel.php?stream=${c.id}">
                            <img src="${c.img}" alt="${c.nombre}" onerror="this.src='assets/logo/logo.png'">
                            <span>${c.nombre}</span>
                        </a>
                    </div>`;
                });
                html += `</div></div>`;
                totalResults += chans.length;
            }
        }

        if (!html && query.length > 0) {
            html = `<div class="modal-buscador-empty">
                <i class="fas fa-search"></i>
                <p>No se encontraron resultados para "${query}"</p>
                <p style="font-size: 0.9rem; margin-top: 8px;">Intenta con otros términos o cambia el filtro</p>
            </div>`;
        } else if (query.length > 0) {
            html = `<div style="text-align: center; margin-bottom: 20px; color: rgba(255,255,255,0.7);">
                <i class="fas fa-info-circle"></i> Se encontraron ${totalResults} resultados
            </div>` + html;
        }

        modalBuscadorResults.innerHTML = html;
    }

    openSearchModal.addEventListener('click', showModalBuscador);
    closeSearchModal.addEventListener('click', hideModalBuscador);

    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideModalBuscador();
        }
    });

    modalBuscador.addEventListener('click', function(e) {
        if (e.target === modalBuscador) {
            hideModalBuscador();
        }
    });

    searchFilters.addEventListener('click', function(e) {
        const target = e.target.closest('.filter-btn');
        if (!target) {
            return;
        }
        searchFilters.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        target.classList.add('active');
        currentFilter = target.getAttribute('data-filter');
        const query = modalBuscadorInput.value.trim();
        if (query.length > 1) {
            renderBuscadorResults(query);
        }
    });

    modalBuscadorInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const q = this.value;

        if (q.length > 1) {
            searchTimeout = setTimeout(() => {
                renderBuscadorResults(q);
            }, 300);
        } else {
            modalBuscadorResults.innerHTML = '';
        }
    });

    modalBuscadorBtn.addEventListener('click', function() {
        const q = modalBuscadorInput.value;
        if (q.length > 1) {
            renderBuscadorResults(q);
        }
    });

    modalBuscadorInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const q = modalBuscadorInput.value;
            if (q.length > 1) {
                renderBuscadorResults(q);
            }
        }
    });
})();

