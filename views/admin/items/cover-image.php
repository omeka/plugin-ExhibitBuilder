<div id="cover-image-form-elements">
<?php
    echo record_image($item, 'square_thumbnail');
    echo $this->formHidden('cover_image_file_id', $item->id);
?>
</div>
