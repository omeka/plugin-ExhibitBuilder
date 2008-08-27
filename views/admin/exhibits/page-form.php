<?php head(array('title'=> htmlentities($actionName) . ' Page', 'body_class'=>'exhibits')); ?>

<?php 
echo js('ibox/ibox');
echo js('search'); 
echo js('exhibits');
echo js('tiny_mce/tiny_mce');
?>

<script type="text/javascript" charset="utf-8">
    iBox.setPath('<?php echo WEB_ROOT; ?>/plugins/Exhibitbuilder/views/admin/javascripts/ibox/');
</script>

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
    	Event.observe(document, 'omeka:loaditems', roundCorners);
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
<?php common('exhibits-nav'); ?>
<div id="primary">
<?php echo flash(); ?>

<div id="page-builder">
	
	<div id="exhibits-breadcrumb">
		<a href="<?php echo uri('exhibits'); ?>">Exhibits</a> &gt; <a href="<?php echo uri('exhibits/edit/' . $exhibit['id']);?>"><?php echo $exhibit['title']; ?></a>  &gt; <a href="<?php echo uri('exhibits/edit-section/' . $section['id']);?>"><?php echo $section['title']; ?></a>  &gt; <?php echo $actionName . ' Page'; ?>
	</div>
	
	<h1><?php echo htmlentities($actionName); ?> Page</h1>

	<div id="item-select" style="display:none;"></div>

<form name="layout" id="page-form" method="post">

	
	<div id="layout-submits">

	
	</div>
	<div id="layout-all">
	<div id="layout-form">
	<?php render_layout_form($page->layout); ?>
	</div>

	</div>

		<p id="page-submits">
			<button id="change_layout" name="change_layout" type="submit">Change the Current Layout</button> or 
			<button id="section_form" name="section_form" type="submit">Save and Return to Section</button> or <button id="page_form" name="page_form" type="submit">Save and Add Another Page</button> or <button name="cancel_and_section_form" class="cancel">Cancel</button></p>
		
	</form>
</div>
</div>
<?php foot(); ?>