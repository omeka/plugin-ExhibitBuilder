<?php
$formStem = $block->getFormStem();
$options = $block->getOptions();
?>
<div class="file-position">
    <?php echo $this->formLabel($formStem . '[options][file-position]', __('File position')); ?>
    <?php
    echo $this->formSelect($formStem . '[options][file-position]',
        @$options['file-position'], array(),
        array('left' => 'Left', 'right' => 'Right'));
    ?>
</div>

<div class="file-size">
    <?php echo $this->formLabel($formStem . '[options][file-size]', __('File size')); ?>
    <?php
    echo $this->formSelect($formStem . '[options][file-size]',
        @$options['file-size'], array(),
        array(
            'fullsize' => 'Fullsize',
            'thumbnail' => 'Thumbnail',
            'square_thumbnail' => 'Square Thumbnail'
        ));
    ?>
</div>

<div class="selected-items">
    <h4><?php echo __('Items'); ?></h4>
    <?php echo $this->exhibitFormAttachments($block); ?>
</div>

<div class="block-text">
    <h4><?php echo __('Text'); ?></h4>
    <?php echo $this->exhibitFormText($block); ?>
</div>
