<div id="cover-image-form-elements">
<?php
  echo record_image($file, 'square_thumbnail');
  echo $this->formHidden('cover_image_file_id', $file->id);
?>
</div>
