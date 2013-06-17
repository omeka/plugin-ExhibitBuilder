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
    <fieldset>
        <legend><?php echo __('Content'); ?></legend>
        <?php
            $block = new ExhibitPageBlock;
            $block->id = 0;
            $block->layout = 'file-text';
            echo $this->partial('exhibits/block-form.php', array('block' => $block));
        ?>
        <div class="add-block block-form">
            <span class="add-link"><a href="#">Add new content block</a></span>
            <div class="layout-select">
                <h4>Select layout</h3>
                <div class="layout-thumbs">
                <?php
                    $layouts = ExhibitLayout::getLayouts();
                    foreach ($layouts as $layout) {
                        echo $layout->name;
                        echo '<img src="' . html_escape($layout->getIconUrl()) . '">';
                        echo '<input type="radio" name="new-block-layout" value="'. html_escape($layout->id) .'">';
                    }
                ?>
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
<script type="text/javascript" charset="utf-8">
//<![CDATA[

    jQuery(document).ready(function() {
        makeLayoutSelectable();
    });

    function makeLayoutSelectable() {
        //Make each layout clickable
        jQuery('div.layout').bind('click', function(e) {
            jQuery('#layout-thumbs').find('div.current-layout').removeClass('current-layout');
            jQuery(this).addClass('current-layout');

            // Remove the old chosen layout
            jQuery('#chosen_layout').find('div.layout').remove()
            jQuery('#chosen_layout').find('p').remove();

            // Copy the chosen layout
            var copyLayout = jQuery(this).clone();

            // Take the form input out of the copy (so no messed up forms).
            copyLayout.find('input').remove();

            // Change the id of the copy
            copyLayout.attr('id', 'chosen_' + copyLayout.attr('id')).removeClass('current-layout');

            // Append the copy layout to the chosen_layout div
            copyLayout.appendTo('#chosen_layout');

            // Check the radio input for the layout
            jQuery(this).find('input').attr('checked', true);
        });
    }
//]]>
</script>
<?php //This item-select div must be outside the <form> tag for this page, b/c IE7 can't handle nested form tags. ?>
<div id="search-items" style="display:none;">
    <div id="item-select"></div>
</div>
<script type="text/javascript" charset="utf-8">
//<![CDATA[

    jQuery(document).ready(function(){

        var exhibitBuilder = new Omeka.ExhibitBuilder();

        // Set the exhibit item uri
        exhibitBuilder.itemContainerUri = <?php echo js_escape(url('exhibits/item-container')); ?>;

        // Set the paginated exhibit items uri
        exhibitBuilder.paginatedItemsUri = <?php echo js_escape(url('exhibit-builder/items/browse')); ?>;

        exhibitBuilder.removeItemText = <?php echo js_escape(__('Remove This Item')); ?>;
        // Get the paginated items
        exhibitBuilder.getItems();

        jQuery(document).bind('omeka:loaditems', function() {
               // Hide the page search form
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
        });

        // Search Items Dialog Box
         jQuery('#search-items').dialog({
             autoOpen: false,
             width: Math.min(jQuery(window).width() - 100, 820),
             height: Math.min(jQuery(window).height() - 50, 500),
             title: <?php echo js_escape(__('Attach an Item')); ?>,
             modal: true,
             buttons: {
                <?php echo js_escape(__('Attach Selected Item')); ?>: function() {
                    exhibitBuilder.attachSelectedItem();
                     jQuery(this).dialog('close');
                 }
             },
             open: function() { jQuery('body').css('overflow', 'hidden'); },
             beforeClose: function() { jQuery('body').css('overflow', 'inherit'); }
         });
    });

    Omeka.wysiwyg();

    jQuery(window).load(function() {
        Omeka.ExhibitBuilder.addNumbers();
    });
    jQuery(document).bind('exhibitbuilder:attachitem', function (event) {
        // Add tinyMCE to all textareas in the div where the item was attached.
        jQuery(event.target).find('textarea').each(function () {
            tinyMCE.execCommand('mceAddControl', false, this.id);
        });
    });
//]]>
</script>
<?php echo foot(); ?>
