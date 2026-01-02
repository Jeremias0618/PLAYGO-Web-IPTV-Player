$(document).ready(function(){
    var lastRefreshTime = 0;
    var refreshCooldown = 3000;

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
            url: 'libs/endpoints/ApiContent.php',
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
});

