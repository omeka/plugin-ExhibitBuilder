<fieldset class="<?php echo $layout; ?>">
	<div class="primary">
	<?php 
	exhibit_builder_layout_form_text(1);
	?>
	</div>
	<div class="secondary">
	<?php
	for($i=1; $i <= 12; $i++):
    	exhibit_builder_layout_form_item($i);	    
	endfor;
	?>
	</div>
</fieldset>
