<?php exhibit_builder_exhibit_head(array('bodyclass' => 'exhibits')); ?>

<h2><?php echo html_escape($section->title); ?></h2>	
		
<div id="primary">
	
	<div class="exhibit-content">
	<?php exhibit_builder_render_exhibit_page(); ?>
	</div>

    <?php echo exhibit_builder_link_to_previous_exhibit_page(); ?>
    <?php echo exhibit_builder_link_to_next_exhibit_page(); ?>
	
</div>
	
<?php exhibit_builder_exhibit_foot(); ?>