<?php 
	//Name: Text Image Right;
	//Description: A full page of text, with a full-size image floated right;
	//Author: Jeremy Boggs; 
?>

<div class="text-image-right">
	<div class="primary">
		<?php if(exhibit_builder_use_exhibit_page_item(1)):?>
		<div class="exhibit-item">
			<?php echo exhibit_builder_exhibit_display_item(array('imageSize'=>'fullsize'), array('class'=>'permalink')); ?>
			<div class="exhibit-item-caption">
			<?php echo item('Dublin Core', 'Title'); ?>
			<?php echo item('Dublin Core', 'Description'); ?>
			<?php echo exhibit_builder_link_to_exhibit_item('Item Link'); ?>
			</div>
		</div>
		<?php endif; ?>
	</div>
	<div class="secondary">		
		<div class="exhibit-text">
			<?php echo exhibit_builder_page_text(1); ?>
		</div>
	</div>
</div>