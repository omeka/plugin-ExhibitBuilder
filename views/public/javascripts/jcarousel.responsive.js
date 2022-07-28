(function($) {
    $(function() {
        var jcarousel = $('.jcarousel');
        
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
                    .on('jcarouselpagination:active', 'a', function() {
                        $(this).addClass('active');
                    })
                    .on('jcarouselpagination:inactive', 'a', function() {
                        $(this).removeClass('active');
                    })
                    .on('click', function(e) {
                        e.preventDefault();
                    })
                    .jcarouselPagination({
                        perPage: 1,
                        method: pageMethod,
                        item: function(page) {
                            return '<a href="#' + page + '">' + page + '</a>';
                        }
                    });
            })
    });
})(jQuery);
