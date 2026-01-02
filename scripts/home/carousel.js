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
});

