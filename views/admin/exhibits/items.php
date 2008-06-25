<div id="page-search-form">
<?php items_search_form(array('id'=>'search'), $url); ?>
</div>

<div id="pagination">
<?php 
	 echo pagination(); 
?>

</div>

<div id="item-list">
<?php while(loop_items()): ?>
	<div class="item-drop">
		<div class="item-drag">
			<div class="handle"><img src="<?php echo img('arrow_move.gif'); ?>"></div>
			<div class="item_id"><?php echo h($item->id); ?></div>
			<?php 
				if(item_has_thumbnail()){
					echo display_files_for_item();
				} else {
					echo item('Title', 0);
				}
			?>
		</div>
		<div class="item_id"><?php echo h($item->id); ?></div>
	</div>
<?php endwhile; ?>
</div>
		