<?php
    if ($page->title) {
        $pageTitle = $actionName . ' Page: "' . $page->title . '"';
    } else {
        $pageTitle = $actionName . ' Page';
    }
?>
<?php head(array('title'=> html_escape($pageTitle), 'bodyclass'=>'exhibits')); ?>

<script type="text/javascript" charset="utf-8">
//<![CDATA[

    Event.observe(window, 'load', Omeka.ExhibitBuilder.wysiwyg);

	var paginate_uri = <?php echo Zend_Json::encode(uri('exhibits/items')); ?>;
	
			Event.observe(window, 'load', function() {
		        var exhibitBuilder = new Omeka.ExhibitBuilder();
						
				// Put the handles on the items that are being dragged.
				Event.observe(document, 'omeka:loaditems', function(){
				    exhibitBuilder.setUrlForHandleGif(<?php echo Zend_Json::encode(img('arrow_move.gif')); ?>);
				    exhibitBuilder.addHandles($$('#item-select .item-drag'));
				});
				
				exhibitBuilder.addStyling();
				
				// Retrieve the pagination through ajaxy goodness
				exhibitBuilder.getItems(paginate_uri);
				
		    	Event.observe(document, 'omeka:loaditems', function(){
		    	    // Put the 'delete' as background to anything with a 'remove_item' class
		            $$('.remove_item').invoke('setStyle', {backgroundImage: 'url(' + <?php echo Zend_Json::encode(img('delete.gif')); ?> + ')'});            
		    	});
		
			}); 
		    
		    Event.observe(document, 'omeka:loaditems', function(){
		        $('page-search-form').hide();
		        $('show-or-hide-search').observe('click', function(){
		            $('page-search-form').toggle();
		            this.toggleClassName('show-form');
		            this.update(this.hasClassName('show-form') ? 'Show Search Form' : 'Hide Search Form');
		        });
		    });
//]]>	    
</script>
<h1><?php echo html_escape($pageTitle); ?></h1>

<div id="primary">
<?php echo flash(); ?>

<div id="page-builder">
	
	<div id="exhibits-breadcrumb">
		<a href="<?php echo html_escape(uri('exhibits')); ?>">Exhibits</a> &gt; <a href="<?php echo html_escape(uri('exhibits/edit/' . $exhibit['id']));?>"><?php echo html_escape($exhibit['title']); ?></a>  &gt; <a href="<?php echo html_escape(uri('exhibits/edit-section/' . $section['id']));?>"><?php echo html_escape($section['title']); ?></a>  &gt; <?php echo html_escape($actionName . ' Page'); ?>
	</div>
    
    <?php //This item-select idv must be outside the <form> tag for this page, b/c IE7 can't handle nested form tags. ?>
	<div id="item-select"></div>
    
    <form id="page-form" method="post" action="<?php echo html_escape(uri(array('module'=>'exhibit-builder', 'controller'=>'exhibits', 'action'=>'edit-page-content', 'id'=>$page->id))); ?>">
        <div id="page-metadata-list">
        <h2>Page Metadata</h2>
            <p>Page Title: <?php echo html_escape($page->title); ?></p>
        <?php 
            $imgFile = web_path_to(EXHIBIT_LAYOUTS_DIR_NAME ."/$page->layout/layout.gif"); 
        	echo '<img src="'. html_escape($imgFile) .'" alt="' . html_escape($page->layout) . '"/>';
        ?>
    <button id="page_metadata_form" name="page_metadata_form" type="submit">Edit Page Metadata</button>
        </div>
    
	<div id="layout-all">
	<div id="layout-form">
	<?php exhibit_builder_render_layout_form($page->layout); ?>
	</div>

	</div>
	
		<div id="page-submits">
			<input id="section_form" name="section_form" type="submit" value="Save and Return to Section" /> or 
			<input id="page_form" name="page_form" type="submit" value="Save and Add Another Page" /> or <a href="<?php echo html_escape(uri(array('module'=>'exhibit-builder', 'controller'=>'exhibits', 'action'=>'edit-section', 'id'=>$page->section_id))); ?>">Cancel</a>
		</div>
	
	<fieldset>
	<?php echo __v()->formHidden('slug', $page->slug); // Put this here to fool the form into not overriding the slug. ?>	
	</fieldset>
	</form>
</div>
</div>
<?php foot(); ?>