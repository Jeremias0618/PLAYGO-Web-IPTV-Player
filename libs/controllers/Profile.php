<?php

if (!defined('IP')) {
    require_once(__DIR__ . '/../lib.php');
}

if (!function_exists('getMoviesData')) {
    require_once(__DIR__ . '/../services/movies.php');
}

function getProfilePageData($user, $pwd) {
    $movies = getMoviesData($user, $pwd);
    $backdrop = getMovieBackdrop($user, $pwd, $movies);
    
    $safeUser = preg_replace('/[^a-zA-Z0-9_-]/', '_', $user);
    $userDataFile = __DIR__ . '/../../storage/users/' . $safeUser . '/user_data.json';
    $historyFile = __DIR__ . '/../../storage/users/' . $safeUser . '/history.json';
    $progressFile = __DIR__ . '/../../storage/users/' . $safeUser . '/progress.json';
    
    $memberSince = 'Desconocida';
    $lastLogin = 'Desconocida';
    
    if (file_exists($userDataFile)) {
        $userDataContent = file_get_contents($userDataFile);
        $sessions = json_decode($userDataContent, true);
        if (is_array($sessions) && count($sessions) > 0) {
            $firstSession = $sessions[0];
            if (isset($firstSession['date'])) {
                $dateObj = DateTime::createFromFormat('Y-m-d H:i:s', $firstSession['date']);
                if ($dateObj) {
                    $monthNames = [
                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                    ];
                    $month = (int)$dateObj->format('n');
                    $year = $dateObj->format('Y');
                    $memberSince = $monthNames[$month] . ' ' . $year;
                }
            }
            
            $lastSession = end($sessions);
            if (isset($lastSession['date'])) {
                $lastLogin = $lastSession['date'];
            }
        }
    }
    
    $url_info = IP . "/player_api.php?username=$user&password=$pwd&action=get_account_info";
    $respuesta_info = apixtream($url_info);
    $info = json_decode($respuesta_info, true);
    
    $exp_date_formatted = 'Desconocida';
    $next_renewal = 'Desconocida';
    
    if (isset($info['user_info']['exp_date']) && is_numeric($info['user_info']['exp_date'])) {
        $exp_timestamp = $info['user_info']['exp_date'];
        
        if ($exp_timestamp == 0 || $exp_timestamp == null) {
            $exp_date_formatted = 'Sin límite';
            $next_renewal = 'N/A';
        } else {
            $exp_date = new DateTime();
            $exp_date->setTimestamp($exp_timestamp);
            
            $monthNames = [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ];
            $day = (int)$exp_date->format('d');
            $month = (int)$exp_date->format('n');
            $year = $exp_date->format('Y');
            $exp_date_formatted = $day . ' de ' . $monthNames[$month] . ', ' . $year;
            
            $now = new DateTime();
            $diff = $now->diff($exp_date);
            if ($diff->invert == 0) {
                $next_renewal = 'En ' . $diff->days . ' días';
            } else {
                $next_renewal = 'Expirado';
            }
        }
    } else {
        $exp_date_formatted = 'Sin límite';
        $next_renewal = 'N/A';
    }
    
    $totalHours = 0;
    $moviesWatched = 0;
    $seriesWatched = 0;
    
    if (file_exists($historyFile)) {
        $historyContent = file_get_contents($historyFile);
        $history = json_decode($historyContent, true);
        if (is_array($history)) {
            foreach ($history as $item) {
                $type = strtolower($item['type'] ?? '');
                if ($type === 'pelicula' || $type === 'movie') {
                    $moviesWatched++;
                } elseif ($type === 'serie' || $type === 'series') {
                    $seriesWatched++;
                }
            }
        }
    }
    
    if (file_exists($progressFile)) {
        $progressContent = file_get_contents($progressFile);
        $progress = json_decode($progressContent, true);
        if (is_array($progress)) {
            foreach ($progress as $item) {
                if (isset($item['time']) && isset($item['duration'])) {
                    $time = (int)$item['time'];
                    $durationStr = $item['duration'];
                    
                    $durationParts = explode(':', $durationStr);
                    $durationSeconds = 0;
                    if (count($durationParts) === 3) {
                        $durationSeconds = (int)$durationParts[0] * 3600 + (int)$durationParts[1] * 60 + (int)$durationParts[2];
                    } elseif (count($durationParts) === 2) {
                        $durationSeconds = (int)$durationParts[0] * 60 + (int)$durationParts[1];
                    }
                    
                    if ($durationSeconds > 0) {
                        $watchedPercent = $time / $durationSeconds;
                        if ($watchedPercent >= 0.9) {
                            $totalHours += $durationSeconds / 3600;
                        } else {
                            $totalHours += ($time / 3600);
                        }
                    }
                }
            }
        }
    }
    
    $consecutiveDays = 0;
    if (file_exists($progressFile)) {
        $progressContent = file_get_contents($progressFile);
        $progress = json_decode($progressContent, true);
        if (is_array($progress)) {
            $dates = [];
            foreach ($progress as $item) {
                if (isset($item['updated'])) {
                    $dateStr = $item['updated'];
                    $dateObj = DateTime::createFromFormat('Y-m-d H:i:s', $dateStr);
                    if ($dateObj) {
                        $dateOnly = $dateObj->format('Y-m-d');
                        if (!in_array($dateOnly, $dates)) {
                            $dates[] = $dateOnly;
                        }
                    }
                }
            }
            
            if (count($dates) > 0) {
                $uniqueDates = array_unique($dates);
                usort($uniqueDates, function($a, $b) {
                    return strcmp($b, $a);
                });
                
                $consecutiveDays = 1;
                $today = new DateTime();
                $today->setTime(0, 0, 0);
                
                $checkDate = new DateTime($uniqueDates[0]);
                $checkDate->setTime(0, 0, 0);
                
                if ($checkDate->format('Y-m-d') === $today->format('Y-m-d') || 
                    $checkDate->format('Y-m-d') === $today->modify('-1 day')->format('Y-m-d')) {
                    
                    for ($i = 1; $i < count($uniqueDates); $i++) {
                        $prevDate = new DateTime($uniqueDates[$i - 1]);
                        $prevDate->setTime(0, 0, 0);
                        $currentDate = new DateTime($uniqueDates[$i]);
                        $currentDate->setTime(0, 0, 0);
                        
                        $expectedDate = clone $prevDate;
                        $expectedDate->modify('-1 day');
                        
                        if ($currentDate->format('Y-m-d') === $expectedDate->format('Y-m-d')) {
                            $consecutiveDays++;
                        } else {
                            break;
                        }
                    }
                }
            }
        }
    }
    
    return [
        'backdrop' => $backdrop,
        'username' => $user,
        'member_since' => $memberSince,
        'last_login' => $lastLogin,
        'exp_date' => $exp_date_formatted,
        'next_renewal' => $next_renewal,
        'total_hours' => round($totalHours),
        'movies_watched' => $moviesWatched,
        'series_watched' => $seriesWatched,
        'consecutive_days' => $consecutiveDays
    ];
}

