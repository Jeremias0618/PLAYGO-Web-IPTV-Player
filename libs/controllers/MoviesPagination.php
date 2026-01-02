<?php

function renderMoviesPagination($currentPage, $totalPages, $queryParams) {
    $html = '<ul class="custom-paginator">';
    
    $totalPages = max(1, $totalPages);
    
    if ($currentPage > 1) {
        $prevParams = array_merge($queryParams, ['pagina' => ($currentPage - 1)]);
        $html .= '<li><a class="arrow" href="?' . http_build_query($prevParams) . '">&#60;</a></li>';
    } else {
        $html .= '<li class="disabled"><span class="arrow">&#60;</span></li>';
    }
    
    if ($currentPage == 1) {
        $html .= '<li class="active"><span>1</span></li>';
    } else {
        $firstParams = array_merge($queryParams, ['pagina' => 1]);
        $html .= '<li><a href="?' . http_build_query($firstParams) . '">1</a></li>';
    }
    
    if ($totalPages >= 2) {
        if ($currentPage == 2) {
            $html .= '<li class="active"><span>2</span></li>';
        } else {
            $secondParams = array_merge($queryParams, ['pagina' => 2]);
            $html .= '<li><a href="?' . http_build_query($secondParams) . '">2</a></li>';
        }
    }
    
    if ($totalPages >= 3) {
        if ($currentPage == 3) {
            $html .= '<li class="active"><span>3</span></li>';
        } else {
            $thirdParams = array_merge($queryParams, ['pagina' => 3]);
            $html .= '<li><a href="?' . http_build_query($thirdParams) . '">3</a></li>';
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
            $lastParams = array_merge($queryParams, ['pagina' => $totalPages]);
            $html .= '<li><a href="?' . http_build_query($lastParams) . '">' . $totalPages . '</a></li>';
        }
    } elseif ($totalPages == 4) {
        if ($currentPage == 4) {
            $html .= '<li class="active"><span>4</span></li>';
        } else {
            $fourthParams = array_merge($queryParams, ['pagina' => 4]);
            $html .= '<li><a href="?' . http_build_query($fourthParams) . '">4</a></li>';
        }
    }
    
    if ($currentPage < $totalPages) {
        $nextParams = array_merge($queryParams, ['pagina' => ($currentPage + 1)]);
        $html .= '<li><a class="arrow" href="?' . http_build_query($nextParams) . '">&#62;</a></li>';
    } else {
        $html .= '<li class="disabled"><span class="arrow">&#62;</span></li>';
    }
    
    $html .= '</ul>';
    
    return $html;
}

