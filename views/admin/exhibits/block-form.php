<?php
$layout = $block->getLayout();
$stem = $block->getFormStem();
$order = $block->order;
?>
<div class="block-form" data-block-index="<?php echo $order; ?>">
<h2><?php echo __('Section'); ?> <?php echo $order; ?> (<?php echo $layout->name; ?>)</h2>
<div class="drawer opened"></div>
<div class="remove-block"><span class="screen-reader-text"><?php echo __('Remove'); ?></span></div>
<?php echo $this->formHidden($stem . '[layout]', $block->layout); ?>
<?php echo $this->formHidden($stem . '[order]', $block->order, array('class' => 'block-order')); ?>
<?php
echo $this->partial($layout->getViewPartial('form'), array('block' => $block));
?>
</div>
