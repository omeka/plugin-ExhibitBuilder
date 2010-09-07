<div class="gallery-full-right">
	<div class="primary">
		<?php if (exhibit_builder_use_exhibit_page_item(1)): ?>
		<div class="exhibit-item">
			<?php echo exhibit_builder_exhibit_display_item(array('imageSize'=>'fullsize'), array('class'=>'permalink')); ?>
			<?php echo exhibit_builder_exhibit_display_caption(1); ?>
		</div>
		<?php endif; ?>
		<?php if($text = exhibit_builder_page_text(1)):?>
    	<div class="exhibit-text">
    	    <?php echo $text; ?>
    	</div>
    	<?php endif; ?>
	</div>
	
	<div class="secondary gallery">
        <?php echo exhibit_builder_display_exhibit_thumbnail_gallery(2, 9, array('class'=>'permalink')); ?>
	</div>
</div>