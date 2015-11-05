<div id="cover-image-form-elements">
<?php if($file): ?>
<p class="explanation">
    <?php echo __("Choose an item to represent this exhibit.  shown on the browse exhibits page and on the home page when the exhibit is featured."); ?>
</p>
<input name="choose-item" id="exhibit-choose-cover-image" value="Choose Item" type="submit">
<br/>
<?php
  echo record_image($file, 'square_thumbnail');
  echo $this->formHidden('cover_image_file_id', $file->id);
?>
<?php else: ?>
<p class="explanation">
    <?php echo __("No cover images available for this exhibit."); ?>
</p>
<?php endif; ?>
</div>
