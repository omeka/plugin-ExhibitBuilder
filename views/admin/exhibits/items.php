<a href="#" id="show-or-hide-search" class="show-form">Show Search Form</a>
<div id="page-search-form">
<?php
    $uri = uri(array('controller'=>'exhibits', 'action'=>'items', 'page'=>null));
    $isPartial = true;
    $formAttributes = array('id'=>'search');
    common('advanced-search', array('isPartial'=>$isPartial, 'formAttributes'=>$formAttributes), 'items');

    //items_search_form(array('id'=>'search'), $uri); 
?>
</div>
<div id="pagination" class="pagination">
<?php     
     echo pagination_links(array('url'=>uri(array('controller'=>'exhibits',
      'action'=>'items', 'page'=>null)) . '/')); 
     // The extra slash is a hack, the pagination should be fixed to work
     // without the extra slash being there. Also, I get the feeling that being
     // forced to set the 'page' parameter to null is also a hack.
?>

</div>
<div id="item-list">
<?php if (!has_items_for_loop()): ?>
    <p>There are no items to choose from.  Please refine your search or <a href="<?php echo html_escape(uri('items/add')); ?>">add</a> some items.</p>
<?php endif; ?>
<?php while($item = loop_items()): ?>
    <?php echo exhibit_builder_exhibit_form_item($item); ?>
<?php endwhile; ?>
</div>