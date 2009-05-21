<?php exhibit_builder_exhibit_head(array('bodyclass' => 'exhibits')); ?>
<div id="primary" class="show">

	<h2 class="item-title"><?php echo item('Dublin Core', 'Title'); ?></h2>
	<ul>
        <?php foreach (item('Dublin Core', 'Title', 'all') as $title): ?>
           <li class="item-title">
           <?php echo $title; ?>
           </li>
        <?php endforeach ?>
	</ul>
	
	<?php echo show_item_metadata(); ?>
	
	<div id="itemfiles">
		<?php echo display_files_for_item(); ?>
	</div>
	
	
	<?php if ( item_belongs_to_collection() ): ?>
        <div id="collection" class="field">
            <h2>Collection</h2>
            <div class="field-value"><p><?php echo link_to_collection_for_item(); ?></p></div>
        </div>
    <?php endif; ?>
    
	

	<?php if(count($item->Tags)): ?>
	<div class="tags">
		<h3>Tags:</h3>
	   <?php echo item_tags_as_string(); ?>	
	</div>
	<?php endif;?>
	
	<div id="citation" class="field">
    	<h2>Citation</h2>
    	<div id="citation-value" class="field-value"><?php echo item_citation(); ?></div>
	</div>

</div>
<?php exhibit_builder_exhibit_foot(); ?>