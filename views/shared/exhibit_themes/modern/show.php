<?php exhibit_head(array('bodyclass' => 'exhibits')); ?>

<h2><?php echo htmlentities($section->title); ?></h2>	
		
<div id="primary">
	
	<div class="exhibit-content">
	<?php render_exhibit_page(); ?>
	</div>

    <?php echo link_to_previous_exhibit_page(); ?>
    <?php echo link_to_next_exhibit_page(); ?>
	
</div>
	
<?php exhibit_foot(); ?>