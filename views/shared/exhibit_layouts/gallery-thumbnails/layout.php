<?php 
	//Name: Gallery Thumbnails;
	//Description: Displays a gallery of up to 12 items;
	//Author: Jeremy Boggs; 
?>

<div class="gallery-thumbnails">
	<div class="primary">
	<?php echo exhibit_builder_display_exhibit_thumbnail_gallery(1, 12, array('class'=>'permalink')); ?>
	</div>
</div>