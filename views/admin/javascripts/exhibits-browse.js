if (!Omeka) {
    var Omeka = {};
}

Omeka.ExhibitsBrowse = {};

(function ($) {
    Omeka.ExhibitsBrowse.setupDetails = function (detailsText, showDetailsText, hideDetailsText) {
        $('.details').hide();
        $('.action-links').prepend('<li class="details-link">' + detailsText + '</li> ');

        $('tr.exhibit').each(function() {
            var exhibitDetails = $(this).find('.details');
            if ($.trim(exhibitDetails.html()) != '') {
                $(this).find('.details-link').css({'color': '#4E7181', 'cursor': 'pointer'}).click(function() {
                    exhibitDetails.slideToggle('fast');
                });
            }
        });

        var toggleList = '<a href="#" class="toggle-all-details small blue button">' + showDetailsText + '</a>';

        $('.quick-filter-wrapper').before(toggleList);

        // Toggle exhibit details.
        var detailsShown = false;
        $('.toggle-all-details').click(function (e) {
            e.preventDefault();
            if (detailsShown) {
            	$('.toggle-all-details').text(showDetailsText);
            	$('.details').slideUp('fast');
            } else {
            	$('.toggle-all-details').text(hideDetailsText);
            	$('.details').slideDown('fast');
            }
            detailsShown = !detailsShown;
        });
    };
})(jQuery);
