<?php
$formStem = $block->getFormStem();
?>
<div class="file-position">
    <?php echo $this->formLabel($formStem . '[options][file-position]', __('File position:')); ?>
    <?php $file_position = array('Left', 'Right', 'Center (no text wrap)'); ?>
    <?php echo $this->formSelect($formStem . '[options][file-position]', null, array(), $file_position); ?>            
</div>

<h4>Select items</h4>
<div class="selected-item-list">
<span><a href="#">Add item</a></span>
</div>

<h4>Text</h4>
<?php echo $this->formTextarea($formStem . '[options][text]'); ?>
