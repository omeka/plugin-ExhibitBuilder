<?php echo head(array('title' => metadata('exhibit', 'title'), 'bodyid'=>'exhibit', 'bodyclass'=>'summary')); ?>

<h1><?php echo metadata('exhibit', 'title'); ?></h1>
<?php echo exhibit_builder_page_nav(); ?>

<h2><?php echo __('Description'); ?></h2>
<?php echo metadata('exhibit', 'description', array('no_escape' => true)); ?>

<h2><?php echo __('Credits'); ?></h2>
<p><?php echo metadata('exhibit', 'credits'); ?></p>

<ul id="exhibit-pages">
    <?php set_exhibit_pages_for_loop_by_exhibit(); ?>
    <?php foreach (loop('exhibit_page') as $exhibitPage): ?>
    <?php echo exhibit_builder_page_summary($exhibitPage); ?>
    <?php endforeach; ?>
</ul>
<?php echo foot(); ?>
