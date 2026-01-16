$(document).ready(function(){
    var $historyCarousel = $('.history-carousel');
    var $prevBtn = $('.history-nav-prev');
    var $nextBtn = $('.history-nav-next');
    
    if ($historyCarousel.length > 0) {
        $historyCarousel.owlCarousel({
            loop: false,
            margin: 30,
            nav: false,
            dots: false,
            autoplay: false,
            autoplayTimeout: 0,
            autoplayHoverPause: false,
            rtl: false,
            smartSpeed: 1800,
            mouseDrag: true,
            touchDrag: true,
            pullDrag: true,
            freeDrag: false,
            responsive:{
                0:{ items:1, margin: 20 },
                600:{ items:3, margin: 25 },
                1000:{ items:5, margin: 30 }
            }
        });
        
        if ($prevBtn.length > 0 && $nextBtn.length > 0) {
            $prevBtn.off('click').on('click', function(){
                $historyCarousel.trigger('prev.owl.carousel');
            });
            
            $nextBtn.off('click').on('click', function(){
                $historyCarousel.trigger('next.owl.carousel');
            });
        }
    }
    
    var $favoritesCarousel = $('.favorites-carousel');
    var $favoritesPrevBtn = $('.favorites-nav-prev');
    var $favoritesNextBtn = $('.favorites-nav-next');
    
    if ($favoritesCarousel.length > 0) {
        $favoritesCarousel.owlCarousel({
            loop: false,
            margin: 30,
            nav: false,
            dots: false,
            autoplay: false,
            autoplayTimeout: 0,
            autoplayHoverPause: false,
            rtl: false,
            smartSpeed: 1800,
            mouseDrag: true,
            touchDrag: true,
            pullDrag: true,
            freeDrag: false,
            responsive:{
                0:{ items:1, margin: 20 },
                600:{ items:3, margin: 25 },
                1000:{ items:5, margin: 30 }
            }
        });
        
        if ($favoritesPrevBtn.length > 0 && $favoritesNextBtn.length > 0) {
            $favoritesPrevBtn.off('click').on('click', function(){
                $favoritesCarousel.trigger('prev.owl.carousel');
            });
            
            $favoritesNextBtn.off('click').on('click', function(){
                $favoritesCarousel.trigger('next.owl.carousel');
            });
        }
    }
});

