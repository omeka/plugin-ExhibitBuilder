<?php exhibit_head(); ?>
<h2><?php echo $section->title; ?></h2>			
			
<div id="primary">
    <?php echo link_to_previous_exhibit_page(); ?>
	<?php echo link_to_next_exhibit_page(); ?>
	
	<div class="exhibit-content"><!--exhibit content-->
	<?php render_exhibit_page(); ?>
	</div>
</div><!--end primary-->
	
<?php exhibit_foot(); ?>