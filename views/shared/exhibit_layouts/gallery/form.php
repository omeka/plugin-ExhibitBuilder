<?php
$formStem = $block->getFormStem();
$options = $block->getOptions();
?>
<div class="selected-items">
    <h4><?php echo __('Items'); ?></h4>
    <?php echo $this->exhibitFormAttachments($block); ?>
</div>

<div class="block-text">
    <h4><?php echo __('Text'); ?></h4>
    <?php echo $this->exhibitFormText($block); ?>
</div>

<div class="aside-position">
    <?php echo $this->formLabel($formStem . '[options][aside-position]', __('Aside position')); ?>
    <?php
    echo $this->formSelect($formStem . '[options][aside-position]',
        @$options['aside-position'], array(),
        array('left' => 'Left', 'right' => 'Right'));
    ?>
</div>

<div class="showcase-file">
    <?php echo $this->formLabel($formStem . '[options][showcase-file]', __('Showcase file')); ?>
    <?php
    echo $this->formCheckbox($formStem . '[options][showcase-file]',
        @$options['showcase-file'], array(), array('1', '0'));
    ?>
</div>
