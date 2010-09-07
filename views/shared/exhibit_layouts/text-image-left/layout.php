<div class="text-image-left">
	<div class="primary">
		<?php if(exhibit_builder_use_exhibit_page_item(1)):?>
		<div class="exhibit-item">
			<?php echo exhibit_builder_exhibit_display_item(array('imageSize'=>'fullsize'), array('class'=>'permalink')); ?>
			<?php echo exhibit_builder_exhibit_display_caption(1); ?>
		</div>
		<?php endif; ?>	
	</div>
	<div class="secondary">
		<div class="exhibit-text">
			<?php echo exhibit_builder_page_text(1); ?>
		</div>
	</div>
</div>