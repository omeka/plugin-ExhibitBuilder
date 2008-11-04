<?php exhibit_head(); ?>

<div class="exhibit-description">
	<?php echo nls2p(htmlentities($exhibit->description)); ?>
</div>

<div id="exhibit-sections">	
	<?php foreach($exhibit->Sections as $section): ?>
	<h3><a href="<?php echo exhibit_uri($exhibit, $section); ?>"><?php echo htmlentities($section->title); ?></a></h3>
	<?php echo nls2p(htmlentities($section->description)); ?>
	<?php endforeach; ?>
</div>

<div id="exhibit-credits">	
	<h3>Credits</h3>
	<p><?php echo htmlentities($exhibit->credits); ?></p>
</div>

<?php exhibit_foot(); ?>