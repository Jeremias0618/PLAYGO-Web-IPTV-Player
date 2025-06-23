<?php
require_once("libs/lib.php");

// Verifica sesión
if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: index.php");
    exit;
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];

// IDs de la saga Predator
$saga_predator_ids = [7716, 2693, 2690, 2689, 2692, 2691, 2450, 2449];

// Obtener info de cada película
$peliculas = [];
foreach ($saga_predator_ids as $id) {
    $url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_info&vod_id=$id";
    $resposta = apixtream($url);
    $output = json_decode($resposta, true);
    if (!empty($output['movie_data'])) {
        $peliculas[] = [
            'id' => $id,
            'nombre' => $output['movie_data']['name'],
            'img' => $output['info']['movie_image'],
            'ano' => isset($output['info']['releasedate']) ? substr($output['info']['releasedate'], 0, 4) : '',
            'nota' => isset($output['info']['rating']) ? $output['info']['rating'] : ''
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SAGA PREDATOR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="./css/bootstrap-reboot.min.css">
    <link rel="stylesheet" href="./css/bootstrap-grid.min.css">
    <link rel="stylesheet" href="./css/main.css">
    <link rel="shortcut icon" href="img/favicon.ico">
    <style>
        body {
            background: url('collection/img/predator.webp') no-repeat center center fixed;
            background-size: cover;
        }
        .predator-overlay {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(10,18,24,0.82);
            z-index: 0;
        }
        .header__wrap, .header__content {
            background: #000 !important;
        }
        .saga-title {
            color: #ffd700;
            text-shadow: 0 2px 16px #000a;
            font-size: 2.8rem;
            font-weight: bold;
            letter-spacing: 2px;
            margin-top: 140px;
            margin-bottom: 32px;
            text-align: center;
        }
        .saga-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 32px 24px;
            justify-content: center;
            margin-bottom: 60px;
        }
        .saga-card {
            background: rgba(20,20,20,0.92);
            border-radius: 16px;
            box-shadow: 0 4px 24px #0007;
            overflow: hidden;
            width: 210px;
            transition: transform 0.18s;
            position: relative;
        }
        .saga-card:hover {
            transform: translateY(-8px) scale(1.04);
            box-shadow: 0 8px 32px #000b;
        }
        .saga-card img {
            width: 100%;
            height: 320px;
            object-fit: cover;
            border-bottom: 2px solid #ffd70044;
            background: #232027;
        }
        .saga-card-content {
            padding: 18px 14px 14px 14px;
            text-align: center;
        }
        .saga-card-title {
            color: #fff;
            font-size: 1.12rem;
            font-weight: 600;
            margin-bottom: 8px;
            min-height: 42px;
        }
        .saga-card-meta {
            color: #ffd700;
            font-size: 1rem;
            font-weight: 500;
        }
        .saga-card-link {
            display: block;
            text-decoration: none;
        }
        @media (max-width: 600px) {
            .saga-card img { height: 220px; }
            .saga-card { width: 98vw; max-width: 320px; }
        }
        .audio-player {
            display: none;
        }
        /* MODAL BUSCADOR ... (igual que antes) */
        .modal-buscador-bg { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.85); align-items: center; justify-content: center; }
        .modal-buscador-bg.active { display: flex; }
        .modal-buscador { background: #181818; border-radius: 18px; padding: 32px 24px 24px 24px; max-width: 900px; width: 98vw; max-height: 90vh; overflow-y: auto; box-shadow: 0 8px 32px #000a; position: relative; }
        .modal-buscador-close { position: absolute; top: 16px; right: 18px; font-size: 1.8rem; color: #fff; background: none; border: none; cursor: pointer; z-index: 2; }
        .modal-buscador-inputbox { display: flex; align-items: center; margin-bottom: 24px; }
        .modal-buscador-inputbox input { flex: 1; background: #232027; border: none; color: #fff; border-radius: 8px; padding: 12px 18px; font-size: 1.2rem; margin-right: 12px; }
        .modal-buscador-inputbox input::placeholder { color: #aaa; }
        .modal-buscador-inputbox button { background: #e50914; border: none; color: #fff; border-radius: 8px; padding: 10px 22px; font-size: 1.1rem; cursor: pointer; transition: background 0.2s; }
        .modal-buscador-inputbox button:hover { background: #fff; color: #e50914; }
        .modal-buscador-section { margin-bottom: 32px; }
        .modal-buscador-section h3 { color: #e50914; font-size: 1.25rem; margin-bottom: 16px; margin-top: 0; font-weight: 700; letter-spacing: 1px; }
        .modal-buscador-grid { display: flex; flex-wrap: wrap; gap: 18px; }
        .modal-buscador-card { width: 110px; text-align: center; }
        .modal-buscador-card img { width: 100%; height: 140px; object-fit: cover; border-radius: 10px; background: #232027; margin-bottom: 7px; box-shadow: 0 2px 8px #0005; }
        .modal-buscador-card span { color: #fff; font-size: 0.98rem; display: block; margin-top: 2px; word-break: break-word; }
    </style>
</head>
<body>
<div class="predator-overlay"></div>
<!-- HEADER igual a filmes.php -->
<header class="header">
    <div class="navbar-overlay bg-animate"></div>
    <div class="header__wrap">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="header__content d-flex align-items-center justify-content-between">
                        <a class="header__logo" href="index.php">
                            <img src="img/logo.png" alt="" height="48px">
                        </a>
                        <ul class="header__nav d-flex align-items-center mb-0">
                            <li class="header__nav-item">
                                <a href="./painel.php" class="header__nav-link">Inicio</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="./canais.php" class="header__nav-link">TV en Vivo</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="filmes.php" class="header__nav-link">Películas</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="series.php" class="header__nav-link">Series</a>
                            </li>
                        </ul>
                        <div class="header__auth d-flex align-items-center">
                            <button class="header__search-btn" type="button" id="openSearchModal">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="profile.php">
                                <button class="header__signout-btn" type="button">
                                    <i class="fas fa-user"></i>
                                </button>
                            </a>
                        </div>
                        <button class="header__btn" type="button">
                            <span></span>
                            <span></span>
                            <span></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- MODAL BUSCADOR -->
<div class="modal-buscador-bg" id="modalBuscador">
    <div class="modal-buscador">
        <button class="modal-buscador-close" id="closeSearchModal" title="Cerrar">&times;</button>
        <form id="modalBuscadorForm" autocomplete="off" onsubmit="return false;">
            <div class="modal-buscador-inputbox">
                <input type="text" id="modalBuscadorInput" placeholder="Buscar películas, series o canales en vivo..." autofocus>
                <button type="button" id="modalBuscadorBtn">Buscar</button>
            </div>
        </form>
        <div id="modalBuscadorResults"></div>
    </div>
</div>
<!-- FIN HEADER -->

<main>
    <div class="container">
        <h1 class="saga-title"><i class="fa fa-skull-crossbones"></i> SAGA PREDATOR</h1>
        <div class="saga-grid">
            <?php foreach ($peliculas as $p): ?>
                <a class="saga-card-link" href="filme.php?stream=<?php echo $p['id']; ?>&streamtipo=movie">
                    <div class="saga-card">
                        <img src="<?php echo htmlspecialchars($p['img']); ?>" alt="<?php echo htmlspecialchars($p['nombre']); ?>">
                        <div class="saga-card-content">
                            <div class="saga-card-title"><?php echo htmlspecialchars($p['nombre']); ?></div>
                            <div class="saga-card-meta">
                                <?php if ($p['ano']) echo $p['ano']; ?>
                                <?php if ($p['nota'] !== '') echo ' &nbsp;<i class="fa-solid fa-star"></i> ' . $p['nota']; ?>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<audio id="predatorAudio" class="audio-player" src="collection/audio/predator.mp3" autoplay loop></audio>
<script>
    // Forzar autoplay en algunos navegadores móviles
    window.addEventListener('DOMContentLoaded', function() {
        var audio = document.getElementById('predatorAudio');
        audio.volume = 0.5;
        audio.play().catch(function(){});
        document.body.addEventListener('click', function() {
            if (audio.paused) audio.play().catch(function(){});
        }, {once:true});
    });

    // MODAL BUSCADOR igual que filme.php
    document.addEventListener('DOMContentLoaded', function() {
        const openSearchModal = document.getElementById('openSearchModal');
        const closeSearchModal = document.getElementById('closeSearchModal');
        const modalBuscador = document.getElementById('modalBuscador');
        const modalBuscadorInput = document.getElementById('modalBuscadorInput');
        const modalBuscadorBtn = document.getElementById('modalBuscadorBtn');
        const modalBuscadorResults = document.getElementById('modalBuscadorResults');

        function showModalBuscador() {
            modalBuscador.classList.add('active');
            setTimeout(() => { modalBuscadorInput.focus(); }, 200);
        }
        function hideModalBuscador() {
            modalBuscador.classList.remove('active');
            modalBuscadorInput.value = '';
            modalBuscadorResults.innerHTML = '';
        }
        if(openSearchModal) openSearchModal.onclick = showModalBuscador;
        if(closeSearchModal) closeSearchModal.onclick = hideModalBuscador;
        window.addEventListener('keydown', function(e) {
            if (e.key === "Escape") hideModalBuscador();
        });
        if(modalBuscador) {
            modalBuscador.addEventListener('click', function(e) {
                if (e.target === modalBuscador) hideModalBuscador();
            });
        }

        // Cargar datos para búsqueda (películas, series, canales)
        let peliculas = [];
        let series = [];
        let canales = [];
        <?php
        // Películas
        $url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_streams";
        $resposta = apixtream($url);
        $peliculas = json_decode($resposta,true);
        echo "peliculas = ".json_encode(array_map(function($p){
            return [
                'id'=>$p['stream_id'],
                'nombre'=>$p['name'],
                'img'=>$p['stream_icon'],
                'tipo'=>$p['stream_type']
            ];
        },$peliculas)).";\n";
        // Series
        $url = IP."/player_api.php?username=$user&password=$pwd&action=get_series";
        $resposta = apixtream($url);
        $series = json_decode($resposta,true);
        echo "series = ".json_encode(array_map(function($s){
            return [
                'id'=>$s['series_id'],
                'nombre'=>$s['name'],
                'img'=>$s['cover']
            ];
        },$series)).";\n";
        // Canales
        $url = IP."/player_api.php?username=$user&password=$pwd&action=get_live_streams";
        $resposta = apixtream($url);
        $canales = json_decode($resposta,true);
        echo "canales = ".json_encode(array_map(function($c){
            return [
                'id'=>$c['stream_id'],
                'nombre'=>$c['name'],
                'img'=>$c['stream_icon'],
                'tipo'=>$c['stream_type']
            ];
        },$canales)).";\n";
        ?>

        function renderBuscadorResults(query) {
            query = query.trim().toLowerCase();
            let html = '';
            // Películas
            let pelis = peliculas.filter(p => p.nombre.toLowerCase().includes(query));
            if (pelis.length > 0) {
                html += `<div class="modal-buscador-section"><h3>PELICULAS</h3><div class="modal-buscador-grid">`;
                pelis.slice(0,12).forEach(p => {
                    html += `<div class="modal-buscador-card">
                        <a href="filme.php?stream=${p.id}&streamtipo=movie">
                            <img src="${p.img}" alt="${p.nombre}">
                            <span>${p.nombre}</span>
                        </a>
                    </div>`;
                });
                html += `</div></div>`;
            }
            // Series
            let sers = series.filter(s => s.nombre.toLowerCase().includes(query));
            if (sers.length > 0) {
                html += `<div class="modal-buscador-section"><h3>SERIES</h3><div class="modal-buscador-grid">`;
                sers.slice(0,12).forEach(s => {
                    html += `<div class="modal-buscador-card">
                        <a href="serie.php?stream=${s.id}&serie=${encodeURIComponent(s.nombre)}&img=${encodeURIComponent(s.img)}">
                            <img src="${s.img}" alt="${s.nombre}">
                            <span>${s.nombre}</span>
                        </a>
                    </div>`;
                });
                html += `</div></div>`;
            }
            // Canales
            let chans = canales.filter(c => c.nombre.toLowerCase().includes(query));
            if (chans.length > 0) {
                html += `<div class="modal-buscador-section"><h3>TV EN VIVO</h3><div class="modal-buscador-grid">`;
                chans.slice(0,12).forEach(c => {
                    html += `<div class="modal-buscador-card">
                        <a href="canal.php?stream=${c.id}&streamtipo=${c.tipo}&canal=${encodeURIComponent(c.nombre)}&img=${encodeURIComponent(c.img)}">
                            <img src="${c.img}" alt="${c.nombre}">
                            <span>${c.nombre}</span>
                        </a>
                    </div>`;
                });
                html += `</div></div>`;
            }
            if (!html && query.length > 0) {
                html = `<div style="color:#fff;text-align:center;margin-top:30px;">Sin resultados.</div>`;
            }
            modalBuscadorResults.innerHTML = html;
        }

        if(modalBuscadorInput) {
            modalBuscadorInput.addEventListener('input', function() {
                let q = this.value;
                if (q.length > 1) renderBuscadorResults(q);
                else modalBuscadorResults.innerHTML = '';
            });
        }
        if(modalBuscadorBtn) {
            modalBuscadorBtn.addEventListener('click', function() {
                let q = modalBuscadorInput.value;
                if (q.length > 1) renderBuscadorResults(q);
            });
        }
        if(modalBuscadorInput) {
            modalBuscadorInput.addEventListener('keydown', function(e){
                if(e.key === "Enter") {
                    e.preventDefault();
                    let q = modalBuscadorInput.value;
                    if (q.length > 1) renderBuscadorResults(q);
                }
            });
        }
    });
</script>
</body>
</html>