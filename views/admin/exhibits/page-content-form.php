<?php
$exhibitPageTitle = __('Edit Page Content: "%s"', $exhibitPage->title);
?>
<?php head(array('title'=> html_escape($exhibitPageTitle), 'bodyclass'=>'exhibits')); ?>

<script type="text/javascript" charset="utf-8">
//<![CDATA[

    jQuery(document).ready(function(){

        var exhibitBuilder = new Omeka.ExhibitBuilder();        
		
		// Add styling
		exhibitBuilder.addStyling();
		
		// Set the exhibit item uri
		exhibitBuilder.itemContainerUri = <?php echo js_escape(uri('exhibits/item-container')); ?>;
		
		// Set the paginated exhibit items uri
		exhibitBuilder.paginatedItemsUri = <?php echo js_escape(uri('exhibits/items')); ?>;
		
		// Set the remove item background image uri
		exhibitBuilder.removeItemBackgroundImageUri = <?php echo js_escape(img('silk-icons/delete.png')); ?>;

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
     		width: 820,
     		height: 500,
            title: <?php echo js_escape(__('Attach an Item')); ?>,
     		modal: true,
     		buttons: {
                <?php echo js_escape(__('Attach Selected Item')); ?>: function() { 
                    exhibitBuilder.attachSelectedItem();
     				jQuery(this).dialog('close'); 
     			} 
     		}
     	});
	});
	
    jQuery(window).load(function() {
        Omeka.ExhibitBuilder.wysiwyg();
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
<h1><?php echo html_escape($exhibitPageTitle); ?></h1>

<div id="primary">
<?php echo flash(); ?>

<div id="page-builder">
	<div id="exhibits-breadcrumb">
		<a href="<?php echo html_escape(uri('exhibits')); ?>"><?php echo __('Exhibits'); ?></a> &gt;
        <a href="<?php echo html_escape(uri('exhibits/edit/' . $exhibit['id']));?>"><?php echo html_escape($exhibit['title']); ?></a>  &gt;
        <a href="<?php echo html_escape(uri('exhibits/edit-section/' . $exhibitSection['id']));?>"><?php echo html_escape($exhibitSection['title']); ?></a>  &gt;
        <?php echo html_escape($exhibitPageTitle); ?>
	</div>
    
    <?php //This item-select div must be outside the <form> tag for this page, b/c IE7 can't handle nested form tags. ?>
	<div id="search-items" style="display:none;">
		<div id="item-select"></div>
    </div>
    
    <form id="page-form" method="post" action="<?php echo html_escape(uri(array('module'=>'exhibit-builder', 'controller'=>'exhibits', 'action'=>'edit-page-content', 'id'=>$exhibitPage->id))); ?>">
        <div id="page-metadata-list">
        <h2><?php echo __('Page Layout'); ?></h2>
            <div id="layout-metadata">
                <?php 
                    $imgFile = web_path_to(EXHIBIT_LAYOUTS_DIR_NAME ."/$exhibitPage->layout/layout.gif"); 
                	echo '<img src="'. html_escape($imgFile) .'" alt="' . html_escape($exhibitPage->layout) . '"/>';
                ?>
                <ul>
                 <li><strong><?php echo __($layoutName); ?></strong></li>
                 <li><?php echo __($layoutDescription); ?></li>
                 </ul>
            </div>

    <button id="page_metadata_form" name="page_metadata_form" type="submit"><?php echo __('Edit Page'); ?></button>
        </div>
    
	<div id="layout-all">
	    <h2><?php echo __('Page Content'); ?></h2>
	<div id="layout-form">
	<?php exhibit_builder_render_layout_form($exhibitPage->layout); ?>
	</div>

	</div>
    <fieldset>
		<p id="exhibit-builder-save-changes">
            <input id="section_form" name="section_form" type="submit" value="<?php echo __('Save and Return to Section'); ?>" /> <?php echo __('or'); ?> 
            <input id="page_form" name="page_form" type="submit" value="<?php echo __('Save and Add Another Page'); ?>" /> <?php echo __('or'); ?>
            <a href="<?php echo html_escape(uri(array('module'=>'exhibit-builder', 'controller'=>'exhibits', 'action'=>'edit-section', 'id'=>$exhibitPage->section_id))); ?>"><?php echo __('Cancel'); ?></a>
		</p>
	</fieldset>
	<fieldset>
	<?php echo __v()->formHidden('slug', $exhibitPage->slug); // Put this here to fool the form into not overriding the slug. ?>	
	</fieldset>
	</form>
</div>
</div>
<?php foot(); ?>
