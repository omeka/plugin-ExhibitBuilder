<?php
$item = $attachment->getItem();
if (!$item) {
    return;
}

$file = $attachment->getFile();
$stem = $block->getFormStem() . "[attachments][{$index}]";
?>
<div class="attachment" data-attachment-index="<?php echo html_escape($index); ?>">
    <div class="attachment-header">
        <h5>
            <?php if (!metadata($item, 'public')): ?>
            <?php echo __('(Private)') . ' '; ?>
            <?php endif; ?>
            <?php echo metadata($item,'rich_title', array('no_escape' => true)); ?>
        </h5>
        <button class="delete-element" type="button" title="<?php echo __('Delete'); ?>"></button>
        <button class="undo-delete" type="button" title="<?php echo __('Undo delete'); ?>"></button>
    </div>
    <div class="attachment-body">
        <?php if ($file): ?>
        <div class="attachment-background" style="background: url('<?php echo metadata($file, 'square_thumbnail_uri'); ?>') center / cover"></div>
        <?php endif; ?>
        <?php echo $this->formHidden($stem . '[item_id]', $item->id); ?>
        <?php if ($file): ?>
        <?php echo $this->formHidden($stem . '[file_id]', $file->id); ?>
        <?php endif; ?>
        <?php echo $this->formHidden($stem . '[caption]', $attachment->caption); ?>
        <?php echo $this->formHidden($stem . '[order]', $index + 1, array('class' => 'attachment-order')); ?>
    </div>

    <button class="edit-attachment" type="button"><?php echo __('Edit'); ?></button>
</div>
