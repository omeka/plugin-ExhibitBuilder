<?php head(array('title'=>'Summary of ' . h($exhibit->title))); ?>
<div id="primary">
<h2><?php echo h($exhibit->title); ?></h2>
<?php echo section_nav(); ?>

<h3 class="clear">Description</h3>
<?php echo nls2p($exhibit->description); ?>

<h3>Credits</h3>
<p><?php echo h($exhibit->credits); ?></p>
</div>
<?php foot(); ?>