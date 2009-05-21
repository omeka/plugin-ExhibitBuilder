<?php head(array('title' => 'Summary of ' . html_escape($exhibit->title))); ?>
<div id="primary">
<h2><?php echo html_escape($exhibit->title); ?></h2>
<?php echo section_nav(); ?>

<h3 class="clear">Description</h3>
<?php echo $exhibit->description; ?>

<h3>Credits</h3>
<p><?php echo html_escape($exhibit->credits); ?></p>
</div>
<?php foot(); ?>