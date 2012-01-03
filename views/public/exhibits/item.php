<?php
$title = html_escape(__('Item #%s', $item->id));
head(array('title' => $title, 'bodyid' => 'exhibit', 'bodyclass' => 'exhibit-item-show'));
?>
<div id="primary">

	<h1 class="item-title"><?php echo item('Dublin Core', 'Title'); ?></h1>
	
	<?php echo show_item_metadata(); ?>
	
	<div id="itemfiles">
		<?php echo display_files_for_item(); ?>
	</div>
	
	<?php if ( item_belongs_to_collection() ): ?>
        <div id="collection" class="field">
            <h2><?php echo __('Collection'); ?></h2>
            <div class="field-value"><p><?php echo link_to_collection_for_item(); ?></p></div>
        </div>
    <?php endif; ?>
    
	<?php if (item_has_tags()): ?>
	<div class="tags">
		<h2><?php echo __('Tags'); ?></h2>
	   <?php echo item_tags_as_string(); ?>	
	</div>
	<?php endif;?>
	
	<div id="citation" class="field">
    	<h2><?php echo __('Citation'); ?></h2>
    	<p id="citation-value" class="field-value"><?php echo item_citation(); ?></p>
	</div>
	
</div>
<?php foot(); ?>
