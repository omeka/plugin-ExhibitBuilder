<fieldset class="<?php echo html_escape($layout); ?>">
	<?php for($i=1; $i<=8; $i++): ?>
	    <div class="section">
    	<?php 
    	    echo exhibit_builder_layout_form_text($i);
    	    echo exhibit_builder_layout_form_item($i);
    	?>
    	</div>
	<?php endfor; ?>
</fieldset>
