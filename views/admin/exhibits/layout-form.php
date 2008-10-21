<?php head(array('title'=>'Exhibit Page', 'body_class'=>'exhibits')); ?>
<?php echo js('exhibits'); ?>

<h1><?php echo htmlentities($actionName); ?> Page</h1>

<div id="primary">

<script type="text/javascript" charset="utf-8">
//<![CDATA[

	Event.observe(window, 'load', makeLayoutSelectable);
	
	function makeLayoutSelectable() {
		$('layout-submits').hide();
		var current_layout = $('current_layout');
		var layouts = $$('div.layout');

		//Make each layout clickable
		layouts.each( function(layout) {
			layout.onclick = function() {
				//Make a copy of the image
				layouts.each(function(layout) {
					layout.style.border = "1px solid #ccc";
					layout.style.backgroundColor = "#fff";
				})
				this.style.border = "1px solid #6BA8DA";
				this.style.backgroundColor = "#A2C9E8"
				var img = this.getElementsByTagName('img')[0];
				var copy = img.cloneNode(true);
				var input = this.getElementsByTagName('input')[0];
				var title = input.readAttribute('value');
				var titletext = document.createTextNode(title);
				var heading = document.createElement('h2');
				heading.appendChild(titletext);
				
				//Overwrite the contents of the div that displays the current layout
				current_layout.update();
				current_layout.appendChild(copy);
				current_layout.appendChild(heading);
				$('layout-submits').show();
				//new Effect.Highlight(current_layout);

				//Make sure the input is selected
				var input = this.getElementsByTagName('input')[0];
				input.click();
			}
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

        <div class="field"><?php echo text(array('name'=>'title', 'id'=>'title', 'class'=>'textinput'), $page->title, 'Title for the Page'); ?></div>
        <div class="field"><?php echo text(array('name'=>'slug','id'=>'slug','class'=>'textinput'), $page->slug, 'URL Slug (optional)'); ?></div>
    </fieldset>		
		
	<fieldset id="layouts">
		<legend>Layouts</legend>
		
		<div id="chosen_layout">
		<div id="current_layout"><p>Choose a layout by selecting a thumbnail on the right.</p></div>
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
	
	<p id="layout-submits">
	<button type="submit" name="choose_layout" id="choose_layout" class="page-button">Choose This Layout</button> or <button type="submit" name="page_form" id="page_form" class="page-button">New Page</button>
	or <button type="submit" name="cancel_and_section_form" id="section_form" class="cancel">Cancel</button></p>
	
	
</form>
</div>
<?php foot(); ?>