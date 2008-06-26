<?php head(array('title'=>'Exhibit Page', 'body_class'=>'exhibits')); ?>

<?php 
echo js('search'); 
echo js('exhibits');
?>

<script type="text/javascript" charset="utf-8">
	var paginate_uri = "<?php echo uri('exhibits/items'); ?>";
	
	Event.observe(window, 'load', function() {

		//Retrieve the pagination through ajaxy goodness
		getPagination(paginate_uri);
	});

	Event.observe(document, 'omeka:loaditems', onLoadPagination);
	
	//When you're done, make all the items drag/droppable
	Event.observe(document, 'omeka:loaditems', dragDropForm);
	
	function onLoadPagination() 
	{
		//When the pagination loads, it is not the same kind of event as window.onload, 
		//so we need to fire all those events manually
		Omeka.Search.toggleSearch();
		roundCorners();
		Omeka.Search.activateSearchButtons();
		
		new Effect.Highlight('item-list');
		
		//Make each of the pagination links fire an additional ajax request
		var links = $$('#pagination a');
				
		links.invoke('observe', 'click', function(e){
		    e.stop();
		    getPagination(this.href);
		});
		
		//Make the correct elements on the pagination draggable
		makeDraggable($$('#item-select div.item-drop'));
		
		//Disable the items in the pagination that are already used
		disablePaginationDraggables();
		
		//Hide all the numbers that tell us the Item ID
		$$('.item_id').invoke('hide');
		
		//Make the search form respond with ajax power
		$('search').onsubmit = function() {
			getPagination(paginate_uri, $('search').serialize());
			return false;
		}
	}
	

	
	function getPagination(uri, parameters)
	{
		new Ajax.Updater('item-select', uri, {
			parameters: parameters,
			evalScripts: true,
			method: 'get',
			onComplete: function() {
			    document.fire("omeka:loaditems");
			}
		});
		
	}
	
</script>
<?php echo js('exhibits'); ?>
<?php common('exhibits-nav'); ?>
<div id="primary">
<?php echo flash(); ?>

<div id="page-builder">
	<h1>Add Exhibit</h1>

	<div id="item-select"></div>

<form name="layout" id="page-form" method="post">

	
	<div id="layout-submits">

	
	</div>
	<div id="layout-all">
	<div id="layout-form">
	<?php render_layout_form($page->layout); ?>
	</div>
	<?php
		//submit('Change the layout for this page', 'change_layout');	
	?>
	</div>


		<?php
		//	submit('Save Changes &amp; Continue Paginating Through Items', 'save_and_paginate'); 
		//	submit('Save &amp; Return to Exhibit', 'exhibit_form');
		//	submit('Save &amp; Return to Section', 'section_form');
		//	
		//	submit('Save &amp; Add Another Page', 'page_form');
			//submit('Change the layout for this page', 'change_layout');	
		?>
		<p id="page-submits">
			<button id="change_layout" name="change_layout" type="submit">Change the Current Layout</button> or 
			<button id="section_form" name="section_form" type="submit">Save and Return to Section</button> or <button id="page_form" name="page_form" type="submit">Save and Add Another Page</button> or <button name="cancel_and_section_form" class="cancel">Cancel</button></p>
		
	</form>
</div>
</div>
<?php foot(); ?>