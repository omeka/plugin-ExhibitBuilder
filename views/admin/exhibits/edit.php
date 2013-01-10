<?php
    queue_js_file(array('vendor/jquery.nestedSortable', 'navigation'));
    $title = __('Edit Exhibit "%s"', $exhibit->title);

    echo head(array('title' => html_escape($title), 'bodyclass' => 'exhibits'));
?>
    <div id="exhibits-breadcrumb">
        <a href="<?php echo html_escape(url('exhibits')); ?>"><?php echo __('Exhibits'); ?></a> &gt;
        <?php echo html_escape($title); ?>
    </div>

<?php echo flash(); ?>
<?php $theme = $exhibit->theme ? Theme::getTheme($exhibit->theme) : null; ?>
<?php echo common('exhibit-metadata-form', array('exhibit' => $exhibit, 'theme' => $theme), 'exhibits'); ?>

<?php echo foot(); ?>
