<?php
$layout = $block->getLayout();
$stem = $block->getFormStem();
?>
<div class="block-form">
<a href="#" class="remove-block"><?php echo __('Remove'); ?></a>
<?php echo $this->formHidden($stem . '[layout]', $block->layout); ?>
<?php echo $this->formHidden($stem . '[order]', $block->order); ?>
<?php
echo $this->partial($layout->getViewPartial('form'), array('block' => $block));
?>
</div>
