<?php
if ($exhibit->title) {
    $exhibitTitle = 'Configure Theme for Exhibit: "' . $exhibit->title . '"';
} else {
    $exhibitTitle = 'Configure Theme for Exhibit';
}
?>
<?php echo head(array('title'=> html_escape($exhibitTitle), 'bodyclass'=>'exhibits')); ?>
<?php echo js_tag('themes'); ?>

<div id="exhibits-breadcrumb">
    <a href="<?php echo html_escape(url('exhibits')); ?>">Exhibits</a> &gt; <?php echo html_escape('Configure Theme for Exhibit'); ?>
</div>
<h2><?php echo $theme->title; ?> Configuration</h2>
<?php flash(); ?>
<?php echo $form; ?>
<?php echo foot(); ?>
