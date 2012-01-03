<?php head(array('title' => html_escape('Summary of ' . exhibit('title')),'bodyid'=>'exhibit','bodyclass'=>'summary')); ?>
<div id="primary">
<h1><?php echo html_escape(exhibit('title')); ?></h1>
<?php echo exhibit_builder_section_nav(); ?>

<h2><?php echo __('Description'); ?></h2>
<?php echo exhibit('description'); ?>

<h2><?php echo __('Credits'); ?></h2>
<p><?php echo html_escape(exhibit('credits')); ?></p>

<div id="exhibit-sections">	
	<?php set_exhibit_sections_for_loop_by_exhibit(get_current_exhibit()); ?>
	<h2><?php echo __('Sections'); ?></h2>
	<?php while(loop_exhibit_sections()): ?>
	<?php if (exhibit_builder_section_has_pages()): ?>
    <h3><a href="<?php echo exhibit_builder_exhibit_uri(get_current_exhibit(), get_current_exhibit_section()); ?>"><?php echo html_escape(exhibit_section('title')); ?></a></h3>
	<?php echo exhibit_section('description'); ?>
	<?php endif; ?>
	<?php endwhile; ?>
</div>
</div>
<?php foot(); ?>
