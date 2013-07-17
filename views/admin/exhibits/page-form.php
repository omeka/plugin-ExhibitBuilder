<?php
$title = ($actionName == 'Add') ? __('Add Page') : __('Edit Page "%s"', $exhibit_page->title);
echo head(array('title'=> $title, 'bodyclass'=>'exhibits'));
?>
<?php echo flash(); ?>
<div id="exhibits-breadcrumb">
    <a href="<?php echo html_escape(url('exhibits')); ?>"><?php echo __('Exhibits'); ?></a> &gt;
    <a href="<?php echo html_escape(url('exhibits/edit/' . $exhibit['id']));?>"><?php echo html_escape($exhibit['title']); ?></a>  &gt;
    <?php echo html_escape($title); ?>
</div>
<form method="post">
    <div class="seven columns alpha">
    <fieldset>
        <legend><?php echo __('Page Metadata'); ?></legend>
        <div class="field">
            <div class="two columns alpha">
            <?php echo $this->formLabel('title', __('Title')); ?>
            </div>
            <div class="inputs five columns omega">
            <?php echo $this->formText('title', $exhibit_page->title); ?>
            </div>
        </div>
        <div class="field">
            <div class="two columns alpha">
                <?php echo $this->formLabel('slug', __('Slug')); ?>
            </div>
            <div class="inputs five columns omega">
                <p class="explanation"><?php echo __('No spaces or special characters allowed'); ?></p>
                <?php echo $this->formText('slug', $exhibit_page->slug); ?>
            </div>
        </div>
    </fieldset>
    <fieldset id="block-container">
        <legend><?php echo __('Content'); ?></legend>
        <?php
        foreach ($exhibit_page->getPageBlocks() as $block):
            echo $this->partial('exhibits/block-form.php', array('block' => $block));
        endforeach;
        ?>
        <div class="add-block">
            <h2>New Content Block</h2>
            <div class="layout-select">
                <h4>Select layout</h3>
                <div class="layout-thumbs">
                <?php
                    $layouts = ExhibitLayout::getLayouts();
                    foreach ($layouts as $layout) {
                        echo '<div class="layout">';
                        echo '<img src="' . html_escape($layout->getIconUrl()) . '">';
                        echo '<span class="layout-name">' . $layout->name . '</span>';
                        echo '<input type="radio" name="new-block-layout" value="'. html_escape($layout->id) .'">';
                        echo '</div>';
                    }
                ?>
                <a class="add-link button" href="#">Add new content block</a>
                </div>
            </div>
        </div>
    </fieldset>
    </div>
    
    <div class="three columns omega">
        <div id="save" class="panel">
            <?php echo $this->formSubmit('continue', __('Save Changes'), array('class'=>'submit big green button')); ?>
            <?php echo $this->formSubmit('page_form', __('Save and Add Another Page'), array('class'=>'submit big green button')); ?>
            <?php if ($exhibit_page->exists()): ?>
                <?php echo exhibit_builder_link_to_exhibit($exhibit, __('View Public Page'), array('class' => 'big blue button', 'target' => '_blank'), $exhibit_page); ?>
            <?php endif; ?>
        </div>
    </div>
</form>
<?php //This item-select div must be outside the <form> tag for this page, b/c IE7 can't handle nested form tags. ?>
<div id="search-items" style="display:none;">
    <button type="button" id="show-or-hide-search" class="show-form blue"><?php echo __('Show Search Form'); ?></button>
    <a href="<?php echo url('exhibit-builder/items/browse'); ?>" id="view-all-items" class="green button"><?php echo __('View All Items'); ?></a>
    <div id="page-search-form" class="container-twelve">
    <?php
        $uri = url(array('controller'=>'exhibits', 'action'=>'items', 'page' => null));
        $formAttributes = array('id'=>'search');
        echo items_search_form($formAttributes);
    ?>
    </div>
    
    <div id="attachment-item-options"></div>
    <div id="item-select"></div>
</div>
<script type="text/javascript">
jQuery(document).ready(function () {
    var blockIndex = jQuery('.block-form').length;
    jQuery('.add-link').click(function (event) {
        event.preventDefault();

        var newLayout = jQuery('input[name=new-block-layout]:checked').val();
        if (!newLayout) {
            return;
        }
        
        jQuery.get(
            <?php echo json_encode(url('exhibits/block-form')); ?>,
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

    jQuery('#block-container').on('click', '.add-item', function (event) {
        event.preventDefault();
        jQuery(this).addClass('item-targeted');
        jQuery('#search-items').dialog('open');
    });
});

    jQuery(document).ready(function(){

        var exhibitBuilder = new Omeka.ExhibitBuilder();

        // Set the exhibit item uri
        exhibitBuilder.itemOptionsUri = <?php echo js_escape(url('exhibits/attachment-item-options')); ?>;
        exhibitBuilder.attachmentUri = <?php echo js_escape(url('exhibits/attachment')); ?>;

        // Set the paginated exhibit items uri
        exhibitBuilder.paginatedItemsUri = <?php echo js_escape(url('exhibit-builder/items/browse')); ?>;

        // Get the paginated items
        exhibitBuilder.getItems();

        jQuery('#page-search-form').hide();

        jQuery('#show-or-hide-search').click( function(){
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

        jQuery('#item-select').on('click', '.select-item', function (event) {
            exhibitBuilder.getItemOptionsForm(jQuery('#attachment-item-options'),
                {item_id: jQuery('#search-items .item-selected').data('itemId')});
            jQuery(document).trigger('exhibit-builder-select-item');
        });

        jQuery('#item-select').on('click', '.item-listing', function (event) {
            jQuery('#item-list div.item-selected').removeClass('item-selected');
            jQuery(this).addClass('item-selected');
        });

        // Search Items Dialog Box
         jQuery('#search-items').dialog({
             autoOpen: false,
             width: Math.min(jQuery(window).width() - 100, 820),
             height: Math.min(jQuery(window).height() - 100, 500),
             title: <?php echo js_escape(__('Attach an Item')); ?>,
             modal: true,
             buttons: [{
                id: 'apply-attachment',
                text: <?php echo js_escape(__('Apply')); ?>,
                click: function() {
                    exhibitBuilder.applyAttachment();
                    jQuery(this).dialog('close');
                }
             }],
             open: function() { jQuery('body').css('overflow', 'hidden'); },
             beforeClose: function() { jQuery('body').css('overflow', 'inherit'); }
         });
    });

    Omeka.wysiwyg();

    jQuery(document).bind('exhibit-builder-refresh-wysiwyg', function (event) {
        // Add tinyMCE to all textareas in the div where the item was attached.
        jQuery(event.target).find('textarea').each(function () {
            tinyMCE.execCommand('mceAddControl', false, this.id);
        });
    });
</script>
<?php echo foot(); ?>
