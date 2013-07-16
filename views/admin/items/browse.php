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
<div class="item-options">
    <h4>Item Options</h4>
    <div class="file-select">
        <p class="direction">Select a file to use.</p>
        <div class="inputs">
            <ul>
                <li class="item-file"><label><input id="item-1-file-1" type="radio" name="item[1]-file" value="file-1" CHECKED/><img src="http://placekitten.com/200/200">file1.jpg</label></li>
                <li class="item-file"><label><input id="item-1-file-2" type="radio" name="item[1]-file" value="file-2" /><img src="http://placekitten.com/200/200">file2.jpg</label></li>
                <li class="item-file"><label><input id="item-1-file-3" type="radio" name="item[1]-file" value="file-3" /><img src="http://placekitten.com/200/200">file3.jpg</label></li>
            </ul>
        </div>
    </div>
    <div class="item-caption">
        <p class="direction">Provide a caption.</p>
        <div class="inputs">
            <textarea id="item-1-caption" name="item[1]-caption" />
        </div>
    </div>
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
