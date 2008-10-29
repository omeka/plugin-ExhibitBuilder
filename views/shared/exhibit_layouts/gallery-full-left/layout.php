<?php 
	//Name: Gallery Full Left;
	//Description: An image gallery, with a wider left column;
	//Author: Jeremy Boggs; 
?>

<div class="gallery-full-left">
	<div class="primary">
		<?php if(use_exhibit_page_item(1)):?>
		<div class="item-full">
			<?php echo exhibit_display_item(array('imageSize'=>'fullsize'), array('class'=>'permalink')); ?>
			<?php echo item('Dublin Core', 'Title'); ?>
			<?php echo item('Dublin Core', 'Description'); ?>
			<?php echo link_to_exhibit_item('Item Link'); ?>
		</div>
		<?php endif; ?>
	</div>
	
	<div class="secondary gallery">
        <?php echo display_exhibit_thumbnail_gallery(2, 5, array('class'=>'permalink')); ?>
	</div>
	
	<?php if($text = page_text(1)):?>
	<div id="item-full-text">
		<?php echo $text; ?>
	</div>
	<?php endif; ?>
</div>