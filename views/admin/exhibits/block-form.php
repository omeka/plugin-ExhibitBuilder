<?php
$layout = $block->getLayout();
$stem = $block->getFormStem();
$order = $block->order;

$blockTemplates = exhibit_builder_get_block_templates($block->getPage()->getExhibit(), $block->layout);
$blockTemplates = ['' => __('Default')] + $blockTemplates;

$cssHexColorRegex = '^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$';
$cssLength = '^(\d*\.?\d+)(%|cap|ch|em|ex|ic|lh|rem|rlh|vh|svh|lvh|dvh|vw|svw|lvw|dvw|vmax|svmax|lvmax|dvmax|vmin|svmin|lvmin|dvmin|vb|svb|lvb|dvb|vi|svi|lvi|dvi|cqw|cqh|cqi|cqb|cqmin|cqmax|px|cm|mm|Q|in|pc|pt)?$';
?>
<div class="block-form" data-block-index="<?php echo $order; ?>">
    <div class="sortable-item drawer block-header opened">
        <h2 class="drawer-name"><?php echo __('Block'); ?> <?php echo $order; ?> (<?php echo $layout->name; ?>)</h2>
        <button class="drawer-toggle" type="button" data-action-selector="opened" aria-expanded="true" aria-controls="block-drawer-<?php echo $order; ?>" aria-label="<?php echo __('Show options'); ?>" title="<?php echo __('Show options'); ?>"><span class="icon"></span></button>
        <button class="undo-delete" type="button" data-action-selector="deleted" aria-label="<?php echo __('Undo remove'); ?>" title="<?php echo __('Undo remove'); ?>"><span class="icon"></span></button>
        <button class="delete-drawer" type="button" data-action-selector="deleted" aria-label="<?php echo __('Remove'); ?>" title="<?php echo __('Remove'); ?>"><span class="icon"></span></button>
    </div>
    <div class="drawer-contents block-body opened" id="block-drawer-<?php echo $order; ?>">
        <?php echo $this->formHidden($stem . '[layout]', $block->layout); ?>
        <?php echo $this->formHidden($stem . '[order]', $block->order, array('class' => 'block-order')); ?>
        <?php
        echo $this->partial($layout->getViewPartial('form'), array('block' => $block));
        ?>
        <div class="layout-options">
            <div class="block-header drawer">
                <h4><?php echo __('Block Layout Options'); ?></h4>
                <button class="drawer-toggle" type="button" data-action-selector="opened"><span class="icon"></span></button>
            </div>
            <div class="drawer-contents" id="">
                <div>
                    <?php echo $this->formLabel(sprintf('%s[layout_data][template]', $stem), 'Template'); ?>
                    <?php echo $this->formSelect(sprintf('%s[layout_data][template]', $stem), $block->getLayoutData('template'), [], $blockTemplates); ?>
                </div>
                <div>
                    <?php echo $this->formLabel(sprintf('%s[layout_data][class]', $stem), 'Class'); ?>
                    <?php echo $this->formText(sprintf('%s[layout_data][class]', $stem), $block->getLayoutData('class')); ?>
                </div>
                <div>
                    <?php echo $this->formLabel(sprintf('%s[layout_data][alignment_block]', $stem), 'Block Alignment'); ?>
                    <?php echo $this->formSelect(sprintf('%s[layout_data][alignment_block]', $stem), $block->getLayoutData('alignment_block'), [], [
                        '' => __('Default'),
                        'left' => __('Left'),
                        'right' => __('Right'),
                        'center' => __('Center'),
                    ]); ?>
                </div>
                <div>
                    <?php echo $this->formLabel(sprintf('%s[layout_data][alignment_text]', $stem), 'Text Alignment'); ?>
                    <?php echo $this->formSelect(sprintf('%s[layout_data][alignment_text]', $stem), $block->getLayoutData('alignment_text'), [], [
                        '' => __('Default'),
                        'left' => __('Left'),
                        'right' => __('Right'),
                        'center' => __('Center'),
                        'justify' => __('Justify'),
                    ]); ?>
                </div>
                <div>
                    <?php echo $this->formLabel(sprintf('%s[layout_data][max_width]', $stem), 'Maximum Width'); ?>
                    <?php echo $this->formText(sprintf('%s[layout_data][max_width]', $stem), $block->getLayoutData('max_width'), ['pattern' => $cssLength]); ?>
                </div>
                <div>
                    <?php echo $this->formLabel(sprintf('%s[layout_data][min_height]', $stem), 'Minimum Height'); ?>
                    <?php echo $this->formText(sprintf('%s[layout_data][min_height]', $stem), $block->getLayoutData('min_height'), ['pattern' => $cssLength]); ?>
                </div>
                <div>
                    <?php echo $this->formLabel(sprintf('%s[layout_data][padding_top]', $stem), 'Top Padding'); ?>
                    <?php echo $this->formText(sprintf('%s[layout_data][padding_top]', $stem), $block->getLayoutData('padding_top'), ['pattern' => $cssLength]); ?>
                </div>
                <div>
                    <?php echo $this->formLabel(sprintf('%s[layout_data][padding_right]', $stem), 'Right Padding'); ?>
                    <?php echo $this->formText(sprintf('%s[layout_data][padding_right]', $stem), $block->getLayoutData('padding_right'), ['pattern' => $cssLength]); ?>
                </div>
                <div>
                    <?php echo $this->formLabel(sprintf('%s[layout_data][padding_bottom]', $stem), 'Bottom Padding'); ?>
                    <?php echo $this->formText(sprintf('%s[layout_data][padding_bottom]', $stem), $block->getLayoutData('padding_bottom'), ['pattern' => $cssLength]); ?>
                </div>
                <div>
                    <?php echo $this->formLabel(sprintf('%s[layout_data][padding_left]', $stem), 'Left Padding'); ?>
                    <?php echo $this->formText(sprintf('%s[layout_data][padding_left]', $stem), $block->getLayoutData('padding_left'), ['pattern' => $cssLength]); ?>
                </div>
                <div>
                    <?php echo $this->formLabel(sprintf('%s[layout_data][background_color]', $stem), 'Background Color'); ?>
                    <?php echo $this->formText(sprintf('%s[layout_data][background_color]', $stem), $block->getLayoutData('background_color'), ['pattern' => $cssHexColorRegex]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
