<?php exhibit_builder_exhibit_head(array('bodyclass' => 'exhibits')); ?>

		<h1><?php echo exhibit_builder_link_to_exhibit($exhibit); ?></h1>

		<p><?php echo $exhibit->description; ?></p>

		<div id="exhibit-sections">	
			<?php foreach($exhibit->Sections as $section): ?>
			<h3><a href="<?php echo exhibit_builder_exhibit_uri($exhibit, $section); ?>"><?php echo html_escape($section->title); ?></a></h3>
			<?php echo $section->description; ?>
			<?php endforeach; ?>
		</div>

		<div id="exhibit-credits">	
			<h3>Credits</h3>
			<p><?php echo html_escape($exhibit->credits); ?></p>
		</div>

<?php exhibit_builder_exhibit_foot(); ?>