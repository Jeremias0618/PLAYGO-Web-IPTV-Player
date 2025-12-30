<?php
if (!defined('PLAYGO_SEARCH_MODAL_LOADED')) {
    define('PLAYGO_SEARCH_MODAL_LOADED', true);

    if (!isset($user) || !isset($pwd)) {
        return;
    }

    if (!function_exists('playgo_render_search_modal')) {
        function playgo_render_search_modal($user, $pwd)
        {
            static $rendered = false;
            if ($rendered) {
                return;
            }
            $rendered = true;

            $moviesUrl = IP . "/player_api.php?username={$user}&password={$pwd}&action=get_vod_streams";
            $seriesUrl = IP . "/player_api.php?username={$user}&password={$pwd}&action=get_series";
            $channelsUrl = IP . "/player_api.php?username={$user}&password={$pwd}&action=get_live_streams";

            $moviesResponse = apixtream($moviesUrl);
            $seriesResponse = apixtream($seriesUrl);
            $channelsResponse = apixtream($channelsUrl);

            $movies = json_decode($moviesResponse, true) ?: [];
            $series = json_decode($seriesResponse, true) ?: [];
            $channels = json_decode($channelsResponse, true) ?: [];

            $moviesData = array_map(function ($item) {
                return [
                    'id' => $item['stream_id'] ?? null,
                    'nombre' => $item['name'] ?? '',
                    'img' => $item['stream_icon'] ?? ''
                ];
            }, $movies);

            $seriesData = array_map(function ($item) {
                return [
                    'id' => $item['series_id'] ?? null,
                    'nombre' => $item['name'] ?? '',
                    'img' => $item['cover'] ?? ''
                ];
            }, $series);

            $channelsData = array_map(function ($item) {
                return [
                    'id' => $item['stream_id'] ?? null,
                    'nombre' => $item['name'] ?? '',
                    'img' => $item['stream_icon'] ?? '',
                    'tipo' => $item['stream_type'] ?? ''
                ];
            }, $channels);

            $moviesJson = json_encode($moviesData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $seriesJson = json_encode($seriesData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $channelsJson = json_encode($channelsData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            ?>
            <style id="playgo-search-modal-style">
                .modal-buscador-bg {
                    display: none;
                    position: fixed;
                    z-index: 9999;
                    left: 0; top: 0;
                    width: 100vw; height: 100vh;
                    background: linear-gradient(135deg, rgba(15,32,39,0.95) 0%, rgba(44,83,100,0.95) 100%);
                    align-items: center;
                    justify-content: center;
                    backdrop-filter: blur(10px);
                    -webkit-backdrop-filter: blur(10px);
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }
                .modal-buscador-bg.active {
                    display: flex;
                    opacity: 1;
                }
                .modal-buscador {
                    background: linear-gradient(145deg, #1a1a1a 0%, #2d2d2d 100%);
                    border-radius: 20px;
                    padding: 0;
                    max-width: 800px;
                    width: 90vw;
                    max-height: 85vh;
                    overflow: hidden;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.8);
                    position: relative;
                    transform: scale(0.9);
                    transition: transform 0.3s ease;
                    border: 1px solid rgba(229,9,20,0.3);
                }
                .modal-buscador-bg.active .modal-buscador {
                    transform: scale(1);
                }
                .modal-buscador-header {
                    background: linear-gradient(90deg, #e50914 0%, #c8008f 100%);
                    padding: 20px 24px;
                    border-radius: 20px 20px 0 0;
                    position: relative;
                    overflow: hidden;
                }
                .modal-buscador-header::before {
                    content: '';
                    position: absolute;
                    top: 0; left: 0; right: 0; bottom: 0;
                    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
                    opacity: 0.3;
                }
                .modal-buscador-title {
                    color: #fff;
                    font-size: 1.4rem;
                    font-weight: 700;
                    margin: 0;
                    text-align: center;
                    position: relative;
                    z-index: 1;
                }
                .modal-buscador-close {
                    position: absolute;
                    top: 20px;
                    right: 24px;
                    width: 36px;
                    height: 36px;
                    background: rgba(255,255,255,0.2);
                    border: none;
                    border-radius: 50%;
                    color: #fff;
                    font-size: 1.2rem;
                    cursor: pointer;
                    z-index: 2;
                    transition: all 0.2s ease;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .modal-buscador-close:hover {
                    background: rgba(255,255,255,0.3);
                    transform: scale(1.1);
                }
                .modal-buscador-body {
                    padding: 24px;
                    max-height: calc(85vh - 100px);
                    overflow-y: auto;
                }
                .modal-buscador-inputbox {
                    display: flex;
                    align-items: center;
                    margin-bottom: 24px;
                    background: rgba(255,255,255,0.05);
                    border-radius: 16px;
                    padding: 4px;
                    border: 1px solid rgba(255,255,255,0.1);
                }
                .modal-buscador-inputbox input {
                    flex: 1;
                    background: transparent;
                    border: none;
                    color: #fff;
                    border-radius: 12px;
                    padding: 16px 20px;
                    font-size: 1.1rem;
                    margin: 0;
                    outline: none;
                }
                .modal-buscador-inputbox input::placeholder {
                    color: rgba(255,255,255,0.6);
                }
                .modal-buscador-inputbox button {
                    background: linear-gradient(90deg, #e50914 0%, #c8008f 100%);
                    border: none;
                    color: #fff;
                    border-radius: 12px;
                    padding: 16px 24px;
                    font-size: 1rem;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    margin-left: 4px;
                    white-space: nowrap;
                }
                .modal-buscador-inputbox button:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 20px rgba(229,9,20,0.4);
                }
                .modal-buscador-inputbox button:active {
                    transform: translateY(0);
                }
                .modal-buscador-section {
                    margin-bottom: 32px;
                }
                .modal-buscador-section h3 {
                    color: #e50914;
                    font-size: 1.2rem;
                    margin-bottom: 20px;
                    margin-top: 0;
                    font-weight: 700;
                    text-align: left;
                    padding-left: 8px;
                    border-left: 4px solid #e50914;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                .modal-buscador-section h3::before {
                    content: '';
                    width: 8px;
                    height: 8px;
                    background: #e50914;
                    border-radius: 50%;
                }
                .modal-buscador-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                    gap: 16px;
                    justify-content: start;
                }
                .modal-buscador-card {
                    text-align: center;
                    transition: transform 0.2s ease;
                    animation: fadeInUp 0.4s ease forwards;
                    opacity: 0;
                    transform: translateY(20px);
                }
                .modal-buscador-card:hover {
                    transform: translateY(-4px);
                }
                .modal-buscador-card:nth-child(1) { animation-delay: 0.1s; }
                .modal-buscador-card:nth-child(2) { animation-delay: 0.15s; }
                .modal-buscador-card:nth-child(3) { animation-delay: 0.2s; }
                .modal-buscador-card:nth-child(4) { animation-delay: 0.25s; }
                .modal-buscador-card:nth-child(5) { animation-delay: 0.3s; }
                .modal-buscador-card:nth-child(6) { animation-delay: 0.35s; }
                .modal-buscador-card a {
                    text-decoration: none;
                    color: inherit;
                    display: block;
                }
                .modal-buscador-card img {
                    width: 100%;
                    height: 160px;
                    object-fit: cover;
                    border-radius: 12px;
                    background: #232027;
                    margin-bottom: 12px;
                    box-shadow: 0 4px 16px rgba(0,0,0,0.3);
                    transition: all 0.2s ease;
                    border: 2px solid transparent;
                }
                .modal-buscador-card:hover img {
                    border-color: #e50914;
                    box-shadow: 0 8px 24px rgba(229,9,20,0.3);
                }
                .modal-buscador-card span {
                    color: #fff;
                    font-size: 0.9rem;
                    display: block;
                    font-weight: 500;
                    line-height: 1.3;
                    padding: 0 4px;
                    word-break: break-word;
                }
                .modal-buscador-empty {
                    text-align: center;
                    padding: 40px 20px;
                    color: rgba(255,255,255,0.7);
                }
                .modal-buscador-empty i {
                    font-size: 3rem;
                    margin-bottom: 16px;
                    opacity: 0.5;
                }
                .modal-buscador-empty p {
                    font-size: 1.1rem;
                    margin: 0;
                }
                .modal-buscador-filters {
                    display: flex;
                    gap: 12px;
                    margin-bottom: 20px;
                    flex-wrap: wrap;
                }
                .filter-btn {
                    background: rgba(255,255,255,0.1);
                    border: 1px solid rgba(255,255,255,0.2);
                    color: #fff;
                    padding: 8px 16px;
                    border-radius: 20px;
                    font-size: 0.9rem;
                    cursor: pointer;
                    transition: all 0.2s ease;
                }
                .filter-btn.active {
                    background: #e50914;
                    border-color: #e50914;
                }
                .filter-btn:hover {
                    background: rgba(255,255,255,0.2);
                }
                .filter-btn.active:hover {
                    background: #c8008f;
                }
                @media (min-width: 1200px) {
                    .modal-buscador {
                        max-width: 700px !important;
                        width: 700px !important;
                    }
                }
                @media (min-width: 768px) and (max-width: 1199px) {
                    .modal-buscador {
                        max-width: 750px !important;
                        width: 85vw !important;
                    }
                }
                @media (max-width: 600px) {
                    .modal-buscador {
                        width: 98vw !important;
                        max-width: 98vw !important;
                        border-radius: 16px !important;
                        margin: 10px;
                        padding: 18px 6px 12px 6px;
                    }
                    .modal-buscador-header {
                        padding: 16px 20px;
                        border-radius: 16px 16px 0 0;
                    }
                    .modal-buscador-title {
                        font-size: 1.2rem;
                    }
                    .modal-buscador-body {
                        padding: 20px 16px;
                    }
                    .modal-buscador-inputbox {
                        flex-direction: column;
                        gap: 12px;
                        padding: 8px;
                    }
                    .modal-buscador-inputbox input {
                        width: 100%;
                        font-size: 1rem;
                        padding: 14px 16px;
                    }
                    .modal-buscador-inputbox button {
                        width: 100%;
                        padding: 14px 20px;
                        font-size: 1rem;
                    }
                    .modal-buscador-filters {
                        justify-content: center;
                        gap: 8px;
                    }
                    .filter-btn {
                        padding: 10px 14px;
                        font-size: 0.9rem;
                        min-width: 80px;
                    }
                    .modal-buscador-grid {
                        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
                        gap: 12px;
                    }
                    .modal-buscador-card {
                        width: 100%;
                    }
                    .modal-buscador-card img {
                        height: 140px;
                    }
                    .modal-buscador-card span {
                        font-size: 0.85rem;
                        line-height: 1.2;
                    }
                    .modal-buscador-close {
                        top: 16px;
                        right: 16px;
                        width: 32px;
                        height: 32px;
                        font-size: 1rem;
                    }
                }
                @keyframes fadeInUp {
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                .modal-buscador-loading {
                    text-align: center;
                    padding: 40px 20px;
                    color: rgba(255,255,255,0.7);
                }
                .modal-buscador-loading i {
                    font-size: 2rem;
                    margin-bottom: 16px;
                    animation: spin 1s linear infinite;
                }
                @keyframes spin {
                    from { transform: rotate(0deg); }
                    to { transform: rotate(360deg); }
                }
                body.modal-open,
                html.modal-open {
                    overflow: hidden !important;
                    height: 100%;
                }
            </style>
            <div class="modal-buscador-bg" id="modalBuscador">
                <div class="modal-buscador">
                    <div class="modal-buscador-header">
                        <h2 class="modal-buscador-title">
                            <i class="fas fa-search"></i> Buscador PLAYGO
                        </h2>
                        <button class="modal-buscador-close" id="closeSearchModal" title="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-buscador-body">
                        <form id="modalBuscadorForm" autocomplete="off" onsubmit="return false;">
                            <div class="modal-buscador-inputbox">
                                <input type="text" id="modalBuscadorInput" placeholder="Buscar películas, series o canales..." autofocus>
                                <button type="button" id="modalBuscadorBtn">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </form>
                        <div class="modal-buscador-filters" id="searchFilters">
                            <button class="filter-btn active" data-filter="all">
                                <i class="fas fa-th-large"></i> Todo
                            </button>
                            <button class="filter-btn" data-filter="movies">
                                <i class="fas fa-film"></i> Películas
                            </button>
                            <button class="filter-btn" data-filter="series">
                                <i class="fas fa-tv"></i> Series
                            </button>
                            <button class="filter-btn" data-filter="channels">
                                <i class="fas fa-broadcast-tower"></i> TV
                            </button>
                        </div>
                        <div id="modalBuscadorResults"></div>
                    </div>
                </div>
            </div>
            <script>
            (function() {
                const peliculas = <?php echo $moviesJson; ?>;
                const series = <?php echo $seriesJson; ?>;
                const canales = <?php echo $channelsJson; ?>;

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
                    'future': ['futuro'],
                    'planet': ['planeta'],
                    'galaxy': ['galaxia'],
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
                    'eternity': ['eternidad'],
                    'battle': ['batalla'],
                    'battles': ['batallas'],
                    'fight': ['pelea'],
                    'fighter': ['luchador'],
                    'fighters': ['luchadores'],
                    'ring': ['anillo'],
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
                                    <a href="filme.php?stream=${p.id}&streamtipo=movie">
                                        <img src="${p.img}" alt="${p.nombre}" onerror="this.src='assets/image/logo.svg'">
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
                                        <img src="${s.img}" alt="${s.nombre}" onerror="this.src='assets/image/logo.svg'">
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
                                    <a href="canal.php?stream=${c.id}">
                                        <img src="${c.img}" alt="${c.nombre}" onerror="this.src='assets/image/logo.svg'">
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
            </script>
            <?php
        }
    }

    playgo_render_search_modal($user, $pwd);
}

