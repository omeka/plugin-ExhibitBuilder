<?php
if ($exhibit->title) {
    $exhibitTitle = 'Configure Theme for Exhibit: "' . $exhibit->title . '"';
} else {
    $exhibitTitle = 'Configure Theme for Exhibit';
}
?>
<?php echo head(array('title'=> html_escape($exhibitTitle), 'bodyclass'=>'exhibits')); ?>
<?php echo js_tag('themes'); ?>
<?php echo flash(); ?>
<div id="exhibits-breadcrumb">
    <a href="<?php echo html_escape(url('exhibits')); ?>">Exhibits</a> &gt; <?php echo html_escape('Configure Theme for Exhibit'); ?>
</div>
<form method="post" action="" enctype="multipart/form-data">
    <section class="seven columns alpha">
        <h2><?php echo __('Configure the &#8220;%s&#8221; Theme', html_escape($theme->title)); ?></h2>
        <p><?php echo __('Configurations apply to this theme only.'); ?></p>
        <?php echo $form; ?>
    </section>
    <section class="three columns omega">
        <div id="save" class="panel">
            <?php echo $this->formSubmit('submit', __('Save Changes'), array('class'=>'submit big green button')); ?>
        </div>
    </section>
</form>
<?php echo foot(); ?>
