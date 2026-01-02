$(document).ready(function(){
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
            url: 'libs/endpoints/ApiContent.php',
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

