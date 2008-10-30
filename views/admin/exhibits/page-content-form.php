<?php head(array('title'=> htmlentities($actionName) . ' Page', 'body_class'=>'exhibits')); ?>

<?php 
echo js('search'); 
echo js('exhibits');
echo js('tiny_mce/tiny_mce');
?>

<script type="text/javascript" charset="utf-8">
	var paginate_uri = "<?php echo uri('exhibits/items'); ?>";
	
	Event.observe(window, 'load', function() {
        var exhibitBuilder = new Omeka.ExhibitBuilder();
				
		// Put the handles on the items that are being dragged.
		Event.observe(document, 'omeka:loaditems', function(){
		    exhibitBuilder.setUrlForHandleGif("<?php echo img('arrow_move.gif'); ?>");
		    exhibitBuilder.addHandles($$('#item-select .item-drag'));
		});
		
		exhibitBuilder.addStyling();
		
		// Retrieve the pagination through ajaxy goodness
		exhibitBuilder.getItems(paginate_uri);

        // These have nothing to do with the exhibit builder per se, they are just
        // related to the search bar and styling of admin theme in general.
    	Event.observe(document, 'omeka:loaditems', Omeka.Search.toggleSearch);
    	Event.observe(document, 'omeka:loaditems', Omeka.Search.activateSearchButtons);

    	Event.observe(document, 'omeka:loaditems', function(){
    	    // Put the 'delete' as background to anything with a 'remove_item' class
            $$('.remove_item').invoke('setStyle', {backgroundImage: "url('<?php echo img('delete.gif'); ?>')"});            
    	});

	});
    
    //Enable the WYSIWYG editor
    Event.observe(window, 'load', function(){

        tinyMCE.init({
         mode: "textareas",
    	theme: "advanced",
    	theme_advanced_toolbar_location : "top",
    	theme_advanced_buttons1 : "bold,italic,underline,justifyleft,justifycenter,justifyright,bullist,numlist,link,formatselect",
 		theme_advanced_buttons2 : "",
 		theme_advanced_buttons3 : "",
 		theme_advanced_toolbar_align : "left"
        });
    });  

</script>
<?php echo js('exhibits'); ?>

<h1><?php echo htmlentities($actionName); ?> Page</h1>

<div id="primary">
<?php echo flash(); ?>

<div id="page-builder">
	
	<div id="exhibits-breadcrumb">
		<a href="<?php echo uri('exhibits'); ?>">Exhibits</a> &gt; <a href="<?php echo uri('exhibits/edit/' . $exhibit['id']);?>"><?php echo $exhibit['title']; ?></a>  &gt; <a href="<?php echo uri('exhibits/edit-section/' . $section['id']);?>"><?php echo $section['title']; ?></a>  &gt; <?php echo $actionName . ' Page'; ?>
	</div>

    <div id="page-metadata-list">
    <form name="layout" id="page-form" method="post">
        <h2>Page Metadata</h2>
            <p>Page Title: <?php echo $page->title; ?></p>
        <?php 
            $imgFile = web_path_to("exhibit_layouts/$page->layout/layout.gif"); 
        	echo '<img src="'.$imgFile.'" />';
        ?>
    <button id="page_metadata_form" name="page_metadata_form" type="submit">Edit Page Metadata</button>
    </div>

    <div id="item-select"></div>
    
	<div id="layout-all">
	<div id="layout-form">
	<?php render_layout_form($page->layout); ?>
	</div>

	</div>
	
		<div id="page-submits">
			<button id="section_form" name="section_form" type="submit">Save and Return to Section</button> or <button id="page_form" name="page_form" type="submit">Save and Add Another Page</button> or <button name="cancel_and_section_form" class="cancel">Cancel</button>
		</div>
		
	</form>
</div>
</div>
<?php foot(); ?>