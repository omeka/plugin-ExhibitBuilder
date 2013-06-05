<?php
$formNameStem = 'blocks[' . $blockId . ']';
?>
<div class="block-form">
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
    Option1: <?php echo $this->formText($formNameStem . '[options][option1]'); ?>
</div>
