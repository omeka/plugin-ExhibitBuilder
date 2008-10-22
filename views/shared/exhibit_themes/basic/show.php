<?php exhibit_head(); ?>
<div id="primary">
	<?php echo link_to_previous_exhibit_page(); ?>
	<?php echo link_to_next_exhibit_page(); ?>

		<div class="exhibit-content">
		<?php render_exhibit_page(); ?>
		</div>

</div><!--end primary-->
	
<?php exhibit_foot(); ?>