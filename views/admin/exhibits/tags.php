<?php
$title = __('Browse Exhibits by Tag');
echo head(array('title' => $title, 'bodyclass' => 'exhibits'));
?>

<div id="primary">
<?php if (!empty($tags)): ?>
    <?php
    echo tag_cloud($tags, 'exhibits/browse/');
    ?>
<?php else: ?>
    <h2><?php echo __('There are no tags to display. You must first tag some exhibits.'); ?></h2>
<?php endif; ?>
</div>
<?php echo foot(); ?>
