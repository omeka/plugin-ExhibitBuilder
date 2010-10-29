<?php
if ($exhibit->title) {
    $exhibitTitle = 'Configure Theme for Exhibit: "' . $exhibit->title . '"';
} else {
    $exhibitTitle = 'Configure Theme for Exhibit';
}
?>
<?php head(array('title'=> html_escape($exhibitTitle), 'bodyclass'=>'exhibits')); ?>
<?php echo js('tiny_mce/tiny_mce'); ?>
<?php echo js('themes'); ?>

<h1><?php echo html_escape($exhibitTitle); ?></h1>

<div id="primary">
    <div id="exhibits-breadcrumb">
        <a href="<?php echo html_escape(uri('exhibits')); ?>">Exhibits</a> &gt; <?php echo html_escape('Configure Theme for Exhibit'); ?>
    </div>
    <h2><?php echo $theme->title; ?> Configuration</h2>    
<?php flash(); ?>
<?php echo $form; ?>
</div>
<?php foot(); ?>