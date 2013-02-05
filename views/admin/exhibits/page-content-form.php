<?php
$title = __('Edit Page Content: "%s"', metadata('exhibit_page', 'title', array('no_escape' => true)));
?>
<?php echo head(array('title'=> html_escape($title), 'bodyclass'=>'exhibits')); ?>

<?php echo flash(); ?>

<div id="exhibits-breadcrumb">
    <a href="<?php echo html_escape(url('exhibits')); ?>"><?php echo __('Exhibits'); ?></a> &gt;
    <a href="<?php echo html_escape(url('exhibits/edit/' . $exhibit['id']));?>"><?php echo html_escape($exhibit['title']); ?></a>  &gt;
    <?php echo html_escape($title); ?>
</div>
<form id="page-form" method="post" action="<?php echo html_escape(url(array('module'=>'exhibit-builder', 'controller'=>'exhibits', 'action'=>'edit-page-content', 'id' => metadata('exhibit_page', 'id')))); ?>">
    <div class="seven columns alpha">
        <div id="page-metadata-list">
            <h2><?php echo __('Page Layout'); ?></h2>
            <div id="layout-metadata">
            <?php
                $layout = metadata('exhibit_page', 'layout', array('no_escape' => true));
                $imgFile = web_path_to(EXHIBIT_LAYOUTS_DIR_NAME ."/$layout/layout.gif");
                echo '<img src="'. html_escape($imgFile) .'" alt="' . html_escape($layout) . '"/>';
            ?>
                <strong><?php echo __($layoutName); ?></strong>
                <p><?php echo __($layoutDescription); ?></p>
            </div>

            <button id="page_metadata_form" name="page_metadata_form" type="submit"><?php echo __('Edit Page'); ?></button>
        </div>
        <div id="layout-all">
            <h2><?php echo __('Page Content'); ?></h2>
            <div id="layout-form">
                <?php exhibit_builder_render_layout_form($layout); ?>
            </div>
        </div>
        <fieldset>
        <?php echo get_view()->formHidden('slug', $exhibit_page->slug); // Put this here to fool the form into not overriding the slug. ?>
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
