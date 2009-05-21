<?php 
//Name: Gallery Thumbnails Text Bottom;
//Description: Displays a block of text below a gallery of up to 12 items;
//Author: Jeremy Boggs;
?>

<div class="gallery-thumbnails-text-bottom">
	<div class="primary">
		<?php echo exhibit_builder_display_exhibit_thumbnail_gallery(1, 12, array('class'=>'permalink')); ?>
	</div>
	<div class="secondary">
		<div class="exhibit-text">
		<?php echo exhibit_builder_page_text(1); ?>
		</div>
	</div>
</div>