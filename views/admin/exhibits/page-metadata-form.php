<?php head(array('title'=>'Exhibit Page', 'body_class'=>'exhibits')); ?>
<?php echo js('exhibits'); ?>

<h1><?php echo htmlentities($actionName); ?> Page</h1>

<div id="primary">

<script type="text/javascript" charset="utf-8">
//<![CDATA[

	Event.observe(window, 'load', makeLayoutSelectable);
	
	function makeLayoutSelectable() {
		var layouts = $$('div.layout');
        
		//Make each layout clickable
		layouts.invoke('observe', 'click', function(e) {
            var currentLayout = $('layout-thumbs').select('div.current-layout').first();

            if (currentLayout) {
                currentLayout.removeClassName('current-layout');
            }

            this.addClassName('current-layout');
            var copy = $(this.cloneNode(true));
            
            // Take the form input out of the copy (so no messed up forms).
            copy.select('input').first().remove();
                    
            $('chosen_layout').update().appendChild(copy);
            this.select('input').first().click();      
		});		
	}	

//]]>	
</script>

<form method="post" id="choose-layout">
		
		<?php 
		//	submit('Exhibit', 'exhibit_form');
		//	submit('New Page', 'page_form'); 
		?>
	
    	<div id="exhibits-breadcrumb">
    		<a href="<?php echo uri('exhibits'); ?>">Exhibits</a> &gt; <a href="<?php echo uri('exhibits/edit/' . $exhibit['id']);?>"><?php echo $exhibit['title']; ?></a>  &gt; <a href="<?php echo uri('exhibits/edit-section/' . $section['id']);?>"><?php echo $section['title']; ?></a>  &gt; <?php echo $actionName . ' Page'; ?>
    	</div>	

    <fieldset>
        <legend>Page Metadata</legend>
        <?php echo flash(); ?>
        <div class="field"><?php echo text(array('name'=>'title', 'id'=>'title', 'class'=>'textinput'), $page->title, 'Title for the Page'); ?></div>
        <div class="field"><?php echo text(array('name'=>'slug','id'=>'slug','class'=>'textinput'), $page->slug, 'URL Slug (optional)'); ?></div>
    </fieldset>		
		
	<fieldset id="layouts">
		<legend>Layouts</legend>
		
		<div id="chosen_layout">
		<?php
		if ($page->layout) {
	        echo exhibit_layout($page->layout, false);
		} else {
		    echo "<p>Choose a layout by selecting a thumbnail on the right.</p>";
		}
		?>
	    </div>
		
		<div id="layout-thumbs">
		<?php 
			$layouts = get_ex_layouts();
	
			foreach ($layouts as $layout) {
				exhibit_layout($layout);
			} 
		?>
		</div>
	</fieldset> 
	
	<p><input type="submit" name="save_page_metadata" id="page_metadata_form" value="Save Changes"/> or 
	    <a href="<?php echo uri(array('module'=>'exhibit-builder', 'controller'=>'exhibits', 'action'=>'edit-section', 'id'=>$page->section_id)); ?>">Cancel</a></p>
	
	
</form>
</div>
<?php foot(); ?>