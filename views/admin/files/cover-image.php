<div class="cover-image-form-elements">
<?php if($file): ?>
    <div class="attachment-body">
        <?php if ($file): ?>
        <div class="cover-image-background" style="background: url('<?php echo metadata($file, 'square_thumbnail_uri'); ?>') center / cover"></div>
        <?php endif; ?>
        <?php echo $this->formHidden('cover_image_item_id', $file->item_id); ?>
        <?php echo $this->formHidden('cover_image_file_id', $file->id); ?>
    </div>

    <span class="edit-attachment" role="button"><?php echo __('Edit'); ?></span>
<?php else: ?>
    <p class="explanation">
        <?php echo __("No cover images available for this exhibit."); ?>
    </p>
<?php endif; ?>
</div>
