<fieldset class="<?php echo html_escape($layout); ?>">

<div class="primary">
	<h3><?php echo __('Primary'); ?></h3>
	<?php 
	    echo exhibit_builder_layout_form_item(1);
	    echo exhibit_builder_layout_form_text(1); 
	?>
</div>
<div class="secondary gallery">
	<h3><?php echo __('Secondary'); ?></h3>
	<?php 
	    for($i=2;$i<=9;$i++):
	        echo exhibit_builder_layout_form_item($i);
	    endfor;
	?>
</div>	
</fieldset>
