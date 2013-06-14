<?php
$formNameStem = 'blocks[' . $blockId . ']';
?>
<div class="block-form">
    <h4>Select layout</h3>
    <div class="layout-thumbs">
    <?php
        $layouts = ExhibitLayout::getLayouts();
        foreach ($layouts as $layout) {
            echo $layout->name;
            echo '<img src="' . html_escape($layout->getIconUrl()) . '">';
            echo '<input type="radio" name="' . html_escape($formNameStem . '[layout]') . '" value="'. html_escape($layout->id) .'">';
        }
    ?>
    </div>
    <?php echo $this->formLabel($formNameStem . '[options][file-position]', __('File position:')); ?>
    <?php $file_position = array('Left', 'Right', 'Center (no text wrap)'); ?>
    <?php echo $this->formSelect($formNameStem . '[options][file-position]', $formNameStem . '[options][file-position]', array(), $file_position); ?>
    
    <h4>Select items</h4>
    <div class="selected-item-list">
    <span><a href="#">Add item</a></span>
    </div>
    
    <h4>Text</h4>
    <?php echo $this->formTextarea($formNameStem . '[options][text]'); ?>
</div>
