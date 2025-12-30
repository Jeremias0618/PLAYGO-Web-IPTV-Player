$(document).ready(function(){
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
        $('#refresh-movies-btn, #refresh-series-btn, #recientes-type, #estrenos-type').hide();
        $('#refresh-movies-btn-mobile, #refresh-series-btn-mobile, #recientes-type-mobile, #estrenos-type-mobile').hide();
        
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
        $btn.addClass('spinning');
        $btn.prop('disabled', true);
        
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
                $btn.prop('disabled', false);
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

    $('#estrenos-type, #estrenos-type-mobile').on('change', function(){
        var type = $(this).val();
        var $grid = $('#estrenos-grid');
        
        $('#estrenos-type').val(type);
        $('#estrenos-type-mobile').val(type);
        
        $grid.html('<div class="col-12 text-center" style="color: #fff; padding: 40px;"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>');
        
        $.ajax({
            url: 'libs/endpoints/ApiContentEndpoint.php',
            method: 'POST',
            data: {
                action: 'premieres',
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
});

