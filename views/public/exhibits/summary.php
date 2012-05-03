<?php head(array('title' => html_escape('Summary of ' . exhibit('title')),'bodyid'=>'exhibit','bodyclass'=>'summary')); ?>

<div id="primary">

<h1><?php echo html_escape(exhibit('title')); ?></h1>
<?php echo exhibit_builder_page_nav(); ?>

<h2><?php echo __('Description'); ?></h2>
<?php echo exhibit('description'); ?>

<h2><?php echo __('Credits'); ?></h2>
<p><?php echo html_escape(exhibit('credits')); ?></p>

<div id="exhibit-sections">
    <?php set_exhibit_pages_for_loop_by_exhibit(); ?>
    <?php while(loop_exhibit_pages()): ?>
    <h3><a href="<?php echo exhibit_builder_exhibit_uri(get_current_exhibit(), get_current_exhibit_page()); ?>">
        <?php echo html_escape(exhibit_page('title')); ?></a></h3>
    <?php echo exhibit_page('description'); ?>
    <?php endwhile; ?>
</div>
</div>
<?php foot(); ?>
