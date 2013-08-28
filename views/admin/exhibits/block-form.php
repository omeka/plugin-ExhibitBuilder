<?php
$layout = $block->getLayout();
$stem = $block->getFormStem();
$order = $block->order;
?>
<div class="block-form" data-block-index="<?php echo $order; ?>">
    <div class="sortable-item block-header">
        <h2><?php echo __('Block'); ?> <?php echo $order; ?> (<?php echo $layout->name; ?>)</h2>
        <div class="delete-element" role="button" title="<?php echo  __('Remove/Restore') ?>"></div>
        <div class="drawer opened" role="button" title="<?php echo __('Expand/Collapse'); ?>"></div>
    </div>
    <div class="block-body">
        <?php echo $this->formHidden($stem . '[layout]', $block->layout); ?>
        <?php echo $this->formHidden($stem . '[order]', $block->order, array('class' => 'block-order')); ?>
        <?php
        echo $this->partial($layout->getViewPartial('form'), array('block' => $block));
        ?>
    </div>
</div>
