<?php
echo pagination_links(array('url' => url(array('controller' => 'exhibits',
    'action'=>'items', 'page' => null)))); 
?>
<div id="item-list">
<?php echo item_search_filters(); ?>
<a href="<?php echo url('exhibit-builder/items/browse'); ?>" id="view-all-items" class="green button"><?php echo __('Clear Filters'); ?></a>
<?php if (!has_loop_records('items')): ?>
    <p><?php echo __('There are no items to choose from.  Please refine your search or %s.', '<a href="' . html_escape(url('items/add')) .'">' . __('add some items') .'</a>') ?></p>
<?php endif; ?>
<?php foreach (loop('items') as $item): ?>
    <?php echo $this->exhibitItemListing($item); ?>
<?php endforeach; ?>
</div>
