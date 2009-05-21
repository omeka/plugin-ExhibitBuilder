<?php exhibit_head(array('bodyclass' => 'exhibits')); ?>

<div id="primary">
    <div class="exhibit-description">
	<?php echo $exhibit->description; ?>
</div>

<div id="exhibit-sections">	
	<?php foreach($exhibit->Sections as $section): ?>
	<h3><a href="<?php echo exhibit_uri($exhibit, $section); ?>"><?php echo htmlentities($section->title); ?></a></h3>
	<?php echo $section->description; ?>
	<?php endforeach; ?>
</div>

<div id="exhibit-credits">	
	<h3>Credits</h3>
	<p><?php echo htmlentities($exhibit->credits); ?></p>
</div>
</div>

<?php exhibit_foot(); ?>