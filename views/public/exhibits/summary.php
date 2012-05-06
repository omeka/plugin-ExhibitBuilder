<?php head(array('title' => html_escape('Summary of ' . exhibit('title')),'bodyid'=>'exhibit','bodyclass'=>'summary')); ?>

<div id="primary">

<h1><?php echo html_escape(exhibit('title')); ?></h1>
<?php echo exhibit_builder_page_nav(); ?>

<h2><?php echo __('Description'); ?></h2>
<?php echo exhibit('description'); ?>

<h2><?php echo __('Credits'); ?></h2>
<p><?php echo html_escape(exhibit('credits')); ?></p>

<div id="exhibit-pages">
    <?php set_exhibit_pages_for_loop_by_exhibit(); ?>
    <?php while(loop_exhibit_pages()): ?>
    <?php exhibit_builder_render_page_summary(); ?>
    <?php endwhile; ?>
</div>
</div>
<?php foot(); ?>
