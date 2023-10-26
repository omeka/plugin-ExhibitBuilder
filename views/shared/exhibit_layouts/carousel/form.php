<?php
$formStem = $block->getFormStem();
$options = $block->getOptions();
?>
<div class="selected-items">
    <h4><?php echo __('Items'); ?></h4>
    <?php echo $this->exhibitFormAttachments($block); ?>
</div>

<div class="layout-options">
    <div class="block-header">
        <h4><?php echo __('Layout Options'); ?></h4>
        <div class="drawer-toggle"></div>
    </div>

    <div class="carousel-title">
        <?php echo $this->formLabel($formStem . '[options][carousel-title]', __('Carousel title')); ?>
        <?php
        echo $this->formText($formStem . '[options][carousel-title]',
            @$options['carousel-title']
        );
        ?>
    </div>

    <div class="per-slide">
        <?php echo $this->formLabel($formStem . '[options][per-slide]', __('Items per slide')); ?>
        <?php
        echo $this->formSelect($formStem . '[options][per-slide]',
            @$options['per-slide'], array(),
            array(
                1 => 1,
                2 => 2,
                3 => 3,
                4 => 4,
                5 => 5,
                6 => 6,
                7 => 7,
                8 => 8,
                9 => 9,
                10 => 10,
            ));
        ?>
    </div>
    
    <div class="file-size">
        <?php echo $this->formLabel($formStem . '[options][file-size]', __('Item file size')); ?>
        <?php
        $defaultFileSize = (get_option('use_square_thumbnail') == 1) ? 'square_thumbnail' : 'thumbnail';
        echo $this->formSelect($formStem . '[options][file-size]',
            (@$options['file-size']) ? @$options['file-size'] : $defaultFileSize, array(),
            array(
                'thumbnail' => __('Thumbnail'),
                'square_thumbnail' => __('Square Thumbnail'),
                'fullsize' => __('Full Size'),
            ));
        ?>
    </div>
    
    <div class="show-title">
        <?php echo $this->formLabel($formStem . '[options][show-title]', __('Show item title')); ?>
        <?php
        echo $this->formCheckbox($formStem . '[options][show-title]',
            @$options['show-title'], array(),
            array('1', '0'));
        ?>
    </div>
    
    <div class="float-caption">
        <?php echo $this->formLabel($formStem . '[options][float-caption]', __('Overlay caption')); ?>
        <?php
        echo $this->formCheckbox($formStem . '[options][float-caption]',
            @$options['float-caption'], array(),
            array('1', '0'));
        ?>
        <p class="instructions"><?php echo __('Place caption over image (may require adjusting CSS settings)'); ?></p>
    </div>
    
    <div class="caption-position">
        <?php echo $this->formLabel($formStem . '[options][caption-position]', __('Caption/title position')); ?>
        <?php
        echo $this->formSelect($formStem . '[options][caption-position]',
            @$options['caption-position'], array(),
            array(
                'center' => __('Center'),
                'left' => __('Left'),
                'right' => __('Right')
            ));
        ?>
    </div>
    
    <div class="stretch-image">
        <?php echo $this->formLabel($formStem . '[options][stretch-image]', __('Stretch image to fill')); ?>
        <?php
        echo $this->formSelect($formStem . '[options][stretch-image]',
            @$options['stretch-image'], array(),
            array(
                'none' => __('None'),
                'width' => __('Fill width'),
                'height' => __('Fill height'),
                'entire' => __('Fill entire slide')
            ));
        ?>
    </div>

    <div class="speed">
        <?php echo $this->formLabel($formStem . '[options][speed]', __('Scrolling speed')); ?>
        <?php
        $defaultSpeed = 400;
        echo $this->formText($formStem . '[options][speed]',
            (@$options['speed']) ? @$options['speed'] : $defaultSpeed
        );
        ?>
        <p class="instructions"><?php echo __('Sets the speed for the scrolling animation. May be "fast", "slow", or a time in milliseconds. Default is 400.'); ?></p>
    </div>
    
    <div class="auto-slide">
        <?php echo $this->formLabel($formStem . '[options][auto-slide]', __('Auto slide duration')); ?>
        <?php
        echo $this->formText($formStem . '[options][auto-slide]',
            @$options['auto-slide']
        );
        ?>
        <p class="instructions"><?php echo __('Time in milliseconds to pause before auto advance (set to 0 to turn off)'); ?></p>
    </div>
    
    <div class="loop">
        <?php echo $this->formLabel($formStem . '[options][loop]', __('Loop')); ?>
        <?php
        echo $this->formCheckbox($formStem . '[options][loop]',
            @$options['loop'], array(),
            array('1', '0'));
        ?>
    </div>
    
    <div class="fade-between-slides">
        <?php echo $this->formLabel($formStem . '[options][fade]', __('Fade between slides')); ?>
        <?php
        echo $this->formCheckbox($formStem . '[options][fade]',
            @$options['fade'], array(),
            array('1', '0'));
        ?>
        <p class="instructions"><?php echo __('Note: only works with 1 item per slide'); ?></p>
    </div>
</div>
