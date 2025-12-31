$(document).ready(function(){
    var lastRefreshTime = 0;
    var refreshCooldown = 3000;
    
    var $carousel = $('.home__carousel');
    $carousel.owlCarousel({
        loop: true,
        margin: 20,
        nav: false,
        dots: false,
        autoplay: false,
        autoplayTimeout: 0,
        autoplayHoverPause: false,
        rtl: false,
        smartSpeed: 1800,
        responsive:{
            0:{ items:1 },
            600:{ items:3 },
            1000:{ items:5 }
        }
    });

    $('.home__nav--prev').off('click').on('click', function(){
        $carousel.trigger('prev.owl.carousel');
    });
    $('.home__nav--next').off('click').on('click', function(){
        $carousel.trigger('next.owl.carousel');
    });

    if (window._carouselInterval) clearInterval(window._carouselInterval);
    window._carouselInterval = setInterval(function(){
        $carousel.trigger('next.owl.carousel', [1800]);
    }, 2000);

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr('href');
        $('#refresh-movies-btn, #refresh-series-btn, #recientes-type, #estrenos-type, #estrenos-year').hide();
        $('#refresh-movies-btn-mobile, #refresh-series-btn-mobile, #recientes-type-mobile, #estrenos-type-mobile, #estrenos-year-mobile').hide();
        
        if (target === '#movies') {
            $('#refresh-movies-btn').show();
            $('#refresh-movies-btn-mobile').show();
        } else if (target === '#series') {
            $('#refresh-series-btn').show();
            $('#refresh-series-btn-mobile').show();
        } else if (target === '#recientes') {
            $('#recientes-type').show();
            $('#recientes-type-mobile').show();
        } else if (target === '#estrenos') {
            $('#estrenos-type').show();
            $('#estrenos-type-mobile').show();
            $('#estrenos-year').show();
            $('#estrenos-year-mobile').show();
        }
    });

    if ($('#movies').hasClass('active')) {
        $('#refresh-movies-btn').show();
        $('#refresh-movies-btn-mobile').show();
    }

    $('.refresh-btn[data-type="movie"]').on('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        var $btn = $(this);
        var $grid = $('#movies-grid');
        refreshContent('movie', $btn, $grid);
    });

    $('.refresh-btn[data-type="series"]').on('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        var $btn = $(this);
        var $grid = $('#series-grid');
        refreshContent('series', $btn, $grid);
    });

    function refreshContent(type, $btn, $grid) {
        var currentTime = Date.now();
        var timeSinceLastRefresh = currentTime - lastRefreshTime;
        
        if (timeSinceLastRefresh < refreshCooldown) {
            var remainingTime = Math.ceil((refreshCooldown - timeSinceLastRefresh) / 1000);
            var originalTitle = $btn.attr('title') || '';
            $btn.attr('title', 'Espera ' + remainingTime + ' segundo' + (remainingTime > 1 ? 's' : ''));
            $btn.addClass('cooldown');
            
            setTimeout(function() {
                $btn.removeClass('cooldown');
                $btn.attr('title', originalTitle);
            }, refreshCooldown - timeSinceLastRefresh);
            
            return;
        }
        
        lastRefreshTime = currentTime;
        $btn.addClass('spinning');
        $btn.prop('disabled', true);
        $btn.addClass('cooldown');
        
        $.ajax({
            url: 'libs/endpoints/ApiContentEndpoint.php',
            method: 'POST',
            data: {
                action: 'refresh',
                type: type,
                limit: 16
            },
            success: function(response) {
                var data = typeof response === 'string' ? JSON.parse(response) : response;
                if (data.html) {
                    $grid.html(data.html);
                } else if (data.error) {
                    console.error('Error:', data.error);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al actualizar contenido:', status, error);
                console.error('Response:', xhr.responseText);
            },
            complete: function() {
                $btn.removeClass('spinning');
                
                setTimeout(function() {
                    $btn.prop('disabled', false);
                    $btn.removeClass('cooldown');
                }, refreshCooldown);
            }
        });
    }


    $('#recientes-type, #recientes-type-mobile').on('change', function(){
        var type = $(this).val();
        var $grid = $('#recientes-grid');
        
        $('#recientes-type').val(type);
        $('#recientes-type-mobile').val(type);
        
        $grid.html('<div class="col-12 text-center" style="color: #fff; padding: 40px;"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>');
        
        $.ajax({
            url: 'libs/endpoints/ApiContentEndpoint.php',
            method: 'POST',
            data: {
                action: 'recent',
                type: type === 'series' ? 'series' : 'movie',
                limit: 16
            },
            success: function(response) {
                var data = typeof response === 'string' ? JSON.parse(response) : response;
                if (data.html) {
                    $grid.html(data.html);
                } else if (data.error) {
                    $grid.html('<div class="col-12 text-center" style="color: #ff4444; padding: 40px;">Error: ' + data.error + '</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar contenido:', status, error);
                console.error('Response:', xhr.responseText);
                var errorMsg = 'Error al cargar contenido';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        errorMsg = response.error;
                    }
                } catch(e) {}
                $grid.html('<div class="col-12 text-center" style="color: #ff4444; padding: 40px;">' + errorMsg + '</div>');
            }
        });
    });

    function loadEstrenos(selectedYear) {
        var type = $('#estrenos-type').val() || $('#estrenos-type-mobile').val() || 'movie';
        var year = selectedYear || $('#estrenos-year').val() || $('#estrenos-year-mobile').val() || new Date().getFullYear();
        var $grid = $('#estrenos-grid');
        
        year = parseInt(year) || new Date().getFullYear();
        
        $('#estrenos-type').val(type);
        $('#estrenos-type-mobile').val(type);
        $('#estrenos-year').val(year);
        $('#estrenos-year-mobile').val(year);
        
        $grid.html('<div class="col-12 text-center" style="color: #fff; padding: 40px;"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>');
        
        $.ajax({
            url: 'libs/endpoints/ApiContentEndpoint.php',
            method: 'POST',
            data: {
                action: 'premieres',
                type: type === 'series' ? 'series' : 'movie',
                year: parseInt(year),
                limit: 16
            },
            timeout: 30000,
            success: function(response) {
                var data = typeof response === 'string' ? JSON.parse(response) : response;
                if (data.html) {
                    $grid.html(data.html);
                } else if (data.error) {
                    var typeLabel = (type === 'series') ? 'series' : 'películas';
                    $grid.html('<div class="col-12 text-center" style="color: #fff; padding: 60px 20px;">' +
                        '<i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: rgba(255, 68, 68, 0.5); margin-bottom: 20px; display: block;"></i>' +
                        '<h3 style="color: #ff4444; margin-bottom: 10px; font-size: 1.5rem;">Error al cargar contenido</h3>' +
                        '<p style="color: rgba(255, 255, 255, 0.7); font-size: 1rem;">' + data.error + '</p>' +
                        '</div>');
                } else {
                    var typeLabel = (type === 'series') ? 'series' : 'películas';
                    $grid.html('<div class="col-12 text-center" style="color: #fff; padding: 60px 20px;">' +
                        '<i class="fas fa-film" style="font-size: 3rem; color: rgba(255, 255, 255, 0.3); margin-bottom: 20px; display: block;"></i>' +
                        '<h3 style="color: #fff; margin-bottom: 10px; font-size: 1.5rem;">No se encontraron resultados</h3>' +
                        '<p style="color: rgba(255, 255, 255, 0.7); font-size: 1rem;">No hay ' + typeLabel + ' disponibles para el año ' + year + '.</p>' +
                        '</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar contenido:', status, error);
                console.error('Response:', xhr.responseText);
                var errorMsg = 'Error al cargar contenido';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        errorMsg = response.error;
                    }
                } catch(e) {}
                var typeLabel = (type === 'series') ? 'series' : 'películas';
                $grid.html('<div class="col-12 text-center" style="color: #fff; padding: 60px 20px;">' +
                    '<i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: rgba(255, 68, 68, 0.5); margin-bottom: 20px; display: block;"></i>' +
                    '<h3 style="color: #ff4444; margin-bottom: 10px; font-size: 1.5rem;">Error al cargar contenido</h3>' +
                    '<p style="color: rgba(255, 255, 255, 0.7); font-size: 1rem;">' + errorMsg + '</p>' +
                    '</div>');
            }
        });
    }

    $('#estrenos-type, #estrenos-type-mobile').on('change', function(){
        loadEstrenos();
    });

    $('#estrenos-year').on('change', function(){
        var selectedYear = $(this).val();
        loadEstrenos(selectedYear);
    });

    $('#estrenos-year-mobile').on('change', function(e){
        e.stopPropagation();
        var selectedYear = $(this).val();
        loadEstrenos(selectedYear);
    });

    $(document).on('change', '#estrenos-year-mobile', function(e){
        e.stopPropagation();
        var selectedYear = $(this).val();
        loadEstrenos(selectedYear);
    });

    $('#estrenos-year-mobile').on('blur', function(){
        var selectedYear = $(this).val();
        if (selectedYear) {
            setTimeout(function(){
                loadEstrenos(selectedYear);
            }, 300);
        }
    });
});

