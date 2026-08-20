(function($) {

    var focusableSelector = 'a, button, input, select, textarea, [tabindex]';

    // jcarousel keeps off-screen slides (and, in circular mode, temporary
    // clone slides) in the tab order. Reaching them with the keyboard makes
    // focus appear to vanish, and clones get removed from the DOM outright
    // once the transition finishes, dropping focus to <body>. Keep hidden
    // and clone slides out of the tab order, and if focus was inside a
    // slide that is about to be hidden or removed, move it somewhere safe.
    function rescueFocus($goingAway, $wrapper) {
        var activeEl = document.activeElement;
        $goingAway.each(function() {
            if (this === activeEl || $.contains(this, activeEl)) {
                $wrapper.trigger('focus');
                return false;
            }
        });
    }

    function makeInert($slides) {
        $slides.attr('aria-hidden', 'true');
        $slides.find(focusableSelector).attr('tabindex', '-1');
    }

    function makeFocusable($slides) {
        $slides.removeAttr('aria-hidden');
        $slides.find(focusableSelector).removeAttr('tabindex');
    }

    $(function() {
        var jcarousel = $('.jcarousel');

        jcarousel
            .on('jcarousel:visiblein', function(e) {
                makeFocusable($(e.target));
            })
            .on('jcarousel:visibleout', function(e) {
                var $slide = $(e.target);
                rescueFocus($slide, $(this).closest('.jcarousel-wrapper'));
                makeInert($slide);
            })
            .on('jcarousel:createend jcarousel:reloadend jcarousel:animate', function() {
                var element = $(this);
                var $wrapper = element.closest('.jcarousel-wrapper');

                var clones = element.find('[data-jcarousel-clone]');
                if (clones.length) {
                    rescueFocus(clones, $wrapper);
                    makeInert(clones);
                }

                var offscreen = element.jcarousel('items').not(element.jcarousel('visible'));
                rescueFocus(offscreen, $wrapper);
                makeInert(offscreen);
                makeFocusable(element.jcarousel('visible'));
            });

        jcarousel
            .on('jcarousel:create jcarousel:reload', function () {
                var element = $(this);
                width = element.innerWidth();

                // Set # of items per slide
                if (element.parent()[0].hasAttribute('data-jcarousel-perslide')) {
                    perSlide = element.parent().attr('data-jcarousel-perslide');
                    width = width / perSlide;
                } else {
                    if (width >= 600) {
                        width = width / 3;
                    } else if (width >= 350) {
                        width = width / 2;
                    }
                }
          
                element.jcarousel('items').css('width', width + 'px');
                element.find('.slide-meta').css('width', width + 'px');
                
                // "Stretch" image within slide if chosen
                if (element.parent()[0].hasAttribute('data-jcarousel-stretch')) {
                    stretch = element.parent().attr('data-jcarousel-stretch');
                    if (stretch == 'width') {
                        element.find('.exhibit-item-link').css({
                            'width': '100%',
                        });
                        element.find("img").css({
                			'width': '100%',
                			'object-fit': 'cover',
                		});
                    } else if (stretch == 'height') {
                        element.find('.exhibit-item-link').css({
                			'height': '100%',
                        });
                        element.find("img").css({
                            'height': '100%',
                			'object-fit': 'cover',
                        });
                    } else if (stretch == 'entire') {
                        element.find('.exhibit-item-link').css({
                            'width': '100%',
                            'height': '100%',
                        });
                        element.find("img").css({
                			'width': '100%',
                            'height': '100%',
                			'object-fit': 'cover',
                		});
                    }
                }
            })

            .on('jcarousel:createend', function(){
                var element = $(this).parent();
                
                // Add fade effect to pagination
                var pageMethod = element.attr('data-jcarousel-fade') == true ? 'fade' : 'scroll';
                
                // Reposition pagination arrows for narrower theme displays
                if (element.closest('#exhibit-blocks').width() < 1000) {
                    element.find('.jcarousel-control-prev').css('left', '10px');
                    element.find('.jcarousel-control-next').css('right', '10px');
                }

                element.find('.jcarousel-control-prev')
                    .jcarouselControl({
                        target: '-=1',
                        method: pageMethod
                    });

                element.find('.jcarousel-control-next')
                    .jcarouselControl({
                        target: '+=1',
                        method: pageMethod
                    });

                element.find('.jcarousel-pagination')
                    .on('jcarouselpagination:active', 'button', function() {
                        $(this).addClass('active');
                    })
                    .on('jcarouselpagination:inactive', 'button', function() {
                        $(this).removeClass('active');
                    })
                    .on('click', function(e) {
                        e.preventDefault();
                    })
                    .jcarouselPagination({
                        perPage: 1,
                        method: pageMethod,
                        item: function(page) {
                            return '<button type="button" data-slide-number-"' + page + '">' + page + '</button>';
                        }
                    });
            })
    });
})(jQuery);
