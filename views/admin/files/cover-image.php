<div class="cover-image-form-elements">
    <div class="attachment-body">
        <?php if ($file): ?>
        <div class="cover-image-background" style="background: url('<?php echo metadata($file, 'square_thumbnail_uri'); ?>') center / cover"></div>
        <?php echo $this->formHidden('cover_image_item_id', $file->item_id); ?>
        <?php echo $this->formHidden('cover_image_file_id', $file->id); ?>
        <span class="edit-cover-image" role="button"><?php echo __('Change'); ?></span>
        <?php else: ?>
        <p class="explanation">
          <?php echo __("Use the first item for the cover image."); ?>
        </p>
        <span class="edit-cover-image" id="first-time-cover-image" role="button"><?php echo __('Change'); ?></span>
        <?php endif; ?>
    </div>
</div>
