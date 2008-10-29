<?php 
	//Name: Gallery Full Right;
	//Description: An image gallery, with a wider right column;
	//Author: Jeremy Boggs; 
?>

<div class="gallery-full-right">
	<div class="primary">
	<?php if (use_exhibit_page_item(1)): ?>
	<div class="item-full">
		<?php echo exhibit_display_item(array('imageSize'=>'fullsize'), array('class'=>'permalink')); ?>
		<?php echo item('Dublin Core', 'Title'); ?>
		<?php echo item('Dublin Core', 'Description'); ?>
	</div>
   	<?php endif; ?>

	</div><!--end primary-->
	
	<div class="secondary gallery">
		
		<?php echo display_exhibit_thumbnail_gallery(2, 5, array('class'=>'permalink')); ?>
	</div><!--end secondary-->

	<div id="item-full-text"><?php echo page_text(1); ?></div>

</div><!--end gallery-->