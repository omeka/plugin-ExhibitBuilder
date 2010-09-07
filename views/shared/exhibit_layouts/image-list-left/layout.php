<div class="image-list-left">
	<?php 
	for ($i=1; $i <= 8; $i++): 
	if(($text = exhibit_builder_page_text($i)) || exhibit_builder_use_exhibit_page_item($i)): ?>

	<div class="image-text-group">

	    <?php if($text): ?>
        <div class="exhibit-text">
            <?php echo $text; ?>
        </div>
		<?php endif; ?>
		<?php if(exhibit_builder_use_exhibit_page_item($i)):?>
	    <div class="exhibit-item">
			<?php echo exhibit_builder_exhibit_display_item(array('imageSize'=>'fullsize'), array('class'=>'permalink')); ?>
			<?php echo exhibit_builder_exhibit_display_caption($i); ?>
	    </div>
		<?php endif; ?>
	</div>
	<?php endif; endfor;?>
</div>