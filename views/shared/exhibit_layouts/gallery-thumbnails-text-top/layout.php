<?php 
	//Name: Gallery Thumbnails Text Top;
	//Description: Displays a block of text above a gallery of up to 12 items;
	//Author: Jeremy Boggs; 
?>

<div class="gallery-thumbnails-text-top">
	<div class="primary">
		<div class="exhibit-text">
		<?php echo page_text(1); ?>
		</div>
	</div>
	<div class="secondary">
	    <?php echo display_exhibit_thumbnail_gallery(1, 12, array('class'=>'permalink')); ?>
	</div>


</div>