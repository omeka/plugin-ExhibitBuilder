if (typeof Omeka === 'undefined') {
    Omeka = {};
}
Omeka.ExhibitBuilder = {};

Omeka.ExhibitBuilder.setUpBlocks = function(blockFormUrl) {
    var blockIndex = jQuery('.block-form').length;
    jQuery('.add-link').click(function (event) {
        event.preventDefault();

        var newLayout = jQuery('input[name=new-block-layout]:checked').val();
        if (!newLayout) return;

        jQuery.get(
            blockFormUrl,
            {
                layout: newLayout,
                order: ++blockIndex
            },
            function (data) {
                jQuery(data).insertBefore('.add-block').trigger('exhibit-builder-refresh-wysiwyg');
                jQuery('input[name=new-block-layout]').prop('checked', false);
                jQuery('.selected').removeClass('selected');
            },
            'html'
        );
    });
    
    jQuery('.layout').click(function (event) {
        var thisLayout = jQuery(this).children('input[type="radio"]')[0];
        jQuery('.layout-thumbs input[type="radio"]').attr('checked', false);
        jQuery(thisLayout).attr('checked', true);
        jQuery('.selected').removeClass('selected');
        jQuery(this).addClass('selected');
    });

    jQuery('#block-container').on('click', '.remove-block, .remove-attachment', function (event) {
        event.preventDefault();
        jQuery(this).parent().remove();
    });
};

Omeka.ExhibitBuilder.setUpItemsSelect = function (itemOptionsUrl, attachmentUrl) {
    /*
     * Use AJAX to retrieve the list of items that can be attached.
     */
    function getItems(uri, parameters) {
        jQuery.ajax({
            url: uri,
            data: parameters,
            method: 'GET',
            success: function(data) {
                jQuery('#item-select').html(data);
                jQuery(document).trigger("omeka:loaditems");
            },
            error: function(xhr, textStatus, errorThrown) {
                alert('Error getting items: ' . textStatus);
            }
        });
    };

    /**
     * Use AJAX to load the form for an attachment.
     */
    this.loadItemOptionsForm = function(data) {
        jQuery.ajax({
            url: itemOptionsUrl,
            method: 'POST',
            dataType: 'html',
            data: data,
            complete: function (xhr, textStatus) {
                if (typeof data.caption !== 'undefined') {
                    if (!data.caption) {
                        data.caption = '';
                    }
                    tinymce.get('attachment-caption').setContent(data.caption);
                }
                jQuery('#attachment-item-options').html(xhr.responseText);
            }
        });
    };

    // Initially load the paginated items
    getItems(jQuery('#search').attr('action'));

    // Make search and pagination use AJAX to respond.
    jQuery('#search').submit(function(event) {
        event.preventDefault();
        getItems(this.action, jQuery(this).serialize());
    });
    jQuery('#search-items').on('click', '.pagination a, #view-all-items', function (event) {
        event.preventDefault();
        getItems(jQuery(this).attr('href'));
    });
    jQuery('#item-select').on('submit', '.pagination form', function (event) {
        event.preventDefault();
        getItems(jQuery(this).attr('action') + '?' + jQuery(this).serialize());
    });

    // Show/hide for the search form
    jQuery('#page-search-form').hide();
    jQuery('#show-or-hide-search').click(function () {
        var searchForm = jQuery('#page-search-form');
        if (searchForm.is(':visible')) {
            searchForm.hide();
        } else {
            searchForm.show();
        }

        var showHideLink = jQuery(this);
        showHideLink.toggleClass('show-form');
        if (showHideLink.hasClass('show-form')) {
            showHideLink.text('Show Search Form');
        } else {
            showHideLink.text('Hide Search Form');
        }
        return false;
    });

    // Make item listings selectable
    jQuery('#item-select').on('click', '.item-listing', function (event) {
        jQuery('#item-list div.item-selected').removeClass('item-selected');
        jQuery(this).addClass('item-selected');
    });

    // Hook select buttons to item options form
    jQuery('#item-select').on('click', '.select-item', function (event) {
        event.preventDefault();
        Omeka.ExhibitBuilder.loadItemOptionsForm(
            {item_id: jQuery('#search-items .item-selected').data('itemId')}
        );
        jQuery('#search-items').addClass('editing-attachment');
        jQuery(document).trigger('exhibit-builder-select-item');
    });

    jQuery('#change-selected-item').on('click', function (event) {
        event.preventDefault();
        jQuery('#search-items').removeClass('editing-attachment');
    });
};

Omeka.ExhibitBuilder.setUpAttachments = function (attachmentUrl) {
    function applyAttachment() {
        var options = jQuery('#attachment-options');
        data = getAttachmentData(options, false);

        var targetedItem = jQuery('.item-targeted').removeClass('item-targeted');
        var targetedBlock = targetedItem.parents('.block-form');
        data['block_index'] = targetedBlock.data('blockIndex');

        if (targetedItem.is('.attachment')) {
            data['index'] = targetedItem.data('attachment-index');
        } else {
            data['index'] = targetedBlock.find('.attachment').length + 1;
        }

        jQuery.ajax({
            url: attachmentUrl,
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

    function getAttachmentData(container, hidden) {
        var item_id, file_id, caption;

        if (hidden) {
            item_id = container.find('input[name*="[item_id]"]').val()
            file_id = container.find('input[name*="[file_id]"]').val();
            caption = container.find('input[name*="[caption]"]').val();
        } else {
            item_id = container.find('input[name="item_id"]').val()
            file_id = container.find('input[name="file_id"]:checked').val();
            caption = tinymce.get(container.find('textarea[name="caption"]').attr('id')).getContent();
        }
        
        return {
            'item_id': item_id,
            'file_id': file_id,
            'caption': caption,
        };
    }

    function targetAttachment(attachment) {
        jQuery('.item-targeted').removeClass('item-targeted');
        jQuery(attachment).addClass('item-targeted');
    }

    var searchItems = jQuery('#search-items');
    // Search Items Dialog Box
    searchItems.dialog({
        autoOpen: false,
        width: Math.min(jQuery(window).width() - 100, 600),
        height: Math.min(jQuery(window).height() - 100, 500),
        modal: true,
        create: function () {
            jQuery(this).dialog('widget')
                .draggable('option', {
                    containment: 'window',
                    scroll: false
                });
        },
        open: function () {
            jQuery('body').css('overflow', 'hidden');
        },
        beforeClose: function () {
            jQuery('body').css('overflow', 'inherit');
            jQuery('#attachment-item-options').empty();
        },
        position: {my: 'center', at: 'center center+22'},
        dialogClass: 'item-dialog'
    });

    jQuery(window).resize(function () {
        searchItems.dialog('option', 'position', {my: 'center', at: 'center center+22'})
    });

    jQuery('#apply-attachment').on('click', function (event) {
        event.preventDefault();
        applyAttachment();
        searchItems.dialog('close');
    });

    jQuery('#block-container').on('click', '.add-item', function (event) {
        event.preventDefault();
        targetAttachment(this);

        tinymce.get('attachment-caption').setContent('');
        searchItems.removeClass('editing-attachment').dialog('open');
    });

    jQuery('#block-container').on('click', '.edit-attachment a', function (event) {
        var attachment;
        event.preventDefault();

        attachment = jQuery(this).parent().parent();
        targetAttachment(attachment);
        Omeka.ExhibitBuilder.loadItemOptionsForm(getAttachmentData(attachment, true));
        jQuery(document).trigger('exhibit-builder-select-item');
        searchItems.addClass('editing-attachment').dialog('open');
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
