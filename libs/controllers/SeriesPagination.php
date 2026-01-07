<?php

function cleanSeriesQueryParams($params) {
    $cleaned = [];
    
    $rating = isset($params['rating']) && $params['rating'] !== '' ? $params['rating'] : '';
    $year = isset($params['year']) && $params['year'] !== '' ? $params['year'] : '';
    
    foreach ($params as $key => $value) {
        if ($value === null || $value === '' || $value === false) {
            continue;
        }
        
        if ($key === 'rating') {
            $cleaned['rating'] = $value;
            continue;
        }
        
        if ($key === 'rating_min' || $key === 'rating_max') {
            if ($rating === '') {
                $min = isset($params['rating_min']) ? $params['rating_min'] : '';
                $max = isset($params['rating_max']) ? $params['rating_max'] : '';
                if ($min !== '' && $max !== '') {
                    if ($key === 'rating_min') {
                        $cleaned['rating_min'] = $min;
                        $cleaned['rating_max'] = $max;
                    }
                }
            }
            continue;
        }
        
        if ($key === 'year') {
            $cleaned['year'] = $value;
            continue;
        }
        
        if ($key === 'year_min' || $key === 'year_max') {
            if ($year === '') {
                $min = isset($params['year_min']) ? $params['year_min'] : '';
                $max = isset($params['year_max']) ? $params['year_max'] : '';
                if ($min !== '' && $max !== '') {
                    if ($key === 'year_min') {
                        $cleaned['year_min'] = $min;
                        $cleaned['year_max'] = $max;
                    }
                }
            }
            continue;
        }
        
        if ($key === 'orden_dir') {
            $orden = isset($params['orden']) ? $params['orden'] : '';
            if ($orden === '') {
                continue;
            }
        }
        
        $cleaned[$key] = $value;
    }
    
    return $cleaned;
}

function renderSeriesPagination($currentPage, $totalPages, $queryParams) {
    $html = '<ul class="custom-paginator">';
    
    $totalPages = max(1, $totalPages);
    
    $cleanParams = cleanSeriesQueryParams($queryParams);
    
    if ($currentPage > 1) {
        $prevParams = array_merge($cleanParams, ['pagina' => ($currentPage - 1)]);
        $queryString = http_build_query($prevParams);
        $html .= '<li><a class="arrow" href="?' . $queryString . '">&#60;</a></li>';
    } else {
        $html .= '<li class="disabled"><span class="arrow">&#60;</span></li>';
    }
    
    if ($currentPage == 1) {
        $html .= '<li class="active"><span>1</span></li>';
    } else {
        $firstParams = array_merge($cleanParams, ['pagina' => 1]);
        $queryString = http_build_query($firstParams);
        $html .= '<li><a href="?' . $queryString . '">1</a></li>';
    }
    
    if ($totalPages >= 2) {
        if ($currentPage == 2) {
            $html .= '<li class="active"><span>2</span></li>';
        } else {
            $secondParams = array_merge($cleanParams, ['pagina' => 2]);
            $queryString = http_build_query($secondParams);
            $html .= '<li><a href="?' . $queryString . '">2</a></li>';
        }
    }
    
    if ($totalPages >= 3) {
        if ($currentPage == 3) {
            $html .= '<li class="active"><span>3</span></li>';
        } else {
            $thirdParams = array_merge($cleanParams, ['pagina' => 3]);
            $queryString = http_build_query($thirdParams);
            $html .= '<li><a href="?' . $queryString . '">3</a></li>';
        }
    }
    
    if ($totalPages > 4) {
        if ($currentPage > 3 && $currentPage < $totalPages - 1) {
            $html .= '<li class="disabled"><span>...</span></li>';
            if ($currentPage != $totalPages && $currentPage != 1 && $currentPage != 2 && $currentPage != 3) {
                $html .= '<li class="active"><span>' . $currentPage . '</span></li>';
                $html .= '<li class="disabled"><span>...</span></li>';
            }
        } else {
            $html .= '<li class="disabled"><span>...</span></li>';
        }
        
        if ($currentPage == $totalPages) {
            $html .= '<li class="active"><span>' . $totalPages . '</span></li>';
        } else {
            $lastParams = array_merge($cleanParams, ['pagina' => $totalPages]);
            $queryString = http_build_query($lastParams);
            $html .= '<li><a href="?' . $queryString . '">' . $totalPages . '</a></li>';
        }
    } elseif ($totalPages == 4) {
        if ($currentPage == 4) {
            $html .= '<li class="active"><span>4</span></li>';
        } else {
            $fourthParams = array_merge($cleanParams, ['pagina' => 4]);
            $queryString = http_build_query($fourthParams);
            $html .= '<li><a href="?' . $queryString . '">4</a></li>';
        }
    }
    
    if ($currentPage < $totalPages) {
        $nextParams = array_merge($cleanParams, ['pagina' => ($currentPage + 1)]);
        $queryString = http_build_query($nextParams);
        $html .= '<li><a class="arrow" href="?' . $queryString . '">&#62;</a></li>';
    } else {
        $html .= '<li class="disabled"><span class="arrow">&#62;</span></li>';
    }
    
    $html .= '</ul>';
    
    return $html;
}

