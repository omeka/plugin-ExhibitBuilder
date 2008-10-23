<?php exhibit_head(); ?>

<h2><?php echo $section->title; ?></h2>	
		
<div id="primary">
	
	<div class="exhibit-content"><!--exhibit content-->
	<?php render_exhibit_page(); ?>
	</div>

    <?php echo link_to_previous_exhibit_page(); ?>
    <?php echo link_to_next_exhibit_page(); ?>
	
</div><!--end primary-->
	
<?php exhibit_foot(); ?>