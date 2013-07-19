if (typeof Omeka === 'undefined') {
    Omeka = {};
}

Omeka.ExhibitBuilder = function() {
    this.attachmentUri = ''; // Used to get a single item container
    this.itemOptionsUri = '';

    /*
    * Load paginated search
    */
    this.loadPaginatedSearch = function() {
        var eb = this;
        // Make each of the pagination links fire an additional ajax request
        jQuery('.pagination a, #view-all-items').click(function (event) {
            event.preventDefault();
            eb.getItems(jQuery(this).attr('href'));
        });

        jQuery('.pagination form').submit(function (event) {
            event.preventDefault();
            var url = jQuery(this).attr('action') + '?' + jQuery(this).serialize();
            eb.getItems(url);
        });
    };
    
    jQuery(document).bind('omeka:loaditems', 
                          {exhibitBuilder:this}, 
                          function(event){
                              event.data.exhibitBuilder.loadPaginatedSearch()
                          });

    this.getItemOptionsForm = function(container, data) {
        jQuery.ajax({
            url: this.itemOptionsUri,
            method: 'POST',
            dataType: 'html',
            data: data,
            complete: function (xhr, textStatus) {
                container.html(xhr.responseText);
                container.trigger('exhibit-builder-refresh-wysiwyg');
            }
        });
    };

    this.applyAttachment = function () {
        var options = jQuery('#attachment-item-options');
        var item_id = options.find('input[name="item_id"]').val();
        var file_id = options.find('input[name="file_id"]:checked').val();
        var captionId = options.find('textarea[name="caption"]').attr('id');
        var caption = tinymce.get(captionId).getContent();
        data = {
            'item_id': item_id,
            'file_id': file_id,
            'caption': caption,
        };

        var targetedItem = jQuery('.item-targeted').removeClass('item-targeted');
        var targetedBlock = targetedItem.parent();
        data['block_index'] = targetedBlock.data('blockIndex');
        data['index'] = targetedBlock.find('.attachment').length;

        options.empty();
        jQuery.ajax({
            url: this.attachmentUri,
            method: 'POST',
            dataType: 'html',
            data: data,
            success: function (response) {
                if (targetedItem.is('.attachment')) {
                    targetedItem.replaceWith(response);
                } else {
                    targetedBlock.find('.selected-item-list').append(response);
                }
            }
        });
    };
}

/*
* Use AJAX request to retrieve the list of items that can be used in the exhibit.
*/
Omeka.ExhibitBuilder.getItems = function(uri, parameters) {
    if (!uri || uri.length == 0) {
        uri = jQuery('#search').attr('action');
    }

    var fireEvents = false;
    jQuery.ajax({
        url: uri,
        data: parameters,
        method: 'GET',
        success: function(data) {
            jQuery('#item-select').html(data);
            fireEvents = true;
        },
        error: function(xhr, textStatus, errorThrown) {
            alert('Error getting items: ' . textStatus);
        },
        complete: function(xhr, textStatus) {
            if (fireEvents) {
                jQuery(document).trigger("omeka:loaditems");
            }
        }
    });
};

Omeka.ExhibitBuilder.addNumbers = function() {
    jQuery('#layout-form .exhibit-form-element').each(function(i){
        var number = i+1;
        if (jQuery(this).find('.exhibit-form-element-number').length == 0) {
            jQuery(this).append('<div class="exhibit-form-element-number">'+number+'</div>'); 
        }
    });
};


/**
 * Enable drag and drop sorting for elements.
 */
Omeka.ExhibitBuilder.enableSorting = function () {
    jQuery('.sortable').nestedSortable({
        listType: 'ul',
        items: 'li.page',
        handle: '.sortable-item',
        revert: 200,
        forcePlaceholderSize: true,
        forceHelperSize: true,
        toleranceElement: '> div',
        placeholder: 'ui-sortable-highlight',
        containment: 'document',
        maxLevels: 3
    });
};

Omeka.ExhibitBuilder.activateDeleteLinks = function () {
    jQuery('#page-list .delete-toggle').click(function (event) {
        event.preventDefault();
        header = jQuery(this).parent();
        if (jQuery(this).hasClass('delete-element')) {
            jQuery(this).removeClass('delete-element').addClass('undo-delete');
            header.addClass('deleted');
        } else {
            jQuery(this).removeClass('undo-delete').addClass('delete-element');
            header.removeClass('deleted');
        }
    });
};

Omeka.ExhibitBuilder.setUpFormSubmission = function () {
    jQuery('#exhibit-metadata-form').submit(function (event) {
        // add ids to li elements so that we can pull out the parent/child relationships
        var listData = jQuery('#page-list').nestedSortable('serialize');
        var deletedIds = [];
        jQuery('#page-list .deleted').each(function () {
            deletedIds.push(jQuery(this).parent().attr('id').match(/_(.*)/)[1]);
        });
        
        jQuery('#pages-hidden').val(listData);
        jQuery('#pages-delete-hidden').val(deletedIds.join(','));
    });
};
