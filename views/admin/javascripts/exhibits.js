if (typeof Omeka === 'undefined') {
    Omeka = {};
}

Omeka.ExhibitBuilder = function() {
    
    this.paginatedItemsUri = ''; // Used to get a paginated list of items for the item search
    this.itemContainerUri = ''; // Used to get a single item container

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

        // Setup layout item Containers
        this.setupLayoutItemContainers();
        
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
    * Setup the item containers located in the main layout
    */
    this.setupLayoutItemContainers = function() {
        var exhibitBuilder = this;
        var layoutItemContainers = jQuery('#layout-form div.item-select-outer');
        jQuery.each(layoutItemContainers, function(index, rawLayoutItemContainer) {
            var layoutItemContainer = jQuery(rawLayoutItemContainer);
            exhibitBuilder.setupLayoutItemContainer(layoutItemContainer);
        });
    };
    
    /*
    * Setup an item container located in the main layout
    */
    this.setupLayoutItemContainer = function(layoutItemContainer) {        
        
        // Add delete buttons to the layout item container
        this.addDeleteButtonsToLayoutItemContainer(layoutItemContainer);
        
        // Attach Item Dialog Link
        layoutItemContainer.find('.attach-item-link').click(function(){
            jQuery(this).parent().addClass('item-targeted');
            jQuery('#search-items').dialog('open');
            return false;
        });
        
        Omeka.ExhibitBuilder.addNumbers();
        
        jQuery(layoutItemContainer).trigger("exhibitbuilder:attachitem");
        
    };
    
    /*
    * Setup the item containers located in the search box
    */
    this.setupSearchItemContainers = function() {
        var exhibitBuilder = this;
        var searchItemContainers = jQuery('#item-select div.item-select-outer');
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
          dataType: 'html',
          method: 'GET',
          complete: function(xhr, textStatus) {
              jQuery('#item-select').html(xhr.responseText);
              jQuery(document).trigger("omeka:loaditems");
          }
        });
    };

    /*
    * Add delete buttons to the layout item containers 
    */
    this.addDeleteButtonsToLayoutItemContainer = function(layoutItemContainer) {
        // Only add a Remove Item link to the layout item container if it has an item
        if (layoutItemContainer.find('div.item-select-inner').size()) {
            var removeItemLink = jQuery('<a></a>');
            removeItemLink.html(this.removeItemText);
            removeItemLink.addClass('remove_item delete-item red button');
            removeItemLink.css('cursor', 'pointer');
            
            // Put the 'delete' as background to anything with a 'remove_item' class
            // removeItemLink.css({
            //     'padding-left': '20px'
            //     });            

            // Make the remove item link delete the item when clicked
            removeItemLink.bind('click', {exhibitBuilder: this}, function(event) {
                event.data.exhibitBuilder.deleteItemFromItemContainer(layoutItemContainer);
                return;
            });
            layoutItemContainer.append(removeItemLink);
        }           
    };

    /*
    * Add selection highlighting to the search item containers 
    */
    this.addSelectionHighlightingToSearchItemContainer = function(searchItemContainer) {
        searchItemContainer.bind('click', {exhibitBuilder: this}, function(event) {
            jQuery('#item-list div.item-select-outer').removeClass('item-selected');
            jQuery(this).addClass('item-selected');
            return;
        });
    };
    
    /*
    * Attach the selected item from a search item container to a layout item container
    */
    this.attachSelectedItem = function() {
        var selectedItemContainer = jQuery('.item-selected');
        var selectedItemId = selectedItemContainer.data('itemId');
        var targetedItemContainer = jQuery('.item-targeted');
        var targetedItemOrder = this.getItemOrderFromItemContainer(targetedItemContainer);      
        this.setItemForItemContainer(targetedItemContainer, selectedItemId, targetedItemOrder);         
    }
    
    /*
    * Deletes an item from the item container
    */
    this.deleteItemFromItemContainer = function(itemContainer) {
        var orderOnForm = this.getItemOrderFromItemContainer(itemContainer);
        this.setItemForItemContainer(itemContainer, 0, orderOnForm);
        
    };

    /*
    * Sets the item for a container.  It uses Ajax to dynamically get a new item container
    */
    this.setItemForItemContainer = function(itemContainer, itemId, orderOnForm) {
        var exhibitBuilder = this;        
        jQuery.ajax({
          url: this.itemContainerUri,
          data: {'item_id': itemId, 'order_on_form': orderOnForm},
          method: 'POST',
          complete: function(xhr, textStatus) {
              var newItemContainer = jQuery(xhr.responseText);
              itemContainer.replaceWith(newItemContainer);
              exhibitBuilder.setupLayoutItemContainer(newItemContainer);
              
          }
        });
    };

    /*
    * Get the order of the item (if any) in the item container
    */
    this.getItemOrderFromItemContainer = function(itemContainer) {
        // for some weird reason, itemContainer.find("input[id^='" + itemOrderPrefix + "']").first() does not work, 
        // so we assume that their is only one input whose id begins with the 'Item-' in the item container
        var itemOrderPrefix = 'Item-';
        var itemOrderInput = itemContainer.find("input[id^='" + itemOrderPrefix + "']");
        if (itemOrderInput) {
            return itemOrderInput.attr('id').substring(itemOrderPrefix.length);
        }
        return false;
    };
}

Omeka.ExhibitBuilder.wysiwyg = function() {
    Omeka.wysiwyg();
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
