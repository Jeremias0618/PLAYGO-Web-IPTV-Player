<?php

require_once(__DIR__ . '/../lib.php');

function getMovies($user, $pwd, $limit = 16) {
    $url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_streams";
    $resposta = apixtream($url);
    $output = json_decode($resposta, true);
    
    if (!is_array($output)) {
        return [];
    }
    
    shuffle($output);
    return array_slice($output, 0, $limit);
}

function getSeries($user, $pwd, $limit = 16) {
    $url = IP."/player_api.php?username=$user&password=$pwd&action=get_series";
    $resposta = apixtream($url);
    $output = json_decode($resposta, true);
    
    if (!is_array($output)) {
        return [];
    }
    
    shuffle($output);
    return array_slice($output, 0, $limit);
}

function getRecentContent($user, $pwd, $type = 'movie', $limit = 16) {
    if ($type === 'movie' || $type === 'movies') {
        $url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_streams";
    } else {
        $url = IP."/player_api.php?username=$user&password=$pwd&action=get_series";
    }
    
    $resposta = apixtream($url);
    $output = json_decode($resposta, true);
    
    if (!is_array($output)) {
        return [];
    }
    
    usort($output, function($a, $b) {
        $dateA = 0;
        $dateB = 0;
        
        if (isset($a['added']) && !empty($a['added'])) {
            $dateA = is_numeric($a['added']) ? (int)$a['added'] : strtotime($a['added']);
        } elseif (isset($a['releasedate']) && !empty($a['releasedate'])) {
            $dateA = strtotime($a['releasedate']);
        } elseif (isset($a['releaseDate']) && !empty($a['releaseDate'])) {
            $dateA = strtotime($a['releaseDate']);
        }
        
        if (isset($b['added']) && !empty($b['added'])) {
            $dateB = is_numeric($b['added']) ? (int)$b['added'] : strtotime($b['added']);
        } elseif (isset($b['releasedate']) && !empty($b['releasedate'])) {
            $dateB = strtotime($b['releasedate']);
        } elseif (isset($b['releaseDate']) && !empty($b['releaseDate'])) {
            $dateB = strtotime($b['releaseDate']);
        }
        
        return $dateB <=> $dateA;
    });
    
    return array_slice($output, 0, $limit);
}

function getPremieres($user, $pwd, $type = 'movie', $limit = 16) {
    $currentYear = date('Y');
    
    if ($type === 'movie' || $type === 'movies') {
        $url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_streams";
    } else {
        $url = IP."/player_api.php?username=$user&password=$pwd&action=get_series";
    }
    
    $resposta = apixtream($url);
    $output = json_decode($resposta, true);
    
    if (!is_array($output)) {
        return [];
    }
    
    $estrenos = [];
    foreach($output as $row) {
        $year = isset($row['year']) ? $row['year'] : (isset($row['releaseDate']) ? substr($row['releaseDate'], 0, 4) : 'N/A');
        if ($year == $currentYear) {
            $estrenos[] = $row;
        }
    }
    
    usort($estrenos, function($a, $b) {
        $dateA = 0;
        $dateB = 0;
        
        if (isset($a['releasedate']) && !empty($a['releasedate'])) {
            $dateA = strtotime($a['releasedate']);
        } elseif (isset($a['releaseDate']) && !empty($a['releaseDate'])) {
            $dateA = strtotime($a['releaseDate']);
        } elseif (isset($a['added']) && !empty($a['added'])) {
            $dateA = is_numeric($a['added']) ? (int)$a['added'] : strtotime($a['added']);
        } elseif (isset($a['year']) && !empty($a['year'])) {
            $dateA = strtotime($a['year'] . '-01-01');
        }
        
        if (isset($b['releasedate']) && !empty($b['releasedate'])) {
            $dateB = strtotime($b['releasedate']);
        } elseif (isset($b['releaseDate']) && !empty($b['releaseDate'])) {
            $dateB = strtotime($b['releaseDate']);
        } elseif (isset($b['added']) && !empty($b['added'])) {
            $dateB = is_numeric($b['added']) ? (int)$b['added'] : strtotime($b['added']);
        } elseif (isset($b['year']) && !empty($b['year'])) {
            $dateB = strtotime($b['year'] . '-01-01');
        }
        
        if ($dateA == $dateB) {
            $ratingA = isset($a['rating_5based']) ? (float)$a['rating_5based'] : (isset($a['rating']) ? (float)$a['rating'] : 0);
            $ratingB = isset($b['rating_5based']) ? (float)$b['rating_5based'] : (isset($b['rating']) ? (float)$b['rating'] : 0);
            return $ratingB <=> $ratingA;
        }
        
        return $dateB <=> $dateA;
    });
    
    return array_slice($estrenos, 0, $limit);
}

