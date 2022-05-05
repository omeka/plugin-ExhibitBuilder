var Omeka = Omeka || {};
Omeka.ExhibitBuilder = {};

(function ($) {
    Omeka.ExhibitBuilder.deleteElement = function (element, event) {
        event.preventDefault();
        $(element).toggleClass('undo-delete')
            .parent().toggleClass('deleted')
            .siblings('div').toggleClass('frozen');

        var target = $(element).parent().parent();
        var removedClass = 'removed';
        if (!target.hasClass(removedClass)) {
            target.addClass(removedClass);
            target.find('input, select, textarea').prop('disabled', true);
        } else {
            target.removeClass(removedClass);
            target.find('input, select, textarea').each(function () {
                if (!$(element).parent().parent().hasClass(removedClass)) {
                    element.disabled = false;
                }
            });
        }
    }
    Omeka.ExhibitBuilder.setSearchVisibility = function(show) {
        var searchForm = $('#page-search-form');

        if (typeof show === 'undefined') {
            show = !searchForm.is(':visible');
        }

        $('.search-toggle.active').removeClass('active');

        if (show) {
            searchForm.show();
            $('.hide-form').addClass('active');
        } else {
            searchForm.hide();
            $('.show-form').addClass('active');
        }
    }

    Omeka.ExhibitBuilder.loadItemOptionsForm = function(data, itemOptionsUrl, panel, options) {
        $(panel).addClass('loading');
        $.ajax({
            url: itemOptionsUrl,
            method: 'POST',
            dataType: 'html',
            data: data,
            success: function (response) {
                if (typeof data.caption !== 'undefined') {
                    if (!data.caption) {
                        data.caption = '';
                    }
                    tinymce.get('attachment-caption').setContent(data.caption);
                }
                $(options).html(response);
            },
            complete: function() {
                $(panel).removeClass('loading');
            }
        });
    };
    Omeka.ExhibitBuilder.setUpBlocks = function(blockFormUrl) {
        function sortAttachments(ancestor) {
            $(ancestor).find('.selected-item-list').sortable({
                items: '> .attachment',
                revert: 200,
                placeholder: 'ui-sortable-highlight',
                tolerance: 'pointer',
                stop: function () {
                    $(this).find('.attachment-order').each(function (index) {
                        $(this).val(index + 1);
                    });
                }
            });
        }

        $('#block-container').sortable({
            items: '> .block-form',
            handle: '> .sortable-item',
            revert: 200,
            placeholder: 'ui-sortable-highlight',
            tolerance: 'pointer',
            forcePlaceholderSize: true,
            forceHelperSize: false,
            helper: 'clone',
            start: function (event, ui) {
                ui.item.find('textarea').each(function () {
                    tinyMCE.EditorManager.execCommand('mceRemoveEditor', false, this.id);
                });
                ui.helper.find('.block-body').hide();
                var height = ui.helper.find('.block-header').outerHeight();
                ui.helper.height(height);
                ui.placeholder.height(height);
            },
            stop: function (event, ui) {
                $(this).find('.block-order').each(function (index) {
                    $(this).val(index + 1);
                });
                ui.item.find('textarea').each(function () {
                    tinyMCE.EditorManager.execCommand('mceAddEditor', false, this.id);
                });
            }
        });
        
        var blockIndex = $('.block-form').length;

        $('.add-link').hide();
        $('.add-link').click(function (event) {
            event.preventDefault();

            var newLayout = $('input[name=new-block-layout]:checked').val();
            if (!newLayout) return;

            $.get(
                blockFormUrl,
                {
                    layout: newLayout,
                    order: ++blockIndex
                },
                function (data) {
                    $(data)
                        .insertBefore('.add-block')
                        .trigger('exhibit-builder-refresh-wysiwyg')
                        .trigger('exhibit-builder-add-block')
                        ;
                    $('input[name=new-block-layout]').prop('checked', false);
                    $('.selected').removeClass('selected');
                    $('.add-link').hide();
                },
                'html'
            );
        });
        
        $('.layout').click(function (event) {
            var layout_id = $(this).attr('id');
            $(this).children('input[type="radio"]').prop('checked', true);
            $('.selected').removeClass('selected');
            $(this).addClass('selected');
            $('.'+layout_id + '.layout-description').addClass('selected');
            $('.add-link').show();
        });

        $('#block-container').on('click', '.delete-element', function (event) {
            Omeka.ExhibitBuilder.deleteElement(this, event);
        });

        $('#block-container').on('exhibit-builder-add-block', '.block-form', function () {
            sortAttachments(this);
        });

        $('#block-container').on('click', '.drawer-toggle', function() {
            $(this).toggleClass('opened');
        });

        $('#block-container .collapse').click(function() {
            $('.sortable-item .drawer-toggle').removeClass('opened');
            $('.block-body').removeClass('opened');
        });

        $('#block-container .expand').click(function() {
            $('.sortable-item .drawer-toggle').addClass('opened');
            $('.block-body').addClass('opened');
        });

        $('#block-container').on('click', '.block-header .drawer-toggle', function (event) {
            event.preventDefault();
            $(this).parent().siblings('.block-body').toggleClass('opened');
        });

        $('#block-container').on('click', '.layout-options .drawer-toggle', function (event) {
            event.preventDefault();
            $(this).parent().siblings('div').toggleClass('opened');
        });

        sortAttachments('#block-container');
    };

    Omeka.ExhibitBuilder.themeConfig = function() {
        if ($('#theme').val() === '') {
            $('.configure-button').hide();
        }
        
        $('#theme').change(function() {
            if ($(this).val() === '') {
                $('.configure-button').hide();
            } else {
                $('.configure-button').show();
            }
        });
    }; 

    Omeka.ExhibitBuilder.setUpItemsSelect = function (itemOptionsUrl) {
        /*
         * Use AJAX to retrieve the list of items that can be attached.
         */
        function getItems(uri, parameters) {
            $('#attachment-panel').addClass('loading');
            $.ajax({
                url: uri,
                data: parameters,
                method: 'GET',
                success: function(data) {
                    $('#item-select').html(data);
                    $(document).trigger("omeka:loaditems");
                },
                error: function(xhr, textStatus, errorThrown) {
                    alert('Error getting items: ' . textStatus);
                },
                complete: function() {
                    $('#attachment-panel').removeClass('loading');
                }
            });
        }

        // Initially load the paginated items
        getItems($('#search').attr('action'));

        // Make search and pagination use AJAX to respond.
        $('#search').submit(function(event) {
            event.preventDefault();
            getItems(this.action, $(this).serialize());
            Omeka.ExhibitBuilder.setSearchVisibility(false);
        });
        $('#item-form').on('click', '.pagination a, #view-all-items', function (event) {
            event.preventDefault();
            getItems(this.href);
            Omeka.ExhibitBuilder.setSearchVisibility(false);
        });
        $('#item-select').on('submit', '.pagination form', function (event) {
            event.preventDefault();
            getItems(this.action + '?' + $(this).serialize());
            Omeka.ExhibitBuilder.setSearchVisibility(false);
        });

        Omeka.ExhibitBuilder.setSearchVisibility(false);
        $('.search-toggle').click(function (event) {
            event.preventDefault();
            Omeka.ExhibitBuilder.setSearchVisibility();
        });

        // Hook select buttons to item options form
        $('#item-select').on('click', '.select-item', function (event) {
            event.preventDefault();
            var data = {item_id: $(this).parent().data('itemId')};
            Omeka.ExhibitBuilder.loadItemOptionsForm(data, itemOptionsUrl, '#attachment-panel', '#attachment-item-options');
            $('#attachment-panel')
                .addClass('editing-attachment')
                .removeClass('editing-selection');
            $(document).trigger('exhibit-builder-select-item');
        });

        $('#change-selected-item').on('click', function (event) {
            event.preventDefault();
            $('#attachment-panel')
                .removeClass('editing-attachment')
                .addClass('editing-selection');
        });

        $('#revert-selected-item').on('click', function (event) {
            event.preventDefault();
            $('#attachment-panel')
                .addClass('editing-attachment')
                .removeClass('editing-selection');
        });
    };

    Omeka.ExhibitBuilder.setUpAttachments = function (attachmentUrl, itemOptionsUrl) {
        function applyAttachment() {
            var options = $('#attachment-options');
            var data = getAttachmentData(options, false);

            var targetedItem = $('.item-targeted').removeClass('item-targeted');
            var targetedBlock = targetedItem.parents('.block-form');
            data.block_index = targetedBlock.data('blockIndex');

            if (targetedItem.is('.attachment')) {
                data.index = targetedItem.data('attachment-index');
            } else {
                data.index = targetedBlock.find('.attachment').length;
            }

            $.ajax({
                url: attachmentUrl,
                method: 'POST',
                dataType: 'html',
                data: data,
                success: function (response) {
                    if (targetedItem.is('.attachment')) {
                        targetedItem.replaceWith(response);
                    } else {
                        targetedBlock.find('.add-item').before(response);
                    }
                }
            });
        }

        function getAttachmentData(container, hidden) {
            var item_id, file_id, caption;

            if (hidden) {
                item_id = container.find('input[name*="[item_id]"]').val();
                file_id = container.find('input[name*="[file_id]"]').val();
                caption = container.find('input[name*="[caption]"]').val();
            } else {
                item_id = container.find('input[name="item_id"]').val();
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
            $('.item-targeted').removeClass('item-targeted');
            $(attachment).addClass('item-targeted');
        }

        var attachmentPanel = $('#attachment-panel');
        Omeka.ExhibitBuilder.createDialog(attachmentPanel);
        
        $('#attachment-item-options').on('click','.file-select .item-file', function(event) {
            $(this).find('input[type="radio"]').prop('checked', true);
            $('.selected').removeClass('selected');
            $(this).addClass('selected');
        });

        $('#apply-attachment').on('click', function (event) {
            event.preventDefault();
            applyAttachment();
            attachmentPanel.dialog('close');
        });

        $('#block-container').on('click', '.add-item', function (event) {
            event.preventDefault();
            targetAttachment(this);

            tinymce.get('attachment-caption').setContent('');
            attachmentPanel
                .removeClass('editing-attachment')
                .removeClass('editing-selection')
                .dialog('open');
        });

        $('#block-container').on('click', '.edit-attachment', function (event) {
            var attachment;
            event.preventDefault();

            attachment = $(this).parent();
            targetAttachment(attachment);
            Omeka.ExhibitBuilder.loadItemOptionsForm(getAttachmentData(attachment, true), itemOptionsUrl, '#attachment-panel', '#attachment-item-options');
            $(document).trigger('exhibit-builder-select-item');
            attachmentPanel.addClass('editing-attachment').dialog('open');
        });
    };

    /**
     * Enable drag and drop sorting for elements.
     */
    Omeka.ExhibitBuilder.enableSorting = function () {
        $('.sortable').nestedSortable({
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
        $('#page-list .delete-element').click(function (event) {
            event.preventDefault();
            var header = $(this).parent();
            if ($(this).hasClass('delete-element')) {
                $(this).removeClass('delete-element').addClass('undo-delete');
                header.addClass('deleted');
            } else {
                $(this).removeClass('undo-delete').addClass('delete-element');
                header.removeClass('deleted');
            }
        });
    };

    Omeka.ExhibitBuilder.setUpFormSubmission = function () {
        $('#exhibit-metadata-form').submit(function (event) {
            // add ids to li elements so that we can pull out the parent/child relationships
            var listData = $('#page-list').nestedSortable('serialize');
            var deletedIds = [];
            $('#page-list .deleted').each(function () {
                deletedIds.push($(this).parent().attr('id').match(/_(.*)/)[1]);
            });
            
            $('#pages-hidden').val(listData);
            $('#pages-delete-hidden').val(deletedIds.join(','));
        });
    };

    Omeka.ExhibitBuilder.setUpPageValidate = function (validateUrl) {
        $('#exhibit-page-form').submit(function (event) {
            $('.page-validate-message').remove();

            $.ajax({
                url: validateUrl,
                method: 'POST',
                dataType: 'json',
                data: $('#title, #slug').serialize(),
                async: false,
                success: function (response) {
                    if (!response.success) {
                        event.preventDefault();
                        $(document).scrollTop(0);
                        $.each(response.messages, function (key, value) {
                            var message = '<span class="error page-validate-message">' + value + '</span>';
                            jQuery('#' + key).after(message)
                                .parent().effect('shake', {distance: 10});
                        });
                    }
                }
            });
        });
    };

    Omeka.ExhibitBuilder.setUpCoverImageChooser = function (coverImageChooserUrl, itemOptionsUrl) {
        var coverImagePanel = $('#cover-image-panel');
        var selected_cover_image_id = $('#cover_image_file_id').val();
        
        Omeka.ExhibitBuilder.createDialog(coverImagePanel);

        function getCoverImageData(container) {
            var item_id, file_id;

            item_id = container.find('input[name="cover_image_item_id"]').val();
            file_id = container.find('input[name="cover_image_file_id"]').val();

            return {
                'item_id': item_id,
                'file_id': file_id,
            };
        }

        // Hook select buttons to item options form
        $('#item-select').on('click', '.select-item', function (event) {
            event.preventDefault();
            var data = {item_id: $(this).parent().data('itemId')};
            Omeka.ExhibitBuilder.loadItemOptionsForm(data, itemOptionsUrl, '#cover-image-panel', '#cover-image-item-options');
            $('#cover-image-panel')
                .addClass('editing-cover-image')
                .removeClass('editing-selection');
            $(document).trigger('exhibit-builder-select-item');
        });

        function chooseCoverImage(fileId){
            $.ajax({
                url: coverImageChooserUrl ,
                method: 'GET',
                dataType: 'html',
                data: {"id": fileId},
                success: function (response) {
                    $('.cover-image-form-elements').replaceWith(response);
                    $('.cover-image-form-elements').addClass('attached');
                },
                error: function(xhr, textStatus, errorThrown) {
                    alert('Error getting items: ' . textStatus);
                }
            });
        }

        $('#cover-image-container').on('click', '.edit-cover-image', function (event) {
            var coverImage;
            event.preventDefault();

            if($(this).prop('id') == 'first-time-cover-image'){
                coverImagePanel
                    .removeClass('editing-cover-image')
                    .removeClass('editing-selection')
                    .dialog('open');
            } else {
                coverImage = $(this).parent();
                Omeka.ExhibitBuilder.loadItemOptionsForm(getCoverImageData(coverImage), itemOptionsUrl, '#cover-image-panel', '#cover-image-item-options');
                coverImagePanel.addClass('editing-cover-image').dialog('open');
            }
        });

        $('#choose-cover-image').on('click', function (event) {
            file_id = $('input[name="file_id"]:checked').val();
            event.preventDefault();
            chooseCoverImage(file_id);
            coverImagePanel.dialog('close');
        });

        $('#cover-image-item-options').on('click','.file-select .item-file', function(event) {
            $(this).find('input[type="radio"]').prop('checked', true);
            $('.selected').removeClass('selected');
            $(this).addClass('selected');
        });

        $('#change-selected-item').on('click', function (event) {
            event.preventDefault();
            $('#cover-image-panel')
                .removeClass('editing-cover-image')
                .addClass('editing-selection');
        });

        $('#revert-selected-item').on('click', function (event) {
            event.preventDefault();
            $('#cover-image-panel')
                .removeClass('editing-selection')
                .addClass('editing-cover-image');
        });
    }

    Omeka.ExhibitBuilder.setUpCoverImageSelect = function(browseUri) {
        /*
         * Use AJAX to retrieve the list of items that can be attached.
         */
        function getItems(uri, parameters) {
            if(typeof parameters == "undefined")
                parameters = "search=";
            $('#cover-image-panel').addClass('loading');
            parameters+= "&hasImage=1";
            $.ajax({
                url: uri,
                data: parameters,
                dataType: 'html',
                method: 'GET',
                success: function(data) {
                    $('#item-select').html(data);
                },
                error: function(xhr, textStatus, errorThrown) {
                    alert('Error getting items: ' . textStatus);
                },
                complete: function() {
                    $('#cover-image-panel').removeClass('loading');
                }
            });
        }

        getItems(browseUri);

        // Make search and pagination use AJAX to respond.
        $('#search').submit(function(event) {
            event.preventDefault();
            getItems(this.action, $(this).serialize());
            Omeka.ExhibitBuilder.setSearchVisibility(false);
        });

        Omeka.ExhibitBuilder.setSearchVisibility(false);
        $('.search-toggle').click(function (event) {
            event.preventDefault();
            Omeka.ExhibitBuilder.setSearchVisibility();
        });

        $('#item-form').on('click', '.pagination a, #view-all-items', function (event) {
            event.preventDefault();
            getItems(this.href);
            Omeka.ExhibitBuilder.setSearchVisibility(false);
        });

        $('#cover-image-container').on('click', '.delete-element', function (event) {
            Omeka.ExhibitBuilder.deleteElement(this, event);
        });
    }

    Omeka.ExhibitBuilder.createDialog = function (panel) {
        panel.dialog({
            autoOpen: false,
            modal: true,
            resizable: false,
            create: function () {
                $(this).dialog('widget').draggable('option', {
                    containment: 'window',
                    scroll: false
                });
            },
            open: function () {
                function refreshDialog() {
                    panel.dialog('option', {
                        width: Math.min($(window).width() - 100, 600),
                        height: Math.min($(window).height() - 100, 500),
                        position: {my: 'center', at: 'center center+22', of: window}
                    });
                }

                refreshDialog();
                $('body').css('overflow', 'hidden');
                $(window).on('resize.ExhibitBuilder', function () {
                    refreshDialog();
                });
            },
            beforeClose: function () {
                $('body').css('overflow', 'inherit');
                $(window).off('resize.ExhibitBuilder');
                $('#attachment-item-options').empty();
            },
            dialogClass: 'item-dialog'
        });
    }

    $.widget('ui.dialog', $.ui.dialog, {
        _allowInteraction: function (event) {
            if (this._super(event)) {
                return true;
            }

            if ($(event.target).closest('.mce-window').length) {
                return true;
            }
        }
    });
})(jQuery);
