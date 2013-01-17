<?php echo item_search_filters(); ?>
<a href="" id="show-or-hide-search" class="show-form blue button"><?php echo __('Show Search Form'); ?></a>
<a href="<?php echo url(); ?>" id="view-all-items" class="green button"><?php echo __('View All Items'); ?></a>
<div id="page-search-form" class="container-twelve">
<?php
    $uri = url(array('controller'=>'exhibits', 'action'=>'items', 'page' => null));
    $formAttributes = array('id'=>'search');
    echo items_search_form($formAttributes);
?>
</div>
<?php     
     echo pagination_links(array('url' => url(array('controller' => 'exhibits',
      'action'=>'items', 'page' => null)))); 
?>
<div id="item-list">
<?php if (!has_loop_records('items')): ?>
    <p><?php echo __('There are no items to choose from.  Please refine your search or %s.', '<a href="' . html_escape(url('items/add')) .'">' . __('add some items') .'</a>') ?></p>
<?php endif; ?>
<?php foreach (loop('items') as $item): ?>
    <?php echo exhibit_builder_form_attachment($item, null, false); ?>
<?php endforeach; ?>
</div>
