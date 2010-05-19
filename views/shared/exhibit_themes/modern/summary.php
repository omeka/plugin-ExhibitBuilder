<?php exhibit_builder_exhibit_head(array('bodyclass' => 'exhibits')); ?>

		<h1><?php echo link_to_exhibit(); ?></h1>

		<p><?php echo exhibit('description'); ?></p>

		<div id="exhibit-sections">	
        	<?php set_exhibit_sections_for_loop_by_exhibit(get_current_exhibit()); ?>
        	<?php while(loop_exhibit_sections()): ?>
            <h3><a href="<?php echo exhibit_builder_exhibit_uri(get_current_exhibit(), get_current_exhibit_section()); ?>"><?php echo html_escape(exhibit_section('title')); ?></a></h3>
			<?php echo exhibit_section('description'); ?>
			<?php endwhile; ?>
		</div>

		<div id="exhibit-credits">	
			<h3>Credits</h3>
			<p><?php echo html_escape(exhibit('credits')); ?></p>
		</div>

<?php exhibit_builder_exhibit_foot(); ?>