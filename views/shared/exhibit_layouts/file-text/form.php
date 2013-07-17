<?php
$formStem = $block->getFormStem();
$options = $block->getOptions();
$attachments = $block->getAttachments();
?>
<div class="file-position">
    <?php echo $this->formLabel($formStem . '[options][file-position]', __('File position')); ?>
    <?php $file_position = array('Left', 'Right', 'Center (no text wrap)'); ?>
    <?php echo $this->formSelect($formStem . '[options][file-position]', @$options['file-position'], array(), $file_position); ?>
</div>

<h4>Select items</h4>
<?php echo $this->exhibitAttachmentForm($block); ?>

<h4>Text</h4>
<?php echo $this->formTextarea($formStem . '[text]', $block->text, array('rows' => 10)); ?>
