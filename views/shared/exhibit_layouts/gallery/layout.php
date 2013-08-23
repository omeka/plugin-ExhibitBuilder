<?php
$showcasePosition = isset($options['showcase-position'])
    ? html_escape($options['showcase-position'])
    : 'left';
$showcaseFile = $options['showcase-position'] !== 'none';
$showShowcase = !empty($text) || ($showcaseFile && !empty($attachments));
$galleryPosition = isset($options['gallery-position'])
    ? html_escape($options['gallery-position'])
    : 'left';
?>
<?php if ($showcaseFile): ?>
<div class="gallery-showcase <?php echo $showcasePosition; ?> with-<?php echo $galleryPosition; ?>">
    <?php
        $attachment = array_shift($attachments);
        echo $this->exhibitAttachment($attachment, array('imageSize' => 'fullsize'));
    ?>
</div>
<?php endif; ?>
<div class="gallery <?php if ($showShowcase) echo 'with-showcase'; ?> <?php if (!empty($text) || $showShowcase) echo $galleryPosition; ?>">
    <?php echo $this->exhibitAttachmentGallery($attachments); ?>
</div>
<?php echo $text; ?>
