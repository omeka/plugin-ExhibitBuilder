<?php
$layout = $block->getLayout();
$stem = $block->getFormStem();
$order = $block->order;
?>
<div class="block-form" data-block-index="<?php echo $order; ?>">
    <div class="sortable-item drawer block-header opened">
        <h2 class="drawer-name"><?php echo __('Block'); ?> <?php echo $order; ?> (<?php echo $layout->name; ?>)</h2>
        <button class="drawer-toggle" type="button" data-action-selector="opened" aria-expanded="true" aria-controls="block-drawer-<?php echo $order; ?>" aria-label="<?php echo __('Show options'); ?>" title="<?php echo __('Show options'); ?>"><span class="icon"></span></button>
        <button class="undo-delete" type="button" data-action-selector="deleted" aria-label="<?php echo __('Undo remove'); ?>" title="<?php echo __('Undo remove'); ?>"><span class="icon"></span></button>
        <button class="delete-drawer" type="button" data-action-selector="deleted" aria-label="<?php echo __('Remove'); ?>" title="<?php echo __('Remove'); ?>"><span class="icon"></span></button>
    </div>
    <div class="drawer-contents block-body opened" id="block-drawer-<?php echo $order; ?>">
        <?php echo $this->formHidden($stem . '[layout]', $block->layout); ?>
        <?php echo $this->formHidden($stem . '[order]', $block->order, array('class' => 'block-order')); ?>
        <?php
        echo $this->partial($layout->getViewPartial('form'), array('block' => $block));
        ?>
    </div>
</div>
