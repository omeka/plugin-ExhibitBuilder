if (typeof Omeka === 'undefined') {
    Omeka = {};
}

Omeka.ExhibitBuilder = function() {
    
    this.paginatedItemsUri = ''; // Used to get a paginated list of items for the item search
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
        
        // Setup search item Containers
        this.setupSearchItemContainers();

        // Make the search form respond with ajax power
        jQuery('#search').bind('submit', {exhibitBuilder:this}, function(event){
            event.stopPropagation();
            event.data.exhibitBuilder.searchItems(jQuery('#search'));
            return false;
        });
    };
    
    jQuery(document).bind('omeka:loaditems', 
                          {exhibitBuilder:this}, 
                          function(event){
                              event.data.exhibitBuilder.loadPaginatedSearch()
                          });
    
    /*
    * Setup the item containers located in the search box
    */
    this.setupSearchItemContainers = function() {
        var exhibitBuilder = this;
        var searchItemContainers = jQuery('#item-select div.item-listing');
        jQuery.each(searchItemContainers, function(index, rawSearchItemContainer) {
            var searchItemContainer = jQuery(rawSearchItemContainer);
            exhibitBuilder.setupSearchItemContainer(searchItemContainer);
        });
    }
    
    /*
    * Setup an item container located in the search box
    */
    this.setupSearchItemContainer = function(searchItemContainer) {
        // Add selection highlighting to the search item container
        this.addSelectionHighlightingToSearchItemContainer(searchItemContainer);
    };
    
    /*
    * Use AJAX request to retrieve the list of items that can be used in the exhibit.
    */
    this.getItems = function(uri, parameters) {          
         
         if (!uri || uri.length == 0) {
             uri = this.paginatedItemsUri;
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
                // Activate the buttons on the advanced search
                //Omeka.Search.activateSearchButtons();
                
                if (fireEvents) {
                    jQuery(document).trigger("omeka:loaditems");
                }
           }
         });
    };

    /*
    * Process the search form 
    */
    this.searchItems = function(searchForm)
    {
        // The advanced search form automatically puts 'items/browse' as the 
        // action URI.  We need to hack that with Javascript to make this work.
        // This will give it the URI exhibits/items or whatever is set in
        // page-form.php.
        searchForm.attr('action', this.paginatedItemsUri);
        jQuery.ajax({
          url: this.paginatedItemsUri,
          data: searchForm.serialize(),
          method: 'POST',
          complete: function(xhr, textStatus) {
              jQuery('#item-select').html(xhr.responseText);
              jQuery(document).trigger("omeka:loaditems");
          }
        });
    };

    /*
    * Add selection highlighting to the search item containers 
    */
    this.addSelectionHighlightingToSearchItemContainer = function(searchItemContainer) {
        searchItemContainer.bind('click', {exhibitBuilder: this}, function(event) {
            jQuery('#item-list div.item-selected').removeClass('item-selected');
            jQuery(this).addClass('item-selected');
            return;
        });
    };

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

Omeka.ExhibitBuilder.addNumbers = function() {
    jQuery('#layout-form .exhibit-form-element').each(function(i){
        var number = i+1;
        if (jQuery(this).find('.exhibit-form-element-number').length == 0) {
            jQuery(this).append('<div class="exhibit-form-element-number">'+number+'</div>'); 
        }
    });
}


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
