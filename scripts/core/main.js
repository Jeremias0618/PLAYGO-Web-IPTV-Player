$(document).ready(function () {
	"use strict";

	$('.header__btn').on('click', function() {
		$(this).toggleClass('header__btn--active');
		$('.header__nav').toggleClass('header__nav--active');
		$('.body').toggleClass('body--active');

		if ($('.header__search-btn').hasClass('active')) {
			$('.header__search-btn').toggleClass('active');
			$('.header__search').toggleClass('header__search--active');
		}
	});

	$('.header__search-btn').on('click', function() {
		$(this).toggleClass('active');
		$('.header__search').toggleClass('header__search--active');

		if ($('.header__btn').hasClass('header__btn--active')) {
			$('.header__btn').toggleClass('header__btn--active');
			$('.header__nav').toggleClass('header__nav--active');
			$('.body').toggleClass('body--active');
		}
	});

	$('.home__bg').owlCarousel({
		animateOut: 'fadeOut',
		animateIn: 'fadeIn',
		mouseDrag: false,
		touchDrag: false,
		items: 1,
		dots: false,
		loop: true,
		autoplay: false,
		smartSpeed: 600,
		margin: 0,
	});

	$('.home__bg .item').each( function() {
		if ($(this).attr("data-bg")){
			$(this).css({
				'background': 'url(' + $(this).data('bg') + ')',
				'background-position': 'center center',
				'background-repeat': 'no-repeat',
				'background-size': 'cover'
			});
		}
	});

	$('.home__carousel').owlCarousel({
		mouseDrag: true,
		touchDrag: true,
		dots: false,
		loop: true,
		autoplay: false,
		smartSpeed: 600,
		margin: 30,
		responsive : {
			0 : {
				items: 2,
			},
			576 : {
				items: 2,
			},
			768 : {
				items: 3,
			},
			992 : {
				items: 4,
			},
			1200 : {
				items: 4,
				margin: 50
			},
		},
        onDragged: function(event) {
            $('.home__bg').trigger('to.owl.carousel', $('.home__carousel .owl-item.active .item').data("index"));
        }
	});

	$('.home__nav--next').on('click', function() {
		$('.home__carousel, .home__bg').trigger('next.owl.carousel');
	});
	$('.home__nav--prev').on('click', function() {
		$('.home__carousel, .home__bg').trigger('prev.owl.carousel');
	});

	$('.seasons__bg').owlCarousel({
		animateOut: 'fadeOut',
		animateIn: 'fadeIn',
		mouseDrag: false,
		touchDrag: false,
		items: 1,
		dots: false,
		loop: true,
		autoplay: false,
		smartSpeed: 300,
		margin: 0,
        
	});

	$('.seasons__bg .item').each( function() {
		if ($(this).attr("data-bg")){
			$(this).css({
				'background': 'url(' + $(this).data('bg') + ')',
				'background-position': 'center center',
				'background-repeat': 'no-repeat',
				'background-size': 'cover'
			});
		}
	});

	$('.seasons__carousel').owlCarousel({
		mouseDrag: true,
		touchDrag: true,
		dots: false,
		loop: true,
		autoplay: false,
		smartSpeed: 300,
		margin: 30,
		responsive : {
			0 : {
				items: 1,
			},
			576 : {
				items: 1,
			},
			768 : {
				items: 2,
			},
			992 : {
				items: 2,
			},
			1200 : {
				items: 2,
				margin: 50
			},
		},
        onDragged: function(event) {
            $('.seasons__bg').trigger('to.owl.carousel', $('.seasons__carousel .owl-item.active .item').data("index"));
        }
	});

	$('.seasons__nav--next').on('click', function() {
		$('.seasons__carousel, .seasons__bg').trigger('next.owl.carousel');
	});
	$('.seasons__nav--prev').on('click', function() {
		$('.seasons__carousel, .seasons__bg').trigger('prev.owl.carousel');
	});

	$(window).on('resize', function() {
		$('.seasons__bg .item').css("height", $('.seasons__bg').height() + "px");
		$('.home__bg .item').css("height", $('.home__bg').height() + "px");
	});
	$(window).trigger('resize');
    
    $(".seasons__bg").on('resize', function() {
        $('.seasons__bg .item').css("height", $('.seasons__bg').height() + "px");
    });
    $(".home__bg").on('resize', function() {
        $('.home__bg .item').css("height", $('.home__bg').height() + "px");
    });

	$('.content__mobile-tabs-menu li').each( function() {
		$(this).attr('data-value', $(this).text().toLowerCase());
	});

	$('.content__mobile-tabs-menu li').on('click', function() {
		var text = $(this).text();
		var item = $(this);
		var id = item.closest('.content__mobile-tabs').attr('id');
		$('#'+id).find('.content__mobile-tabs-btn input').val(text);
	});

	$('.section--bg, .details__bg, .nav__bg').each( function() {
		if ($(this).attr("data-bg")){
			$(this).css({
				'background': 'url(' + $(this).data('bg') + ')',
				'background-position': 'center center',
				'background-repeat': 'no-repeat',
				'background-size': 'cover'
			});
		}
	});

	$('.filter__item-menu li').each( function() {
		$(this).attr('data-value', $(this).text().toLowerCase());
	});

	$('.filter__item-menu li').on('click', function() {
		var text = $(this).text();
		var item = $(this);
		var id = item.closest('.filter__item').attr('id');
		$('#'+id).find('.filter__item-btn input').val(text);
	});

	$('.card__description--details').moreLines({
		linecount: 6,
		baseclass: 'b-description',
		basejsclass: 'js-description',
		classspecific: '_readmore',
		buttontxtmore: "",
		buttontxtless: "",
		animationspeed: 400
	});

	var initPhotoSwipeFromDOM = function(gallerySelector) {
		var parseThumbnailElements = function(el) {
			var thumbElements = el.childNodes,
				numNodes = thumbElements.length,
				items = [],
				figureEl,
				linkEl,
				size,
				item;

			for(var i = 0; i < numNodes; i++) {
				figureEl = thumbElements[i];
				if(figureEl.nodeType !== 1) {
					continue;
				}

				linkEl = figureEl.children[0];

				size = linkEl.getAttribute('data-size').split('x');

				item = {
					src: linkEl.getAttribute('href'),
					w: parseInt(size[0], 10),
					h: parseInt(size[1], 10)
				};

				if(figureEl.children.length > 1) {
					item.title = figureEl.children[1].innerHTML; 
				}

				if(linkEl.children.length > 0) {
					item.msrc = linkEl.children[0].getAttribute('src');
				} 

				item.el = figureEl;
				items.push(item);
			}

			return items;
		};

		var closest = function closest(el, fn) {
			return el && ( fn(el) ? el : closest(el.parentNode, fn) );
		};

		var onThumbnailsClick = function(e) {
			e = e || window.event;
			e.preventDefault ? e.preventDefault() : e.returnValue = false;

			var eTarget = e.target || e.srcElement;

			var clickedListItem = closest(eTarget, function(el) {
				return (el.tagName && el.tagName.toUpperCase() === 'FIGURE');
			});

			if(!clickedListItem) {
				return;
			}

			var clickedGallery = clickedListItem.parentNode,
				childNodes = clickedListItem.parentNode.childNodes,
				numChildNodes = childNodes.length,
				nodeIndex = 0,
				index;

			for (var i = 0; i < numChildNodes; i++) {
				if(childNodes[i].nodeType !== 1) { 
					continue; 
				}

				if(childNodes[i] === clickedListItem) {
					index = nodeIndex;
					break;
				}
				nodeIndex++;
			}

			if(index >= 0) {
				openPhotoSwipe( index, clickedGallery );
			}
			return false;
		};

		var photoswipeParseHash = function() {
			var hash = window.location.hash.substring(1),
			params = {};

			if(hash.length < 5) {
				return params;
			}

			var vars = hash.split('&');
			for (var i = 0; i < vars.length; i++) {
				if(!vars[i]) {
					continue;
				}
				var pair = vars[i].split('=');  
				if(pair.length < 2) {
					continue;
				}           
				params[pair[0]] = pair[1];
			}

			if(params.gid) {
				params.gid = parseInt(params.gid, 10);
			}

			return params;
		};

		var openPhotoSwipe = function(index, galleryElement, disableAnimation, fromURL) {
			var pswpElement = document.querySelectorAll('.pswp')[0],
				gallery,
				options,
				items;

			items = parseThumbnailElements(galleryElement);

			options = {

				galleryUID: galleryElement.getAttribute('data-pswp-uid'),

				getThumbBoundsFn: function(index) {
					var thumbnail = items[index].el.getElementsByTagName('img')[0],
						pageYScroll = window.pageYOffset || document.documentElement.scrollTop,
						rect = thumbnail.getBoundingClientRect(); 

					return {x:rect.left, y:rect.top + pageYScroll, w:rect.width};
				}

			};

			if(fromURL) {
				if(options.galleryPIDs) {
					for(var j = 0; j < items.length; j++) {
						if(items[j].pid == index) {
							options.index = j;
							break;
						}
					}
				} else {
					options.index = parseInt(index, 10) - 1;
				}
			} else {
				options.index = parseInt(index, 10);
			}

			if( isNaN(options.index) ) {
				return;
			}

			if(disableAnimation) {
				options.showAnimationDuration = 0;
			}

			gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);
			gallery.init();
		};

		var galleryElements = document.querySelectorAll( gallerySelector );

		for(var i = 0, l = galleryElements.length; i < l; i++) {
			galleryElements[i].setAttribute('data-pswp-uid', i+1);
			galleryElements[i].onclick = onThumbnailsClick;
		}

		var hashData = photoswipeParseHash();
		if(hashData.pid && hashData.gid) {
			openPhotoSwipe( hashData.pid ,  galleryElements[ hashData.gid - 1 ], true, true );
		}
	};
	initPhotoSwipeFromDOM('.gallery');

	function initializePlayer() {
		if ($('#player').length) {
			const player = new Plyr('#player');
		} else {
			return false;
		}
		return false;
	}
	$(window).on('load', initializePlayer());
});
