<?php 
	//Name: Gallery Full Right;
	//Description: An image gallery, with a wider right column;
	//Author: Jeremy Boggs; 
?>

<div class="gallery-full-right">
	<div class="primary">
		<?php if (exhibit_builder_use_exhibit_page_item(1)): ?>
		<div class="exhibit-item">
			<?php echo exhibit_builder_exhibit_display_item(array('imageSize'=>'fullsize'), array('class'=>'permalink')); ?>
			<?php echo item('Dublin Core', 'Title'); ?>
			<?php echo item('Dublin Core', 'Description'); ?>
			<?php echo exhibit_builder_link_to_exhibit_item('Item Link'); ?>
		</div>
		<?php endif; ?>
	</div>
	
	<div class="secondary gallery">
        <?php echo exhibit_builder_display_exhibit_thumbnail_gallery(2, 9, array('class'=>'permalink')); ?>
	</div>
	
	<?php if($text = exhibit_builder_page_text(1)):?>
	<div class="tertiary">
		<div class="exhibit-text"><?php echo exhibit_builder_page_text(1); ?></div>
	</div>
	<?php endif; ?>
</div>