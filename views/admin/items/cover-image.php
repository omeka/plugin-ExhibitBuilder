<?php
    echo record_image($item, 'square_thumbnail');
    echo $this->formHidden('cover_image_item_id', $item->getFile()->id);
?>
