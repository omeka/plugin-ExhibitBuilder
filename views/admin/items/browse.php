<a href="" id="show-or-hide-search" class="show-form"><?php echo __('Show Search Form'); ?></a>
<div id="page-search-form">
<?php
    $uri = uri(array('controller'=>'exhibits', 'action'=>'items', 'page'=>null));
    $formAttributes = array('id'=>'search');
    echo items_search_form($formAttributes);
?>
</div>
<div id="pagination" class="pagination">
<?php     
     echo pagination_links(array('url'=>uri(array('controller'=>'exhibits',
      'action'=>'items', 'page'=>null), 'exhibitItemPagination') . '/')); 
     // The extra slash is a hack, the pagination should be fixed to work
     // without the extra slash being there. Also, I get the feeling that being
     // forced to set the 'page' parameter to null is also a hack.
?>

</div>
<div id="item-list">
<?php if (!has_items_for_loop()): ?>
    <p><?php echo __('There are no items to choose from.  Please refine your search or %s.', '<a href="' . html_escape(uri('items/add')) .'">' . __('add some items') .'</a>') ?></p>
<?php endif; ?>
<?php while($item = loop_items()): ?>
    <?php echo exhibit_builder_exhibit_form_item($item, null, null, false); ?>
<?php endwhile; ?>
</div>
