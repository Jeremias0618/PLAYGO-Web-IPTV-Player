$(document).ready(function(){
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
});

