<?php
$formStem = $block->getFormStem();
$options = $block->getOptions();
$attachments = $block->getAttachments();
?>
<div class="file-position">
    <?php echo $this->formLabel($formStem . '[options][file-position]', __('File position:')); ?>
    <?php $file_position = array('Left', 'Right', 'Center (no text wrap)'); ?>
    <?php echo $this->formSelect($formStem . '[options][file-position]', @$options['file-position'], array(), $file_position); ?>
</div>

<h4>Select items</h4>
<div class="selected-item-list">
<div class="add-item button"><a href="#">Add item</a></span></div>
<div class="item">
    <h5><a href="#">#302: Armory Square Hospital</a></h5>
    <span class="edit button"><a href="#">Edit</a></span>
    <span class="close button"><a href="#">Close</a></span>
</div>
</div>

<h4>Text</h4>
<?php echo $this->formTextarea($formStem . '[text]', $block->text, array('rows' => 10)); ?>
