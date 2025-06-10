<?php
$id = html_escape($page->id);
$title = html_escape($page->title);
?>
<li class="page" id="page_<?php echo $id; ?>">
    <div class="sortable-item drawer">
        <span id="move-<?php echo $id; ?>" class="move icon" title="<?php echo __('Move'); ?>" aria-label="<?php __('Move'); ?>" aria-labelledby="move-<?php echo $id; ?> element-<?php echo $id; ?>"></span>
        <a href="../edit-page/<?php echo $id; ?>" class="drawer-name"><?php echo $title; ?></a>
        <button class="undo-delete" data-action-selector="deleted" type="button" aria-label="<?php echo __('Undo remove'); ?>" title="<?php echo __('Undo remove'); ?>"><span class="icon"></span></button>
        <button class="delete-drawer" data-action-selector="deleted" type="button" aria-label="<?php echo __('Remove'); ?>" title="<?php echo __('Remove'); ?>"><span class="icon"></span></button>

    </div>
    <?php if (isset($page->_pages[$id])): ?>
        <ul>
            <?php foreach ($this->_pages[$id] as $childPage): ?>
                <?php echo $this->partial($childPage, $currentPage, $ancestorIds); ?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</li>