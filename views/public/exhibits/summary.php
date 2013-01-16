<?php echo head(array('title' => metadata('exhibit', 'title'), 'bodyid'=>'exhibit', 'bodyclass'=>'summary')); ?>

<h1><?php echo metadata('exhibit', 'title'); ?></h1>
<?php echo exhibit_builder_page_nav(); ?>

<div class="exhibit-description">
    <?php echo metadata('exhibit', 'description', array('no_escape' => true)); ?>
</div>

<div class="exhibit-credits">
    <?php if (($credits = metadata('exhibit', 'credits'))): ?>
    <h2><?php echo __('Credits'); ?></h2>
    <p><?php echo $credits; ?></p>
    <?php endif; ?>
</div>

<ul id="exhibit-pages">
    <?php set_exhibit_pages_for_loop_by_exhibit(); ?>
    <?php foreach (loop('exhibit_page') as $exhibitPage): ?>
    <?php echo exhibit_builder_page_summary($exhibitPage); ?>
    <?php endforeach; ?>
</ul>
<?php echo foot(); ?>
