<?php echo head(array('title' => metadata('exhibit', 'title'), 'bodyclass'=>'exhibits summary')); ?>

<h1><?php echo metadata('exhibit', 'title'); ?></h1>
<?php echo exhibit_builder_page_nav(); ?>

<div id="primary">
<?php if ($exhibitDescription = metadata('exhibit', 'description', array('no_escape' => true))): ?>
<div class="exhibit-description">
    <?php echo $exhibitDescription; ?>
</div>
<?php endif; ?>

<?php if (($exhibitCredits = metadata('exhibit', 'credits'))): ?>
<div class="exhibit-credits">
    <h3><?php echo __('Credits'); ?></h3>
    <p><?php echo $exhibitCredits; ?></p>
</div>
<?php endif; ?>
</div>

<?php set_exhibit_pages_for_loop_by_exhibit(); ?>
<?php if (has_loop_records('exhibit_page')): ?>
<nav id="exhibit-pages">
    <ul>
        <?php foreach (loop('exhibit_page') as $exhibitPage): ?>
        <?php echo exhibit_builder_page_summary($exhibitPage); ?>
        <?php endforeach; ?>
    </ul>
</nav>
<?php endif; ?>

<?php echo foot(); ?>
