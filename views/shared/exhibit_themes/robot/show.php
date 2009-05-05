<?php exhibit_head(array('bodyclass' => 'show')); ?>
<div id="primary">
	<h2><?php echo $page->title; ?></h2>

	<div class="exhibit-content">
		<?php render_exhibit_page(); ?>
	</div>
	
	<div id="previous-next-nav">
		<div class="previous"><?php echo link_to_previous_exhibit_page(); ?></div>
		<div class="next"><?php echo link_to_next_exhibit_page(); ?></div>
	</div>

</div><!--end primary-->
	
<?php exhibit_foot(); ?>