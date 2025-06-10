<div class="cover-image-form-elements <?php echo ($file) ? 'attached' : ''; ?>">
    <p class="explanation">
        <?php echo __("Omeka will use the first attached file as the cover image."); ?>
    </p>
    <?php if ($file):
         $item = $file->getItem();
    ?>
    <div class="attachment-header">
        <button class="undo-delete" type="button" aria-label="<?php echo __('Undo delete'); ?>" title="<?php echo __('Undo delete'); ?>"></button>
        <button class="delete-element" type="button" aria-label="<?php echo __('Delete'); ?>" title="<?php echo __('Delete'); ?>"></button>
    </div>
    <div class="attachment-body">
        <div class="cover-image-background" style="background: url('<?php echo metadata($file, 'square_thumbnail_uri'); ?>') center / cover"></div>
        <h5>
            #<?php echo html_escape($item->id); ?>:
            <?php if (!metadata($item, 'public')): ?>
            <?php echo __('(Private)') . ' '; ?>
            <?php endif; ?>
            <?php echo metadata($item, array('Dublin Core', 'Title')); ?>
        </h5>
        <?php echo $this->formHidden('cover_image_item_id', $file->item_id); ?>
        <?php echo $this->formHidden('cover_image_file_id', $file->id); ?>
        <button class="edit-cover-image big blue button" type="button"><?php echo __('Change'); ?></button>
    </div>
    <?php else: ?>
    <button type="button" class="edit-cover-image big blue button" id="first-time-cover-image"><?php echo __('Change'); ?></button>
    <?php endif; ?>
</div>
