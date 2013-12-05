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

<div class="layout-options">
    <div class="block-header">
        <h4><?php echo __('Layout Options'); ?></h4>
        <div class="drawer"></div>
    </div>

    <div class="showcase-position">
        <?php echo $this->formLabel($formStem . '[options][showcase-position]', __('Showcase file position')); ?>
        <?php
        echo $this->formSelect($formStem . '[options][showcase-position]',
            @$options['showcase-position'], array(),
            array(
                'none' => __('No showcase file'),
                'left' => __('Left'),
                'right' => __('Right')
            )
        );
        ?>
    </div>

    <div class="gallery-position">
        <?php echo $this->formLabel($formStem . '[options][gallery-position]', __('Gallery position')); ?>
        <?php
        echo $this->formSelect($formStem . '[options][gallery-position]',
            @$options['gallery-position'], array(),
            array(
                'left' => __('Left'),
                'right' => __('Right')
            )
        );
        ?>
        <p class="instructions"><?php echo __('If there is no showcase file or text, the gallery will use the full width of the page.'); ?></p>
    </div>
    
</div>
