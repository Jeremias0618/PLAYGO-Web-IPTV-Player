$(document).ready(function(){
    $('#recientes-type, #recientes-type-mobile').on('change', function(){
        var type = $(this).val();
        var $grid = $('#recientes-grid');
        
        $('#recientes-type').val(type);
        $('#recientes-type-mobile').val(type);
        
        $grid.html('<div class="col-12 text-center" style="color: #fff; padding: 40px;"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>');
        
        $.ajax({
            url: 'libs/endpoints/ApiContent.php',
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
});

