<?php 
	//Name: Image List Right Thumbs;
	//Description: An image gallery, with the thumbnail image on the right;
	//Author: Jeremy Boggs; 
?>

<div class="image-list-right-thumbs">
	<?php for ($i=1; $i <= 8; $i++): ?>
		<?php if(use_exhibit_page_item($i)):?>
		    <div class="exhibit-item">
				<?php echo exhibit_display_item(array('imageSize'=>'thumbnail'), array('class'=>'permalink')); ?>
				<?php echo item('Dublin Core', 'Title'); ?>
				<?php if($text = page_text($i)): ?>
		        <div class="exhibit-text"><?php echo $text; ?></div>
				<?php endif; ?>
		    </div>
		<?php endif; ?>
	<?php endfor;?>
</div>