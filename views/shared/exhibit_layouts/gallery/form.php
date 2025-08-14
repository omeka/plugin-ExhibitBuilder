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
    <div class="block-header drawer">
        <h4><?php echo __('Layout Options'); ?></h4>
        <button class="drawer-toggle" type="button" data-action-selector="opened" aria-expanded="true" aria-controls="<?php echo $formStem; ?>-layout-options" aria-label="<?php echo __('Show options'); ?>" title="<?php echo __('Show options'); ?>"><span class="icon"></span></button>
    </div>

    <div class="drawer-contents" id="<?php echo $formStem; ?>-layout-options">
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

        <div class="gallery-file-size">
            <?php echo $this->formLabel($formStem . '[options][gallery-file-size]', __('Gallery file size')); ?>
            <?php
                $defaultFileSize = (get_option('use_square_thumbnail') == 1) ? 'square_thumbnail' : 'thumbnail';
                echo $this->formSelect($formStem . '[options][gallery-file-size]',
                (@$options['gallery-file-size']) ? @$options['gallery-file-size'] : $defaultFileSize, array(),
                array(
                    'square_thumbnail' => __('Square Thumbnail'),
                    'thumbnail' => __('Thumbnail'),
                ));
                
            ?>
        </div>
        
        <div class="captions-position">
            <?php echo $this->formLabel($formStem . '[options][captions-position]', __('Captions position')); ?>
            <?php
            echo $this->formSelect($formStem . '[options][captions-position]',
                @$options['captions-position'], array(),
                array(
                    'center' => __('Center'),
                    'left' => __('Left'),
                    'right' => __('Right')
                ));
            ?>
        </div>
    </div>
</div>
