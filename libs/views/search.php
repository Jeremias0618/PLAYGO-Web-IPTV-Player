<?php
if (!defined('PLAYGO_SEARCH_MODAL_LOADED')) {
    define('PLAYGO_SEARCH_MODAL_LOADED', true);

    if (!function_exists('playgo_render_search_modal')) {
        function playgo_render_search_modal($user, $pwd) {
            static $rendered = false;
            if ($rendered) {
                return;
            }
            $rendered = true;

            if (!function_exists('getSearchData')) {
                require_once(__DIR__ . '/../controllers/Search.php');
            }

            $searchData = getSearchData($user, $pwd);

            $moviesJson = json_encode($searchData['peliculas'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $seriesJson = json_encode($searchData['series'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $channelsJson = json_encode($searchData['canales'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            ?>
            <link rel="stylesheet" href="./styles/search/modal.css">
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
                window.PLAYGO_SEARCH_DATA = {
                    peliculas: <?php echo $moviesJson; ?>,
                    series: <?php echo $seriesJson; ?>,
                    canales: <?php echo $channelsJson; ?>
                };
            </script>
            <script src="./scripts/search/modal.js"></script>
            <?php
        }
    }
}

if (isset($user) && isset($pwd)) {
    playgo_render_search_modal($user, $pwd);
}
?>

