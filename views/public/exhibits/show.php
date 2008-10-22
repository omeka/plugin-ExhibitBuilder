<?php head(array('title'=>h($exhibit->title))); ?>
<div id="primary">
	<h2><?php echo h($exhibit->title); ?></h2>

<div id="nav-container">
	<?php echo section_nav();?>
	<?php echo page_nav();?>
</div>

	<?php echo flash(); ?>
	<?php echo link_to_previous_exhibit_page(); ?>
	<?php echo link_to_next_exhibit_page(); ?>
	
	<?php render_exhibit_page(); ?>
</div>	
<?php foot(); ?>