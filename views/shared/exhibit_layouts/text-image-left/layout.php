<?php 
	//Name: Text Image Left;
	//Description: A full page of text, with a full-size image floated left;
	//Author: Jeremy Boggs; 
?>

<div class="text-image-left">
	<div class="primary">
		<?php if(use_exhibit_page_item(1)):?>
		<div class="exhibit-item">
			<?php echo exhibit_display_item(array('imageSize'=>'fullsize'), array('class'=>'permalink')); ?>
			<div class="exhibit-item-caption">
			<?php echo item('Dublin Core', 'Title'); ?>
			<?php echo item('Dublin Core', 'Description'); ?>
			<?php echo link_to_exhibit_item('Item Link'); ?>
			</div>
		</div>
		<?php endif; ?>	
	</div>
	<div class="secondary">
		<div class="exhibit-text">
			<?php echo page_text(1); ?>
		</div>
	</div>
</div>