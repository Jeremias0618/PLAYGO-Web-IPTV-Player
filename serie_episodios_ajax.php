<?php
require_once("libs/lib.php");

$user = $_COOKIE['xuserm'] ?? '';
$pwd = $_COOKIE['xpwdm'] ?? '';
$id = trim($_REQUEST['serie_id'] ?? '');
$season = intval($_REQUEST['season'] ?? 1);

$url = IP."/player_api.php?username=$user&password=$pwd&action=get_series_info&series_id=$id";
$resposta = apixtream($url);
$output = json_decode($resposta,true);

$poster_img = $output['info']['cover'];
$episodios = $output['episodes'] ?? [];
$tmdb_api_key = "eae5dbe11c2b8d96808af6b5e0fec463";
$tmdb_id = $output['info']['tmdb_id'] ?? null;
$tmdb_episodios_imgs = [];

if ($tmdb_id && isset($episodios[$season])) {
    $cache_dir = __DIR__ . '/tmdb_cache/';
    if (!is_dir($cache_dir)) mkdir($cache_dir, 0777, true);

    // Obtener episodios de la temporada desde TMDb
    $tmdb_url = "https://api.themoviedb.org/3/tv/$tmdb_id/season/$season?api_key=$tmdb_api_key&language=es-ES";
    $tmdb_json = @file_get_contents($tmdb_url);
    $tmdb_data = json_decode($tmdb_json, true);
    if (!empty($tmdb_data['episodes'])) {
        foreach ($tmdb_data['episodes'] as $ep) {
            if (!empty($ep['still_path'])) {
                $img_url = "https://image.tmdb.org/t/p/w500" . $ep['still_path'];
                $img_local = $cache_dir . "{$tmdb_id}_{$season}_{$ep['episode_number']}.jpg";
                $img_local_url = "tmdb_cache/{$tmdb_id}_{$season}_{$ep['episode_number']}.jpg";
                if (!file_exists($img_local)) {
                    $img_data = @file_get_contents($img_url);
                    if ($img_data) file_put_contents($img_local, $img_data);
                }
                if (file_exists($img_local)) {
                    $tmdb_episodios_imgs[$ep['episode_number']] = $img_local_url;
                } else {
                    $tmdb_episodios_imgs[$ep['episode_number']] = $img_url;
                }
            }
        }
    }
}

$html = '';
foreach ($episodios[$season] as $ep) {
    $ep_name = $ep['title'] ?? 'Episodio';
    if (strpos($ep_name, '-') !== false) {
        $parts = explode('-', $ep_name);
        $ep_name = trim(end($parts));
    }
    $ep_id = $ep['id'];
    $ep_num = $ep['episode_num'] ?? '';
    $ep_img = $poster_img;
    if (isset($tmdb_episodios_imgs[$ep_num])) {
        $ep_img = $tmdb_episodios_imgs[$ep_num];
    } elseif (!empty($ep['info']['movie_image'])) {
        $ep_img = $ep['info']['movie_image'];
    }
    $ep_plot = $ep['info']['plot'] ?? '';
    $ep_dur = $ep['info']['duration'] ?? '';
    $ep_num_str = $ep_num ? str_pad($ep_num, 2, '0', STR_PAD_LEFT) : '';
    $duracion_valida = !empty($ep_dur) && !in_array(trim($ep_dur), ['0', '00:00', '00:00:00']);
    $html .= '<div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex">
        <div class="episode-card w-100">
          <img src="'.htmlspecialchars($ep_img).'" alt="">
          <div class="card-body d-flex flex-column">
            <div class="card-title">'.htmlspecialchars($ep_num_str ? "Episodio $ep_num_str - " : "").limitar_texto($ep_name, 40).'</div>
            <div class="card-text">'.htmlspecialchars($ep_plot).'</div>';
    if ($duracion_valida) {
        $html .= '<div class="mb-2" style="color:#ffd700;">'.htmlspecialchars($ep_dur).'</div>';
    }
    $html .= '<a href="serie_play.php?serie_id='.$id.'&ep_id='.$ep_id.'&streamtipo=serie" class="btn-play mt-auto"><i class="fa-solid fa-circle-play"></i> Ver episodio</a>
          </div>
        </div>
      </div>';
}
echo $html;