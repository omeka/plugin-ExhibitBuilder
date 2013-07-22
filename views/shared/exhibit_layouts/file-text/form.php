<?php
$formStem = $block->getFormStem();
$options = $block->getOptions();
?>
<div class="file-position">
    <?php echo $this->formLabel($formStem . '[options][file-position]', __('File position')); ?>
    <?php $file_position = array('Left', 'Right', 'Center (no text wrap)'); ?>
    <?php echo $this->formSelect($formStem . '[options][file-position]', @$options['file-position'], array(), $file_position); ?>
</div>

<div class="selected-items">
    <h4><?php echo __('Items'); ?></h4>
    <?php echo $this->exhibitFormAttachments($block); ?>
</div>

<div class="block-text">
    <h4><?php echo __('Text'); ?></h4>
    <?php echo $this->exhibitFormText($block); ?>
</div>
