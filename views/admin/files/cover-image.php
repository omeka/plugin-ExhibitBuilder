<div class="cover-image-form-elements">
    <?php if ($file):
         $item = $file->getItem();
    ?>
    <div class="attachment-header">
        <div class="delete-element" role="button" title="Remove/Restore"></div>
    </div>
    <div class="attachment-body">
        <div class="cover-image-background" style="background: url('<?php echo metadata($file, 'square_thumbnail_uri'); ?>') center / cover"></div>
        <h5>
            #<?php echo html_escape($item->id); ?>:<br>
            <?php if (!metadata($item, 'public')): ?>
            <?php echo __('(Private)') . ' '; ?>
            <?php endif; ?>
            <?php echo metadata($item, array('Dublin Core', 'Title')); ?>
        </h5>
        <?php echo $this->formHidden('cover_image_item_id', $file->item_id); ?>
        <?php echo $this->formHidden('cover_image_file_id', $file->id); ?>
        <span class="edit-cover-image" role="button"><?php echo __('Change'); ?></span>
    </div>
    <?php else: ?>
    <p class="explanation">
        <?php echo __("Use the first item for the cover image."); ?>
    </p>
    <span class="edit-cover-image" id="first-time-cover-image" role="button"><?php echo __('Change'); ?></span>
    <?php endif; ?>
</div>
