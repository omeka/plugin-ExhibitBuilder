<?php exhibit_builder_exhibit_head(array('bodyclass' => 'exhibits')); ?>
<div id="primary">
	<h2><?php echo html_escape($page->title); ?></h2>

	<div class="exhibit-content">
		<?php exhibit_builder_render_exhibit_page(); ?>
	</div>
	
	<div id="previous-next-nav">
		<div class="previous"><?php echo exhibit_builder_link_to_previous_exhibit_page(); ?></div>
		<div class="next"><?php echo exhibit_builder_link_to_next_exhibit_page(); ?></div>
	</div>

</div><!--end primary-->
	
<?php exhibit_builder_exhibit_foot(); ?>