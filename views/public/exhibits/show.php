<?php head(array('title' => html_escape($exhibit->title))); ?>
<div id="primary">
	<h2><?php echo html_escape($exhibit->title); ?></h2>

<div id="nav-container">
	<?php echo exhibit_builder_section_nav();?>
	<?php echo exhibit_builder_page_nav();?>
</div>

	<?php echo flash(); ?>
	<?php echo exhibit_builder_link_to_previous_exhibit_page(); ?>
	<?php echo exhibit_builder_link_to_next_exhibit_page(); ?>
	
	<?php exhibit_builder_render_exhibit_page(); ?>
</div>	
<?php foot(); ?>