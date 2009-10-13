<?php 
	//Name: Image List Left;
	//Description: An image gallery, with a full-size image on the left;
	//Author: Jeremy Boggs; 
?>

<div class="image-list-left">
	<?php for ($i=1; $i <= 8; $i++): ?>
   		<?php if(exhibit_builder_use_exhibit_page_item($i)):?>
   		    <div class="exhibit-item">
				<?php echo exhibit_builder_exhibit_display_item(array('imageSize'=>'fullsize'), array('class'=>'permalink')); ?>
				<?php echo item('Dublin Core', 'Title'); ?>
				<?php if ($text = exhibit_builder_page_text($i)): ?>
   		        <div class="exhibit-text"><?php echo $text; ?></div>
				<?php endif; ?>
   		    </div>
   		<?php endif; ?>
	<?php endfor;?>
</div>